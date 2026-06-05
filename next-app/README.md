# Next App - Tavus Usage Sync

This app exposes a cron route at `/api/sync-tavus-usage` that:
- fetches conversations from the Tavus API
- computes duration data from `created_at` / `updated_at`
- upserts rows into Supabase `tavus_conversations`
- optionally syncs usage summaries to Monday.com

## Environment
Copy `.env.local.example` to `.env.local` and set:

- `SUPABASE_URL`
- `SUPABASE_SERVICE_ROLE_KEY`
- `SUPABASE_TABLE_NAME` (default: `tavus_conversations`)
- `TAVUS_API_KEY`
- `CRON_SECRET`
- `MONDAY_API_KEY`
- `MONDAY_BOARD_ID`

## Local test

Call the route with the cron secret header:

```bash
curl -H "Authorization: Bearer $CRON_SECRET" http://localhost:3000/api/sync-tavus-usage
```

## Vercel cron

The route is scheduled in `vercel.json`:

```json
{
  "crons": [
    {
      "path": "/api/sync-tavus-usage",
      "schedule": "0 9 * * 1"
    }
  ]
}
```

Vercel cron requests are also allowed using the `x-vercel-cron` header.
