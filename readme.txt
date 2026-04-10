=== AI Content Reservation (TDMRep) ===
Contributors: eloqio, haroldparis
Tags: ai, privacy, tdm, copyright, seo
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Signal your AI training opt-out through the W3C TDM Reservation Protocol. One click, three delivery methods, zero configuration file to upload.

== Description ==

**AI Content Reservation** implements the [W3C TDM Reservation Protocol (TDMRep)](https://www.w3.org/community/reports/tdmrep/CG-FINAL-tdmrep-20240510/) on your WordPress site. TDMRep is the technical counterpart of the European DSM Directive (Article 4) and the AI Act: it lets publishers legally reserve their rights regarding the use of their content for Text and Data Mining, including generative AI training.

Unlike `robots.txt`, which is a voluntary convention, TDMRep has **legal weight**. AI providers that ignore a properly deployed TDMRep signal expose themselves to substantial fines under the EU AI Act starting August 2026.

= What this plugin does =

* Serves `/.well-known/tdmrep.json` dynamically — no file to create, no FTP required
* Adds the `tdm-reservation` HTTP header on every front-end response
* Injects the matching `<meta name="tdm-reservation">` tag in the HTML head
* Supports the optional `tdm-policy` URL to link to your human-readable policy document
* Ships with a Site Health check that verifies the endpoint responds correctly
* Zero dependencies, zero tracking, zero external API calls

= Why three delivery methods? =

The TDMRep specification allows crawlers to look for the signal in any of the three locations. Deploying all three maximises the chance that AI bots actually pick up your reservation.

= Evidence preservation =

After activating the plugin, submit your endpoint URL to the [Wayback Machine](https://web.archive.org/) to create a timestamped proof of publication. This is essential if you ever need to demonstrate the date from which your reservation was in force.

= Who is this for? =

News publishers, bloggers, authors, creative agencies, and any content creator who wants to keep a clear legal record of their opt-out from AI training. Combine this plugin with blocking rules for known AI crawlers in your `robots.txt` for defence in depth.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ai-content-reservation` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Settings → AI Content Reservation** to verify or adjust the reservation value.
4. Visit `https://your-site.com/.well-known/tdmrep.json` to confirm the endpoint is live.
5. (Optional) Archive the endpoint URL on the Wayback Machine for evidence.

== Frequently Asked Questions ==

= Does this plugin block AI crawlers? =

No. TDMRep is a declarative legal signal, not a technical block. It tells compliant AI providers that your content is off-limits for training. For active blocking, combine it with `robots.txt` rules or WAF filters.

= Does it work retroactively? =

No. TDMRep only applies to crawls performed after you deployed the signal. Content already collected before activation is not covered.

= Does it affect SEO? =

No. Google Search and Bing are not affected. TDMRep targets TDM/AI training crawlers specifically. Google's AI training (Google-Extended) reads a separate `robots.txt` directive.

= Is there a performance impact? =

Negligible. The endpoint is served in a single PHP request, the HTTP header is one `header()` call, and the meta tag is three lines of HTML.

= Can I reserve only part of my site? =

The current version applies to the entire site (`location: /`). Per-post granularity is on the roadmap for future versions if demand justifies the added complexity.

== Screenshots ==

1. Settings screen under Settings → AI Content Reservation.
2. The JSON endpoint served at /.well-known/tdmrep.json.
3. Site Health check confirming the endpoint is reachable.

== Changelog ==

= 1.0.0 =
* Initial release.
* Dynamic `/.well-known/tdmrep.json` endpoint.
* `tdm-reservation` HTTP header on all front-end responses.
* `<meta name="tdm-reservation">` tag injection.
* Optional `tdm-policy` URL support.
* Site Health async check.
* French and English translations included.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
