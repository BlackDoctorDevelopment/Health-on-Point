=== Health on Point ===
Contributors: blackdoctor
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage Health on Point AI-driven health assessments — configure Tavus replicas, questions, scoring tiers, and ad placements without code.

== Description ==

Health on Point lets your content team create and configure AI-driven health assessments powered by Tavus, directly from the WordPress admin — no code required.

**Features:**

* Custom post type for Assessments with 12 admin panels
* Live Tavus replica & persona picker (fetched from the Tavus API)
* Bulk CSV question importer
* Google Ad Manager (GAM) tag or house creative control per assessment
* Public REST API (`/wp-json/hop/v1/assessment/{slug}`) consumed by a Next.js app on Vercel
* On-save Vercel ISR revalidation webhook
* `[health-on-point id="slug"]` shortcode for iframe embedding in any post or page
* Duplicate assessment row action

== Requirements ==

* MetaBox (free core)       — https://wordpress.org/plugins/meta-box/
* MB Group (free extension) — https://metabox.io/plugins/meta-box-group/

Optional:
* MB Conditional Logic      — https://metabox.io/plugins/meta-box-conditional-logic/
  (enables show/hide of fields based on radio/checkbox values)

== Installation ==

1. Install and activate **MetaBox** from WordPress.org.
2. Install and activate **MB Group** from metabox.io (free).
3. Upload the `health-on-point` folder to `/wp-content/plugins/` or install via **Plugins → Add New → Upload Plugin**.
4. Activate the plugin.
5. Go to **Assessments → Settings** and configure:
   * Tavus API key
   * Vercel app URL
   * Vercel revalidate secret (must match `REVALIDATE_SECRET` in Vercel env)
   * GAM network code (optional)

== Changelog ==

= 1.0.0 =
* Initial release (MetaBox edition).
