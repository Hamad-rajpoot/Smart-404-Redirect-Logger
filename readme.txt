=== Smart 404 Redirect & Logger ===
Contributors: hamad
Tags: 404, redirect, logs, broken links, seo, error monitor, url redirect
Requires at least: 5.5
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Smart 404 Redirect & Logger helps you monitor 404 errors, log broken URLs, and fix them by adding manual redirects. Improve SEO and user experience instantly.

== Description ==

When users land on broken or missing pages, it hurts SEO and user experience.  
Smart 404 Redirect & Logger solves this by:

* **Logging 404 URLs**
* **Recording IP addresses & referrers**
* **Allowing manual redirects** (From → To)
* **Automatically applying redirects**
* **Auto deleting old logs to keep database clean**
* **Export logs to CSV for analysis**

Ideal for SEO experts, site admins, and developers maintaining active websites.

### Key Features

- Log missing/404 URLs
- Log IP Address and Referrer
- Manual Redirect Manager (Create From → To redirects)
- Apply redirects instantly when the 404 is visited
- Exclude URLs from logging
- Exclude IPs from logging (e.g., admin or developer’s own visits)
- Auto-delete old logs (Uses WP-Cron)
- Export logs to CSV
- Lightweight & performance-focused

### Why this plugin?
Broken links damage SEO significantly.  
This plugin helps you:

✔ Detect broken links  
✔ Fix them before search engines index them  
✔ Prevent users from seeing a 404 page  
✔ Improve rankings and engagement  

---

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin from **Plugins > Installed Plugins**
3. Go to **404 Logs** in the WordPress Admin menu
4. (Optional) Configure settings in **404 Logger → Settings**
5. Use **Redirect Manager** to create custom redirects

---

== Screenshots ==

1. 404 Logs Table
2. Manual Redirect Manager
3. Settings Page (Exclude URLs, Exclude IPs, Auto Delete Logs)
4. Export Logs to CSV Button

---

== Frequently Asked Questions ==

= Will this slow down my website? =
No. The plugin is optimized to store logs efficiently and only runs on 404 requests.

= Can I export logs for analysis? =
Yes, use the **Export CSV** button on Logs page.

= Does auto delete require Cron to work? =
Yes, WordPress Cron must be enabled (wp-cron.php).

= Can I exclude admin users or IPs? =
Yes, you can specify IP addresses in the settings.

---

== Changelog ==

= 1.0.0 =
Initial release.  
- Log 404 with IP & referrer  
- Manual redirect manager  
- Exclude URLs and IPs  
- Auto delete old logs  
- Export logs to CSV

---

== Upgrade Notice ==

No upgrade notes yet.

