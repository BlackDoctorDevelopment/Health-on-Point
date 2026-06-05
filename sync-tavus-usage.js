import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

async function syncToMonday(data) {
  if (!process.env.MONDAY_API_KEY || !process.env.MONDAY_BOARD_ID) {
    return;
  }

  const columnValues = JSON.stringify({
    status: { label: 'Done' },
    numbers: data.total_minutes_used ?? 0,
  });

  const query = `mutation {
    create_item(
      board_id: ${process.env.MONDAY_BOARD_ID},
      item_name: "Tavus Usage - ${new Date().toDateString()}",
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

export default async function handler(req, res) {
  if (req.method !== 'GET') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  const authHeader = req.headers.authorization || '';
  if (authHeader !== `Bearer ${process.env.CRON_SECRET}`) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

  const tavusRes = await fetch('https://tavusapi.com/v2/usage', {
    headers: {
      'x-api-key': process.env.TAVUS_API_KEY,
    },
  });

  if (!tavusRes.ok) {
    const text = await tavusRes.text();
    return res.status(502).json({ error: 'Tavus API request failed', details: text });
  }

  const tavusData = await tavusRes.json();

  const { error } = await supabase.from('tavus_usage').insert({
    fetched_at: new Date().toISOString(),
    data: tavusData,
  });

  if (error) {
    return res.status(500).json({ error: error.message });
  }

  try {
    await syncToMonday(tavusData);
  } catch (mondayError) {
    console.warn('Monday sync failed:', mondayError);
  }

  return res.status(200).json({ success: true });
}
