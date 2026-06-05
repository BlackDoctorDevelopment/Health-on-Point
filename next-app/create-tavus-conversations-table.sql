-- Supabase table for Tavus conversation usage data
-- Matches the rows written by next-app/app/api/sync-tavus-usage/route.js
-- Table name: tavus_conversation_usage (override via SUPABASE_TABLE_NAME)
create table if not exists public.tavus_conversation_usage (conversation_id text primary key, persona_name text, experience_name text, user_id text, status text, started_at timestamptz, ended_at timestamptz, duration_seconds integer, duration_minutes integer, estimated_cost numeric, shutdown_reason text, monday_item_id text);
create index if not exists tavus_conversation_usage_started_at_idx on public.tavus_conversation_usage (started_at desc);
create index if not exists tavus_conversation_usage_persona_idx on public.tavus_conversation_usage (persona_name);
create index if not exists tavus_conversation_usage_experience_idx on public.tavus_conversation_usage (experience_name);
