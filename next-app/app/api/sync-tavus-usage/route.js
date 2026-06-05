import { NextResponse } from 'next/server';
import { createClient } from '@supabase/supabase-js';

const TABLE_NAME = process.env.SUPABASE_TABLE_NAME || 'tavus_conversation_usage';
const COST_PER_MINUTE = 0.05;

function getMissingEnvVars() {
  return ['SUPABASE_URL', 'SUPABASE_SERVICE_ROLE_KEY', 'TAVUS_API_KEY', 'CRON_SECRET'].filter((key) => !process.env[key]);
}

function createSupabaseClient() {
  return createClient(process.env.SUPABASE_URL, process.env.SUPABASE_SERVICE_ROLE_KEY);
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
    headers: { 'Content-Type': 'application/json', Authorization: process.env.MONDAY_API_KEY },
    body: JSON.stringify({ query }),
  });
  const json = await res.json();
  return json?.data?.boards?.[0]?.columns ?? [];
}

function getColumnId(columns, title) {
  return columns.find((column) => column.title === title)?.id;
}

async function syncToMonday(conversations) {
  if (!process.env.MONDAY_API_KEY || !process.env.MONDAY_BOARD_ID) return;

const columns = await getMondayColumns();
  if (!columns.length) return;

const columnId = (title) => getColumnId(columns, title);
  const grouped = {};

for (const conv of conversations) {
  const date = conv.started_at ? conv.started_at.split('T')[0] : new Date().toISOString().split('T')[0];
  const persona = conv.persona_name ?? 'Unknown';
  const experience = conv.experience_name ?? 'Unknown';
  const key = `${date}||${persona}||${experience}`;
  if (!grouped[key]) {
    grouped[key] = { date, persona, experience, conversations: 0, totalMinutes: 0 };
  }
  grouped[key].conversations += 1;
  grouped[key].totalMinutes += conv.duration_minutes ?? 0;
}

for (const row of Object.values(grouped)) {
  const minutesUsed = row.totalMinutes;
  const avgMinutes = row.conversations > 0 ? Math.round(minutesUsed / row.conversations) : 0;
  const estimatedCost = (minutesUsed * COST_PER_MINUTE).toFixed(2);
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

  const mutation = `mutation { create_item(board_id: ${process.env.MONDAY_BOARD_ID}, item_name: "${row.experience} - ${row.date}", column_values: ${JSON.stringify(columnValues)}) { id } }`;

  await fetch('https://api.monday.com/v2', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Authorization: process.env.MONDAY_API_KEY },
    body: JSON.stringify({ query: mutation }),
  });
}
}

export async function GET(request) {
  const missingEnv = getMissingEnvVars();
  if (missingEnv.length) {
    return NextResponse.json({ error: 'Missing environment variables', missing: missingEnv }, { status: 500 });
  }

const authHeader = request.headers.get('authorization');
  const isVercelCron = request.headers.get('x-vercel-cron');
  if (authHeader !== `Bearer ${process.env.CRON_SECRET}` && !isVercelCron) {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

const tavusRes = await fetch('https://tavusapi.com/v2/conversations', {
  headers: { 'x-api-key': process.env.TAVUS_API_KEY },
});

const tavusText = await tavusRes.text();
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
    const startedAt = parseTimestamp(c.created_at);
    const endedAt = parseTimestamp(c.updated_at);
    const durationSeconds = computeDurationSeconds(startedAt, endedAt);
    const durationMinutes = computeDurationMinutes(durationSeconds);
    const estimatedCost = durationMinutes != null ? Number((durationMinutes * COST_PER_MINUTE).toFixed(2)) : null;

                              return {
                                conversation_id: c.conversation_id ?? c.id,
                                persona_name: c.persona_name ?? null,
                                experience_name: c.experience_name ?? c.conversation_name ?? null,
                                user_id: null,
                                status: c.status ?? null,
                                started_at: startedAt,
                                ended_at: endedAt,
                                duration_seconds: durationSeconds,
                                duration_minutes: durationMinutes,
                                estimated_cost: estimatedCost,
                                shutdown_reason: null,
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
