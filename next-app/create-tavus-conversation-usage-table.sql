-- Supabase table for Tavus conversation usage data
create table if not exists tavus_conversation_usage (
  id uuid default gen_random_uuid() primary key,
  fetched_at timestamptz not null,
  data jsonb not null,
  created_at timestamptz default now()
);
