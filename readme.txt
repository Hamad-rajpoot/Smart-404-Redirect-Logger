=== Hamada Smart 404 Redirect & Logger ===
Contributors: hamad
Tags: 404, redirect, logs, broken links, seo, error monitor, url redirect
Requires at least: 5.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Hamada Smart 404 Redirect & Logger helps you monitor 404 errors, log broken URLs, and fix them with manual or automatic redirects. Improve SEO and user experience instantly.

== Description ==

Broken or missing pages hurt SEO and user experience.  
Hamada Smart 404 Redirect & Logger solves this by:

* Logging 404 URLs
* Recording IP addresses & referrers
* Allowing manual redirects (From → To)
* Automatically applying redirects
* Auto deleting old logs to keep the database clean
* Exporting logs to CSV for analysis

Ideal for SEO experts, site admins, and developers maintaining active websites.

=== Key Features ===

- Log missing/404 URLs
- Record IP addresses and referrers
- Manual Redirect Manager (Create From → To redirects)
- Instant redirect on 404 visits
- Exclude URLs from logging
- Exclude IPs from logging (e.g., admin or developer IPs)
- Auto-delete old logs using WP-Cron
- Export logs to CSV
- Lightweight and performance-focused

=== Why Use This Plugin? ===

Broken links damage SEO. This plugin helps you:

* Detect broken links automatically
* Fix them before search engines index them
* Prevent users from seeing 404 pages
* Improve rankings and user engagement

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin from **Plugins > Installed Plugins**
3. Go to **404 Logs** in the WordPress admin menu
4. (Optional) Configure settings in **Settings → Smart 404 Settings**
5. Use **Redirect Manager** to create custom redirects

== Screenshots ==

1. 404 Logs Table
2. Manual Redirect Manager
3. Settings Page (Exclude URLs, Exclude IPs, Auto Delete Logs)
4. Export Logs to CSV button

== Frequently Asked Questions ==

= Will this plugin slow down my site? =
No. The plugin is optimized to log efficiently and only runs on 404 requests.

= Can I export logs for analysis? =
Yes, click the **Export CSV** button on the Logs page.

= Does auto delete require WP-Cron? =
Yes, WordPress Cron must be enabled (`wp-cron.php`).

= Can I exclude admin users or IPs? =
Yes, specify IP addresses in the settings page.

== Changelog ==

= 1.0.0 =
Initial release:
- Log 404 requests with IP & referrer
- Manual Redirect Manager
- Exclude URLs and IPs
- Auto-delete old logs
- Export logs to CSV

== Upgrade Notice ==

No upgrade notes yet.
