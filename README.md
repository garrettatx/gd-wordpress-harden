# GD WordPress Harden

A lightweight WordPress must-use (MU) plugin for agency-standard site hardening. Drop one file into `wp-content/mu-plugins/` and it handles the security, cleanup, and environment awareness tasks that every WordPress site needs but nobody wants to configure manually.

**18 features. All independently toggleable. Zero database entries. Zero settings pages.**

Built and maintained by [Garrett Digital](https://www.garrettdigital.com).

## Installation

1. Download `gd-wordpress-harden.php`
2. Upload to `/wp-content/mu-plugins/`
3. Done. MU plugins activate automatically.

To verify: Go to **Settings > Site Hardening** in your WordPress admin to see which features are active and how they're configured.

## Why a Must-Use Plugin?

MU plugins load before regular plugins, can't be accidentally deactivated from the WordPress admin, and don't appear in the regular plugins list for non-technical users to mess with. A client can't accidentally deactivate your hardening by clicking "Deactivate" in the plugins list.

The tradeoff: MU plugins can only be updated via SFTP or file manager. For an agency managing client sites, that's a feature, not a bug.

## Features at a Glance

| Feature | Constant | Default |
|---------|----------|---------|
| [Disable Comments](#disable-comments) | `GD_DISABLE_COMMENTS` | On |
| [Restrict REST API](#restrict-rest-api) | `GD_RESTRICT_REST_API` | On |
| [Restrict XML-RPC](#restrict-xml-rpc) | `GD_RESTRICT_XMLRPC` | On |
| [Remove Emoji Scripts](#remove-emoji-scripts) | `GD_REMOVE_EMOJI` | On |
| [Dashboard Support Widget](#dashboard-support-widget) | `GD_DASHBOARD_WIDGET` | On |
| [Disable Author Archives](#disable-author-archives) | `GD_DISABLE_AUTHOR_ARCHIVES` | On |
| [Remove WP Version](#remove-wp-version) | `GD_REMOVE_WP_VERSION` | On |
| [Disable Self-Pingbacks](#disable-self-pingbacks) | `GD_DISABLE_SELF_PINGBACKS` | On |
| [Disable Application Passwords](#disable-application-passwords) | `GD_DISABLE_APP_PASSWORDS` | On |
| [Environment Awareness](#environment-awareness) | `GD_ENVIRONMENT_AWARENESS` | On |
| [Disable Admin Email Check](#disable-admin-email-check) | `GD_DISABLE_ADMIN_EMAIL_CHECK` | On |
| [Obscure Login Errors](#obscure-login-errors) | `GD_OBSCURE_LOGIN_ERRORS` | On |
| [Custom Admin Footer](#custom-admin-footer) | `GD_CUSTOM_ADMIN_FOOTER` | On |
| [Limit Post Revisions](#limit-post-revisions) | `GD_LIMIT_REVISIONS` | On |
| [Throttle Heartbeat API](#throttle-heartbeat-api) | `GD_THROTTLE_HEARTBEAT` | On |
| [Disable oEmbed Discovery](#disable-oembed-discovery) | `GD_DISABLE_OEMBED_DISCOVERY` | On |
| [Noindex Warning Banner](#noindex-warning-banner) | `GD_NOINDEX_WARNING` | On |
| [Status Page](#status-page) | `GD_STATUS_PAGE` | On |

To disable any feature, add its constant to `wp-config.php` and set it to `false`:

```php
define( 'GD_DISABLE_COMMENTS', false ); // Keep comments enabled
```

---

## Security Features

### Disable Comments

**What it does:** Removes the entire comment system. Closes comments on all post types, hides existing comments, removes comment links from the admin bar and sidebar, removes the Recent Comments widget, strips comment feed links and X-Pingback headers, and redirects direct access to the comments admin page. A daily database check (admin-only) catches imported posts with comments left open. The Discussion settings page stays accessible because form plugins use the "Disallowed Comment Keys" list for spam filtering.

**Keep it on if:** You're building a brochure site, service site, or e-commerce store that doesn't use comments. Most agency sites fall in this category.

**Turn it off if:** You run a blog with active reader discussion, or you use a plugin that depends on the WordPress comment system (some review plugins, BuddyPress, bbPress).

```php
define( 'GD_DISABLE_COMMENTS', false );
```

---

### Restrict REST API

**What it does:** Controls which REST API endpoints are accessible to unauthenticated visitors. Two modes:

**Auto mode** (default): All plugin endpoints work automatically. Only sensitive WordPress core endpoints are blocked for non-admins: `wp/v2/users` (username enumeration), `wp/v2/settings` (site configuration), `wp/v2/application-passwords` (API tokens), `wp/v2/plugins`, `wp/v2/themes`, `wp/v2/block-types`, `wp/v2/sidebars`, `wp/v2/widget-types`, and `wp/v2/widgets`.

**Strict mode**: Blocks all endpoints unless the namespace is in a hardcoded whitelist (WooCommerce, Kadence, Rank Math, Gravity Forms, WPForms, Contact Form 7, Formidable, Yoast, Jetpack, and more).

In both modes, `/wp/v2/users` endpoint is removed for non-admins to prevent username enumeration.

```php
// Switch to strict mode (block everything, whitelist by namespace)
define( 'GD_REST_MODE', 'strict' );

// Auto mode: add more blocked endpoints
define( 'GD_REST_EXTRA_BLOCKED', 'wp/v2/comments,wp/v2/search' );

// Strict mode: add more whitelisted namespaces
define( 'GD_REST_EXTRA_NAMESPACES', 'my-plugin/v1,another-plugin' );

// Change required capability (default: manage_options / Administrators)
define( 'GD_REST_CAPABILITY', 'edit_posts' ); // Editors and above
```

**Keep it on (auto mode) if:** You want every plugin to work out of the box while still blocking the endpoints that expose sensitive data. This is the right choice for most sites.

**Switch to strict mode if:** You're building a high-security site and you want full control over which REST endpoints are publicly accessible. You'll need to whitelist any plugin namespaces that require public access.

**Turn it off if:** You're building a headless WordPress site where the REST API is the primary content delivery mechanism.

```php
define( 'GD_RESTRICT_REST_API', false );
```

---

### Restrict XML-RPC

**What it does:** Requires authentication for XML-RPC requests. Disables `pingback.ping` and `pingback.extensions.getPingbacks` methods. Removes the X-Pingback header, RSD link, Windows Live Writer manifest, REST API discovery link, and shortlink from `<head>`.

**Keep it on if:** You're not using XML-RPC for anything (most sites). XML-RPC is a legacy protocol and the #1 vector for brute force login attacks and pingback amplification DDoS.

**Turn it off if:** You use the WordPress mobile app or a desktop publishing tool that connects via XML-RPC (rare these days). If your host already blocks XML-RPC at the server level (GridPane, WP Engine), this filter won't conflict.

```php
define( 'GD_RESTRICT_XMLRPC', false );
```

---

### Disable Application Passwords

**What it does:** Removes the Application Passwords feature introduced in WordPress 5.6. This feature lets users generate persistent API tokens from their profile page.

**Keep it on if:** Nobody on your site is actively using application passwords. Each token is a persistent credential that grants full API access until manually revoked. If a token leaks, an attacker has persistent access that doesn't get invalidated by a password change.

**Turn it off if:** You use Zapier, Make, or other automation tools that authenticate via application passwords. Or if you have custom scripts that create posts through the REST API using app password tokens.

```php
define( 'GD_DISABLE_APP_PASSWORDS', false );
```

---

### Obscure Login Errors

**What it does:** Replaces WordPress login error messages with a generic "The username or password you entered is incorrect." By default, WordPress tells attackers whether the username or the password was wrong, which helps them confirm valid usernames.

**Keep it on if:** You want to prevent username confirmation through the login page. Works alongside the author archive blocking and REST API user enumeration protection for comprehensive username privacy.

**Turn it off if:** You're the only person logging in and you find the generic errors annoying during development. Or you have a membership site where helping users identify login problems is more important than obscuring usernames.

```php
define( 'GD_OBSCURE_LOGIN_ERRORS', false );
```

---

### Disable Author Archives

**What it does:** 301 redirects `/author/username/` pages to the homepage. Also redirects `/?author=N` requests, which is a common technique for enumerating valid usernames.

**Keep it on if:** Your site is single-author, agency-built, or doesn't benefit from author archive pages. Author archives expose usernames in the URL, which helps attackers.

**Turn it off if:** You run a multi-author publication where readers browse content by author and the author archive pages are part of your content strategy.

```php
define( 'GD_DISABLE_AUTHOR_ARCHIVES', false );
```

---

### Remove WP Version

**What it does:** Strips the WordPress version number from HTML source (`<meta name="generator">`), RSS feeds, and script/style query strings that match the core version. Plugin and theme version strings are left alone.

**Keep it on if:** You don't want automated scanners knowing which WordPress version you're running. Removes information that attackers use to target known vulnerabilities.

**Turn it off if:** You're debugging a caching or CDN issue and temporarily need version query strings to verify assets are updating. Turn it back on when you're done.

```php
define( 'GD_REMOVE_WP_VERSION', false );
```

---

### Disable Self-Pingbacks

**What it does:** Prevents WordPress from sending pingback requests to itself when you link to your own content in a post.

**Keep it on if:** You don't want unnecessary database entries created every time you link to your own posts. Nobody benefits from self-pingbacks.

**Turn it off if:** You specifically want internal pingback references to appear on your posts as a way to show related content (very rare legacy use case).

```php
define( 'GD_DISABLE_SELF_PINGBACKS', false );
```

---

## Environment & Performance

### Environment Awareness

**What it does:** Detects `WP_ENVIRONMENT_TYPE` (a WordPress 5.5+ constant) and adjusts behavior automatically:

- **Local** (green admin bar): Development machine
- **Development** (red admin bar): Dev server
- **Staging** (orange admin bar): Staging/preview site
- **Production**: Normal admin bar, plus a warning if `DISALLOW_FILE_EDIT` is not set

The environment label appears in the top-right corner of the admin bar so you never confuse staging with production.

On non-production environments, the noindex warning banner is automatically suppressed since blocking search engines is expected on staging/dev.

Most managed hosts (GridPane, WP Engine, Kinsta) set `WP_ENVIRONMENT_TYPE` automatically. If yours doesn't:

```php
// Add to wp-config.php
define( 'WP_ENVIRONMENT_TYPE', 'staging' );
```

**Keep it on if:** You work with staging and production environments and want a clear visual indicator of which site you're looking at. Prevents accidental edits on the wrong environment.

**Turn it off if:** You only have one environment and the admin bar coloring isn't useful to you.

```php
define( 'GD_ENVIRONMENT_AWARENESS', false );
```

---

### Limit Post Revisions

**What it does:** Sets `WP_POST_REVISIONS` to 10 if it's not already defined in `wp-config.php`. WordPress stores unlimited revisions by default, which bloats the database over time with hundreds of nearly identical copies of each post.

Respects any existing `WP_POST_REVISIONS` constant in `wp-config.php` — if you've already set it, this feature won't override your choice.

```php
// Custom revision limit
define( 'GD_REVISION_LIMIT', 20 ); // Store up to 20 revisions

// Or set the WordPress native constant directly in wp-config.php
define( 'WP_POST_REVISIONS', 5 ); // This takes priority
```

**Keep it on if:** You want a sensible default without thinking about it. 10 revisions gives enough history to recover from mistakes without database bloat.

**Turn it off if:** You need unlimited revisions for compliance, legal review, or editorial audit trail purposes.

```php
define( 'GD_LIMIT_REVISIONS', false );
```

---

### Throttle Heartbeat API

**What it does:** Slows the WordPress Heartbeat API from 15 seconds to 60 seconds on dashboard and post list pages. Leaves the post editor at the default 15-second interval so autosave and collaborative editing keep working.

The Heartbeat API sends an AJAX request on every interval. On pages where it's only used for login session checks and notification updates, 60 seconds is more than fast enough. Reducing it cuts server load noticeably on hosts with many logged-in users.

```php
// Custom interval (15-120 seconds)
define( 'GD_HEARTBEAT_INTERVAL', 30 );
```

**Keep it on if:** You want to reduce unnecessary server load on admin pages. Especially useful on shared hosting or sites with multiple simultaneous admin users.

**Turn it off if:** You use a plugin that depends on frequent heartbeat updates on non-editor pages (rare, but some real-time notification plugins need it).

```php
define( 'GD_THROTTLE_HEARTBEAT', false );
```

---

### Remove Emoji Scripts

**What it does:** Strips the WordPress emoji detection script and stylesheet from every page load (~10KB combined). Removes the emoji DNS prefetch hint and the TinyMCE emoji plugin from the editor.

**Keep it on if:** Your visitors use modern browsers (any browser released after 2015). Every modern browser and operating system renders emoji natively. The WordPress scripts exist for IE 10 and earlier.

**Turn it off if:** You're supporting a very old browser for a specific audience. In practice, this is the feature people are least likely to disable.

```php
define( 'GD_REMOVE_EMOJI', false );
```

---

### Disable oEmbed Discovery

**What it does:** Stops other sites from embedding your content with a preview card (like how Twitter shows link previews). Removes the oEmbed discovery link from `<head>`, the oEmbed JavaScript, and the oEmbed REST API endpoint for external consumers.

This does NOT affect consuming oEmbeds — embedding YouTube videos, tweets, and other external content in your own posts still works normally.

**Keep it on if:** You don't want external sites auto-generating preview cards of your content, and you want to reduce the public API surface of your site. Most agency and business sites don't benefit from being embeddable.

**Turn it off if:** You want other WordPress sites and platforms to be able to generate rich preview cards when linking to your content. Useful for media sites, blogs, and content publishers that want maximum distribution.

```php
define( 'GD_DISABLE_OEMBED_DISCOVERY', false );
```

---

## Admin UX

### Dashboard Support Widget

**What it does:** Removes the default "WordPress Events and News" widget from the dashboard and replaces it with a support widget showing your agency's contact info.

**Customize it for your agency:**

```php
define( 'GD_SUPPORT_NAME',  'Your Agency Name' );
define( 'GD_SUPPORT_EMAIL', 'help@youragency.com' );
define( 'GD_SUPPORT_PHONE', '555-555-5555' );
define( 'GD_SUPPORT_URL',   'https://youragency.com' );
define( 'GD_SUPPORT_NOTE',  'Hosting clients: website updates are included in your plan.' );
```

All five constants are optional. Any you don't define fall back to the [Garrett Digital](https://www.garrettdigital.com) defaults. Set all five to fully white-label the widget for your own agency or business.

**Keep it on if:** You manage client sites and want your contact info visible on the dashboard. The default WordPress Events and News widget is distracting noise for non-technical clients.

**Turn it off if:** You're using this on your own site (not a client site) and prefer the default dashboard, or you have another plugin managing dashboard widgets.

```php
define( 'GD_DASHBOARD_WIDGET', false );
```

---

### Disable Admin Email Check

**What it does:** Removes the "Please verify your administration email address" nag screen that WordPress shows every 6 months. This overlay blocks the entire admin until the user clicks through it.

**Keep it on if:** You manage client sites. Clients don't understand this prompt, can't dismiss it without clicking the right button, and it generates support tickets. On agency-managed sites, the admin email is your responsibility, not theirs.

**Turn it off if:** You're the sole admin of your own site and you want the periodic reminder to verify your email is still correct.

```php
define( 'GD_DISABLE_ADMIN_EMAIL_CHECK', false );
```

---

### Custom Admin Footer

**What it does:** Replaces the default "Thank you for creating with WordPress" admin footer with your agency name and link. Also removes the WordPress version number from the right side of the footer.

Uses the same `GD_SUPPORT_NAME` and `GD_SUPPORT_URL` constants as the dashboard widget, so you only configure your agency info once.

**Keep it on if:** You want consistent agency branding throughout the admin. Small touch, but it reinforces who built and maintains the site.

**Turn it off if:** You prefer the default WordPress footer text, or you're not using this on a client site.

```php
define( 'GD_CUSTOM_ADMIN_FOOTER', false );
```

---

### Noindex Warning Banner

**What it does:** Displays a bright red banner across every admin page when search engine indexing is blocked. Checks WordPress core Settings > Reading, Rank Math global noindex settings, and Yoast global noindex settings. Includes direct links to the relevant settings page.

**Environment-aware:** When Environment Awareness is enabled, the noindex warning is automatically suppressed on staging, development, and local environments (where noindex is expected).

**Keep it on if:** You've ever accidentally launched a site with "Discourage search engines" still checked. This banner makes it impossible to miss and has saved more than a few sites from months of invisible traffic loss.

**Turn it off if:** You're running a private intranet or staging site where noindex is permanent and intentional, and you've disabled Environment Awareness.

```php
define( 'GD_NOINDEX_WARNING', false );
```

---

### Status Page

**What it does:** Adds a read-only admin page at **Settings > Site Hardening** showing all active features, their configuration source (wp-config.php or default), REST API mode and configuration, environment type with visual indicator, post revision limit, file editing status, and basic environment info.

No form submissions. No database writes. No toggles. Just a clear overview.

**Keep it on if:** You want anyone managing the site to verify the hardening configuration at a glance.

**Turn it off if:** You don't want other administrators seeing the security configuration details.

```php
define( 'GD_STATUS_PAGE', false );
```

---

## All Constants Reference

```php
// Security
define( 'GD_DISABLE_COMMENTS',          false ); // Keep comments enabled
define( 'GD_RESTRICT_REST_API',         false ); // Leave REST API unrestricted
define( 'GD_RESTRICT_XMLRPC',           false ); // Leave XML-RPC open
define( 'GD_DISABLE_APP_PASSWORDS',     false ); // Keep application passwords
define( 'GD_OBSCURE_LOGIN_ERRORS',      false ); // Keep specific login errors
define( 'GD_DISABLE_AUTHOR_ARCHIVES',   false ); // Keep author archives
define( 'GD_REMOVE_WP_VERSION',         false ); // Keep WP version visible
define( 'GD_DISABLE_SELF_PINGBACKS',    false ); // Keep self-pingbacks

// REST API configuration
define( 'GD_REST_MODE',                 'strict' ); // Use whitelist mode (default: auto)
define( 'GD_REST_CAPABILITY',           'edit_posts' ); // Change required capability
define( 'GD_REST_EXTRA_NAMESPACES',     'my-plugin/v1' ); // Strict mode: add namespaces
define( 'GD_REST_EXTRA_BLOCKED',        'wp/v2/comments' ); // Auto mode: add blocked endpoints

// Environment & Performance
define( 'GD_ENVIRONMENT_AWARENESS',     false ); // Skip environment detection
define( 'GD_REMOVE_EMOJI',              false ); // Keep emoji scripts
define( 'GD_LIMIT_REVISIONS',           false ); // Keep unlimited revisions
define( 'GD_REVISION_LIMIT',            20 );    // Custom revision cap (default: 10)
define( 'GD_THROTTLE_HEARTBEAT',        false ); // Keep default heartbeat (15s)
define( 'GD_HEARTBEAT_INTERVAL',        30 );    // Custom interval in seconds (15-120)
define( 'GD_DISABLE_OEMBED_DISCOVERY',  false ); // Keep oEmbed discovery

// Admin UX
define( 'GD_DASHBOARD_WIDGET',          false ); // Skip support widget
define( 'GD_DISABLE_ADMIN_EMAIL_CHECK', false ); // Keep email verification nag
define( 'GD_CUSTOM_ADMIN_FOOTER',       false ); // Keep default WP footer
define( 'GD_NOINDEX_WARNING',           false ); // Hide noindex banner
define( 'GD_STATUS_PAGE',              false ); // Hide status page

// Branding (used by dashboard widget and admin footer)
define( 'GD_SUPPORT_NAME',  'Your Agency Name' );
define( 'GD_SUPPORT_EMAIL', 'help@youragency.com' );
define( 'GD_SUPPORT_PHONE', '555-555-5555' );
define( 'GD_SUPPORT_URL',   'https://youragency.com' );
define( 'GD_SUPPORT_NOTE',  'Custom note for the dashboard widget.' );
```

---

## Compatibility

- **WordPress:** 5.5+ (environment detection requires 5.5, application passwords requires 5.6)
- **PHP:** 7.4+
- **Tested with:** WooCommerce, Kadence Theme/Blocks, Rank Math, Yoast, Gravity Forms, WPForms, Contact Form 7, Formidable Forms, Elementor, Beaver Builder, LearnDash, MemberPress, SureCart
- **Hosting:** Works alongside server-level security on GridPane, WP Engine, Rocket.net, Kinsta, Cloudways, and similar managed hosts. No conflicts with host-level WAF, brute force protection, or XML-RPC blocking.

## When You Don't Need a Separate Security Plugin

If your hosting provider handles brute force protection, WAF, and malware scanning (GridPane, WP Engine, Rocket.net, Kinsta, etc.), this MU plugin covers the remaining application-level hardening. You likely don't need Wordfence, Solid Security, or similar heavy security plugins on top of this.

**What this plugin handles that hosting typically doesn't:**

- REST API restriction with auto-detection or per-namespace whitelisting
- Comment system removal
- Author archive and user enumeration blocking (REST API, author URLs, and login errors)
- Application password disabling
- WordPress version stripping
- Environment-aware admin bar coloring
- Post revision limiting
- Heartbeat API throttling
- oEmbed discovery disabling
- Noindex detection across WordPress core, Rank Math, and Yoast
- Agency-branded dashboard widget and admin footer
- Admin email verification nag removal

## FAQ

**Will this break my site?**
Every feature is independently toggleable. If something stops working, disable that specific feature via `wp-config.php` and everything else keeps running.

**Will this break my page builder / theme / form plugin?**
In auto mode (default), the REST API restriction allows all plugin endpoints automatically. No namespace configuration needed. If you're in strict mode and a plugin stops working, add its namespace via `GD_REST_EXTRA_NAMESPACES`.

**Can I use this on client sites?**
Yes. MIT licensed. Use it, modify it, white-label it. Set the `GD_SUPPORT_*` constants to brand the dashboard widget and admin footer with your own agency info.

**Can I use this alongside Wordfence / Solid Security?**
You can, but you probably don't need to if your host provides brute force and WAF protection. If you do run both, consider disabling the overlapping features in this plugin to avoid conflicts.

**How do I update it?**
Download the latest version and upload it to `/wp-content/mu-plugins/` via SFTP, replacing the existing file. No reactivation needed.

**The admin bar is the wrong color. What happened?**
Check your `WP_ENVIRONMENT_TYPE` constant in `wp-config.php`. If it's set to `staging` or `development`, the admin bar will be colored accordingly. If you're on production and seeing a colored bar, your host may have set the environment type incorrectly.

## Changelog

### 1.3.0
- Added auto/strict REST API modes (auto allows all plugin endpoints, blocks only sensitive core endpoints)
- Added environment awareness with colored admin bar and auto-adjusted noindex behavior
- Added admin email verification nag disable
- Added login error message obscuring
- Added custom admin footer with agency branding
- Added post revision limiting (default: 10)
- Added Heartbeat API throttling on non-editor pages (60s)
- Added oEmbed discovery disable
- Total features: 18, all independently toggleable

### 1.2.0
- Added Kadence (`kb`, `kadence`) to REST API namespace whitelist
- Moved comment database cleanup from `init` to `admin_init`
- Removed REST API `<link>` and shortlink from `<head>`
- Added application passwords disable
- Fixed `wp_kses` escaping in noindex warning banner

### 1.1.0
- Added noindex warning banner with Rank Math and Yoast detection
- Added read-only status page at Settings > Site Hardening

### 1.0.0
- Initial release

## Contributing

Issues and pull requests welcome. If you add support for another plugin's REST API namespace or find an edge case, submit a PR so everyone benefits.

## Credits

Built and maintained by [Garrett Digital](https://www.garrettdigital.com) — web design, development, and SEO for businesses and organizations.

## License

MIT — see [LICENSE](LICENSE) for details.
