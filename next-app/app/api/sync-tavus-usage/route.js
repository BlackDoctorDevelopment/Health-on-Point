import { NextResponse } from 'next/server';
import { createClient } from '@supabase/supabase-js';

const TABLE_NAME = process.env.SUPABASE_TABLE_NAME || 'tavus_conversations';

function getMissingEnvVars() {
  return [
    'SUPABASE_URL',
    'SUPABASE_SERVICE_ROLE_KEY',
    'TAVUS_API_KEY',
    'CRON_SECRET',
  ].filter((key) => !process.env[key]);
}

function createSupabaseClient() {
  return createClient(
    process.env.SUPABASE_URL,
    process.env.SUPABASE_SERVICE_ROLE_KEY
  );
}

function parseTimestamp(value) {
  if (!value) return null;
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? null : date.toISOString();
}

function computeDurationSeconds(start, end) {
  if (!start || !end) return null;
  const startMs = new Date(start).getTime();
  const endMs = new Date(end).getTime();
  if (Number.isNaN(startMs) || Number.isNaN(endMs) || endMs < startMs) return null;
  return Math.round((endMs - startMs) / 1000);
}

function computeDurationMinutes(seconds) {
  if (typeof seconds !== 'number' || Number.isNaN(seconds)) return null;
  return Math.ceil(seconds / 60);
}

async function getMondayColumns() {
  const query = `query { boards(ids: ${process.env.MONDAY_BOARD_ID}) { columns { id title } } }`;
  const res = await fetch('https://api.monday.com/v2', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: process.env.MONDAY_API_KEY,
    },
    body: JSON.stringify({ query }),
  });
  const json = await res.json();
  return json?.data?.boards?.[0]?.columns ?? [];
}

function getColumnId(columns, title) {
  return columns.find((column) => column.title === title)?.id;
}

async function syncToMonday(conversations) {
  if (!process.env.MONDAY_API_KEY || !process.env.MONDAY_BOARD_ID) {
    return;
  }

  const columns = await getMondayColumns();
  if (!columns.length) return;

  const columnId = (title) => getColumnId(columns, title);
  const grouped = {};

  for (const conv of conversations) {
    const date = conv.created_at ? conv.created_at.split('T')[0] : new Date().toISOString().split('T')[0];
    const persona = conv.persona_name ?? conv.persona_id ?? 'Unknown';
    const experience = conv.experience_name ?? conv.conversation_name ?? 'Unknown';
    const key = `${date}||${persona}||${experience}`;
    const durationSeconds = computeDurationSeconds(conv.created_at, conv.updated_at);
    if (!grouped[key]) {
      grouped[key] = {
        date,
        persona,
        experience,
        conversations: 0,
        totalMinutes: 0,
      };
    }
    grouped[key].conversations += 1;
    grouped[key].totalMinutes += computeDurationMinutes(durationSeconds) ?? 0;
  }

  for (const row of Object.values(grouped)) {
    const minutesUsed = row.totalMinutes;
    const avgMinutes = row.conversations > 0 ? Math.round(minutesUsed / row.conversations) : 0;
    const estimatedCost = (minutesUsed * 0.05).toFixed(2);
    const columnValues = JSON.stringify({
      [columnId('Date')]: { date: row.date },
      [columnId('Experience')]: row.experience,
      [columnId('Persona')]: row.persona,
      [columnId('Conversations')]: row.conversations,
      [columnId('Minutes Used')]: minutesUsed,
      [columnId('Avg Minutes')]: avgMinutes,
      [columnId('Estimated Cost')]: estimatedCost,
      [columnId('Status')]: { label: 'Synced' },
    });

    const query = `mutation {
      create_item(
        board_id: ${process.env.MONDAY_BOARD_ID},
        item_name: "${row.experience} - ${row.date}",
        column_values: "${columnValues.replace(/"/g, '\\"')}"
      ) { id }
    }`;

    await fetch('https://api.monday.com/v2', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: process.env.MONDAY_API_KEY,
      },
      body: JSON.stringify({ query }),
    });
  }
}

export async function GET(request) {
  const missingEnv = getMissingEnvVars();
  if (missingEnv.length) {
    return NextResponse.json(
      { error: 'Missing environment variables', missing: missingEnv },
      { status: 500 }
    );
  }

  const authHeader = request.headers.get('authorization');
  const isVercelCron = request.headers.get('x-vercel-cron');
  if (authHeader !== `Bearer ${process.env.CRON_SECRET}` && !isVercelCron) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  const tavusRes = await fetch('https://tavusapi.com/v2/conversations', {
    headers: {
      'x-api-key': process.env.TAVUS_API_KEY,
    },
  });

  const tavusText = await tavusRes.text();
  console.error('Tavus fetch', { status: tavusRes.status, body: tavusText.slice(0, 2000) });

  let tavusData;
  try {
    tavusData = JSON.parse(tavusText);
  } catch (error) {
    return NextResponse.json({ error: 'Invalid Tavus response', details: tavusText }, { status: 502 });
  }

  if (!tavusRes.ok) {
    return NextResponse.json({ error: 'Tavus API request failed', status: tavusRes.status, details: tavusData }, { status: 502 });
  }

  const rows = Array.isArray(tavusData.data) ? tavusData.data : [];
  if (!rows.length) {
    return NextResponse.json({ success: true, inserted: 0, message: 'No conversations returned' });
  }

  const supabase = createSupabaseClient();
  const mappedRows = rows.map((c) => {
    const createdAt = parseTimestamp(c.created_at);
    const updatedAt = parseTimestamp(c.updated_at);
    const durationSeconds = computeDurationSeconds(createdAt, updatedAt);
    const durationMinutes = computeDurationMinutes(durationSeconds);

    return {
      conversation_id: c.conversation_id ?? c.id,
      conversation_name: c.conversation_name ?? null,
      conversation_url: c.conversation_url ?? null,
      conversational_context: c.conversational_context ?? null,
      callback_url: c.callback_url ?? null,
      status: c.status ?? null,
      replica_id: c.replica_id ?? null,
      persona_id: c.persona_id ?? null,
      persona_name: c.persona_name ?? null,
      experience_name: c.experience_name ?? null,
      created_at: createdAt,
      updated_at: updatedAt,
      duration_seconds: durationSeconds,
      duration_minutes: durationMinutes,
      fetched_at: new Date().toISOString(),
    };
  });

  const { error } = await supabase.from(TABLE_NAME).upsert(mappedRows, { onConflict: 'conversation_id' });
  if (error) {
    return NextResponse.json({ error: error.message }, { status: 500 });
  }

  try {
    await syncToMonday(mappedRows);
  } catch (err) {
    console.warn('Monday sync failed:', err);
  }

  return NextResponse.json({ success: true, inserted: mappedRows.length });
}
