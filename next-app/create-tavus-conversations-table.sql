-- Create the tavus_conversations table for Tavus conversation sync
create table if not exists public.tavus_conversations (
  conversation_id text primary key,
  conversation_name text,
  conversation_url text,
  conversational_context text,
  callback_url text,
  status text,
  replica_id text,
  persona_id text,
  persona_name text,
  experience_name text,
  duration_seconds integer,
  duration_minutes integer,
  fetched_at timestamptz not null default now(),
  created_at timestamptz,
  updated_at timestamptz,
  monday_item_id text
);

create index if not exists tavus_conversations_fetched_at_idx on public.tavus_conversations (fetched_at desc);
create index if not exists tavus_conversations_status_idx on public.tavus_conversations (status);
create index if not exists tavus_conversations_persona_id_idx on public.tavus_conversations (persona_id);
