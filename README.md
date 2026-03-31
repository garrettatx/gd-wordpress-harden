# GD WordPress Harden

A lightweight WordPress must-use (MU) plugin for agency-standard site hardening. Drop one file into `wp-content/mu-plugins/` and it handles the security and cleanup tasks that every WordPress site needs but nobody wants to configure manually.

Built and maintained by [Garrett Digital](https://www.garrettdigital.com).

All features are **on by default** and independently toggleable via `wp-config.php` constants. Disable anything that doesn't fit your setup.

## Installation

1. Download `gd-wordpress-harden.php`
2. Upload to `/wp-content/mu-plugins/`
3. Done. MU plugins activate automatically.

No settings page to configure. No database entries. No activation step.

To verify: Go to **Settings > Site Hardening** in your WordPress admin to see which features are active.

## Features at a Glance

| Feature                                                      | Constant                     | Default |
| ------------------------------------------------------------ | ---------------------------- | ------- |
| [Disable Comments](#disable-comments)                        | `GD_DISABLE_COMMENTS`        | On      |
| [Restrict REST API](#restrict-rest-api)                      | `GD_RESTRICT_REST_API`       | On      |
| [Restrict XML-RPC](#restrict-xml-rpc)                        | `GD_RESTRICT_XMLRPC`         | On      |
| [Remove Emoji Scripts](#remove-emoji-scripts)                | `GD_REMOVE_EMOJI`            | On      |
| [Dashboard Support Widget](#dashboard-support-widget)        | `GD_DASHBOARD_WIDGET`        | On      |
| [Disable Author Archives](#disable-author-archives)          | `GD_DISABLE_AUTHOR_ARCHIVES` | On      |
| [Remove WP Version](#remove-wp-version)                      | `GD_REMOVE_WP_VERSION`       | On      |
| [Disable Self-Pingbacks](#disable-self-pingbacks)            | `GD_DISABLE_SELF_PINGBACKS`  | On      |
| [Disable Application Passwords](#disable-application-passwords) | `GD_DISABLE_APP_PASSWORDS`   | On      |
| [Noindex Warning Banner](#noindex-warning-banner)            | `GD_NOINDEX_WARNING`         | On      |
| [Status Page](#status-page)                                  | `GD_STATUS_PAGE`             | On      |

To disable any feature, add its constant to `wp-config.php` and set it to `false`:

```php
define( 'GD_DISABLE_COMMENTS', false ); // Keep comments enabled
```

---

## Feature Details

### Disable Comments

**What it does:** Removes the entire comment system from WordPress. Closes comments on all post types, hides existing comments, removes comment links from the admin bar and sidebar, removes the Recent Comments widget, strips comment feed links and X-Pingback headers, and redirects direct access to the comments admin page.

The Discussion settings page stays accessible because several form plugins (Formidable Forms, etc.) use the "Disallowed Comment Keys" list for spam filtering.

A daily database check (runs in admin only) catches any posts created by imports or plugins with comments accidentally left open.

**Why you'd keep it on:** Most brochure sites, service sites, and e-commerce stores don't use comments. Leaving the comment system active creates spam targets and unnecessary database queries.

**Why you might turn it off:** You run a blog with active reader discussion, or you use a plugin that depends on the WordPress comment system (some review plugins, BuddyPress, etc.).

```php
define( 'GD_DISABLE_COMMENTS', false );
```

---

### Restrict REST API

**What it does:** Limits REST API access to logged-in users with the `manage_options` capability (Administrators by default). Unauthenticated requests get a 403 error unless they hit a whitelisted plugin namespace.

Also removes the `/wp/v2/users` endpoint for non-admins, which prevents attackers from enumerating usernames through the REST API.

**Whitelisted namespaces** (work without authentication):

| Namespace         | Plugin                                               |
| ----------------- | ---------------------------------------------------- |
| `contact-form-7`  | Contact Form 7                                       |
| `frm`             | Formidable Forms                                     |
| `wc`              | WooCommerce (also covers `wc-analytics`, `wc-admin`) |
| `wp-block-editor` | WordPress block editor                               |
| `oembed`          | oEmbed embeds                                        |
| `jetpack`         | Jetpack                                              |
| `aiwu`            | AI WP Utils                                          |
| `rankmath`        | Rank Math SEO                                        |
| `yoast`           | Yoast SEO                                            |
| `wpforms`         | WPForms                                              |
| `gravityforms`    | Gravity Forms                                        |
| `kb`              | Kadence Blocks                                       |
| `kadence`         | Kadence Theme                                        |

To add more namespaces:

```php
define( 'GD_REST_EXTRA_NAMESPACES', 'my-plugin/v1,another-plugin' );
```

To change the required capability:

```php
define( 'GD_REST_CAPABILITY', 'edit_posts' ); // Editors and above
```

**Why you'd keep it on:** The REST API exposes user data, post content, and site structure to anyone who knows the endpoint. Restricting it is one of the most impactful hardening steps you can take.

**Why you might turn it off:** You're building a headless WordPress site that serves content through the REST API, or you have a custom frontend that needs unauthenticated access to endpoints not covered by the whitelist. In most cases, adding namespaces via `GD_REST_EXTRA_NAMESPACES` is better than disabling the restriction entirely.

```php
define( 'GD_RESTRICT_REST_API', false );
```

---

### Restrict XML-RPC

**What it does:** Requires authentication for XML-RPC requests. Unauthenticated requests (the source of most brute force and pingback DDoS attacks) get blocked. Disables `pingback.ping` and `pingback.extensions.getPingbacks` methods. Removes the X-Pingback header, RSD link, Windows Live Writer manifest, REST API discovery link, and shortlink from `<head>`.

**Why you'd keep it on:** XML-RPC is a legacy protocol from before the REST API existed. It's the #1 vector for brute force login attacks and pingback amplification DDoS. Most sites have zero legitimate use for it.

**Why you might turn it off:** You use the WordPress mobile app or a desktop publishing tool that connects via XML-RPC (rare these days). You use Jetpack features that still require XML-RPC (Jetpack has been migrating away from it). If your host already blocks XML-RPC at the server level (GridPane, WP Engine, etc.), this filter won't conflict.

```php
define( 'GD_RESTRICT_XMLRPC', false );
```

---

### Remove Emoji Scripts

**What it does:** Strips the WordPress emoji detection script and stylesheet from every page load (~10KB combined). Also removes the emoji DNS prefetch hint and the TinyMCE emoji plugin from the editor.

**Why you'd keep it on:** Every modern browser and operating system renders emoji natively. The WordPress emoji scripts exist to support ancient browsers (IE 10 and earlier) that your visitors aren't using. Removing them shaves ~10KB off every page load for zero functional cost.

**Why you might turn it off:** There's almost no reason to. If you're supporting a very old browser for a specific audience, you could disable this. In practice, this is the feature people are least likely to toggle off.

```php
define( 'GD_REMOVE_EMOJI', false );
```

---

### Dashboard Support Widget

**What it does:** Removes the default "WordPress Events and News" widget from the dashboard and replaces it with a support widget showing your agency's contact information: name, email, phone, website, and a custom note.

Designed for agencies and freelancers who manage client sites. Your clients see your contact info front and center when they log in, instead of WordPress community news they don't need.

**Customize it for your agency:**

```php
define( 'GD_SUPPORT_NAME',  'Your Agency Name' );
define( 'GD_SUPPORT_EMAIL', 'help@youragency.com' );
define( 'GD_SUPPORT_PHONE', '555-555-5555' );
define( 'GD_SUPPORT_URL',   'https://youragency.com' );
define( 'GD_SUPPORT_NOTE',  'Hosting clients: website updates are included in your plan.' );
```

All five constants are optional. Any you don't define will fall back to the [Garrett Digital](https://www.garrettdigital.com) defaults. Set all five to fully white-label the widget for your own agency or business.

**Why you'd keep it on:** Your clients need a clear way to reach you for support, and the default WordPress dashboard news is distracting noise for non-technical users.

**Why you might turn it off:** You're using this on your own personal site (not a client site) and prefer the default WordPress dashboard, or you already have another plugin managing dashboard widgets.

```php
define( 'GD_DASHBOARD_WIDGET', false );
```

---

### Disable Author Archives

**What it does:** 301 redirects `/author/username/` pages to the homepage. Also redirects `/?author=N` requests, which is a common technique attackers use to discover valid usernames on a WordPress site.

**Why you'd keep it on:** Author archives expose usernames in the URL, which gives attackers half the credentials they need for a brute force attempt. On most sites (especially single-author or agency-built sites), author archive pages serve no useful purpose for visitors or SEO.

**Why you might turn it off:** You run a multi-author publication where readers browse content by author. The author archive pages are part of your content strategy and internal linking, and you have other protections against username enumeration (like hiding login URLs or using non-obvious usernames).

```php
define( 'GD_DISABLE_AUTHOR_ARCHIVES', false );
```

---

### Remove WP Version

**What it does:** Strips the WordPress version number from the HTML source (`<meta name="generator">`), RSS feeds, and script/style query strings that match the core version. Plugin and theme version strings in query args are left alone.

**Why you'd keep it on:** Exposing your WordPress version tells attackers exactly which known vulnerabilities apply to your site. Automated vulnerability scanners specifically look for version numbers to target exploits. Removing it is basic security hygiene.

**Why you might turn it off:** You're debugging a caching or CDN issue and temporarily need version query strings to verify assets are updating correctly. Turn this back on when you're done.

```php
define( 'GD_REMOVE_WP_VERSION', false );
```

---

### Disable Self-Pingbacks

**What it does:** Prevents WordPress from sending pingback requests to itself when you link to your own content in a post or page.

**Why you'd keep it on:** Self-pingbacks create unnecessary database entries and serve no real purpose. When you link to your own blog post from another post, WordPress pings itself and creates a comment-like trackback notification. Nobody benefits from this.

**Why you might turn it off:** You specifically want internal pingback references to appear on your posts as a way to show related content. This is a legacy pattern that's been mostly replaced by related posts plugins and manual internal linking.

```php
define( 'GD_DISABLE_SELF_PINGBACKS', false );
```

---

### Disable Application Passwords

**What it does:** Removes the Application Passwords feature introduced in WordPress 5.6. This feature allows users to generate persistent API tokens from their profile page for authenticating REST API and XML-RPC requests without using their main password.

**Why you'd keep it on:** Most sites don't use application passwords. Each token is a persistent credential that grants full API access to the user's account until manually revoked. If someone generates one and forgets about it, or if a token leaks, an attacker has persistent access that doesn't get invalidated by a password change. If nobody on your site is actively using application passwords, disabling them removes an unnecessary attack surface.

**Why you might turn it off:** You use a mobile app, external service, or automation tool that authenticates to your WordPress site via application passwords. Common examples: Zapier or Make integrations that create posts via the REST API, custom deployment scripts, or third-party services that sync content with your site. If you need application passwords for even one integration, keep this feature enabled.

```php
define( 'GD_DISABLE_APP_PASSWORDS', false );
```

---

### Noindex Warning Banner

**What it does:** Displays a bright red banner across the top of every admin page when search engine indexing is blocked. Checks three sources:

- **WordPress core:** Settings > Reading > "Discourage search engines from indexing this site"
- **Rank Math:** Homepage set to noindex, or Posts/Pages globally set to noindex
- **Yoast:** Posts or Pages globally set to noindex

The banner includes direct links to the relevant settings page so you can fix the problem immediately. Visible to all admin users (not just Administrators) so editors and content managers can raise the flag too.

**Why you'd keep it on:** Accidentally leaving "Discourage search engines" checked after launching a site is one of the most common and damaging WordPress mistakes. Sites can go months with zero organic traffic before anyone notices. This banner makes it impossible to miss. It's saved more than a few real sites from months of invisible traffic loss.

**Why you might turn it off:** You're running a staging, development, or private site where noindex is intentional and you don't want the red banner on every admin page.

```php
define( 'GD_NOINDEX_WARNING', false );
```

---

### Status Page

**What it does:** Adds a read-only admin page at **Settings > Site Hardening** showing which features are active, whether each setting comes from `wp-config.php` or the plugin default, REST API whitelist details, user enumeration blocking status, search engine indexing status, dashboard widget contact info, and basic environment info (WordPress version, PHP version, plugin file path).

No form submissions. No database writes. No toggles. Just a clear overview of what's running. Visible only to users with `manage_options` capability (Administrators).

**Why you'd keep it on:** Makes it easy for anyone managing the site to verify the hardening configuration at a glance without reading the PHP file or checking `wp-config.php` via SFTP.

**Why you might turn it off:** You don't want other administrators seeing the security configuration details, or you prefer to keep admin menus minimal.

```php
define( 'GD_STATUS_PAGE', false );
```

---

## Compatibility

- **WordPress:** 5.6+ (application passwords feature requires 5.6)
- **PHP:** 7.4+
- **Tested with:** WooCommerce, Kadence Theme/Blocks, Rank Math, Yoast, Gravity Forms, WPForms, Contact Form 7, Formidable Forms
- **Hosting:** Works alongside server-level security on GridPane, WP Engine, Rocket.net, Kinsta, Cloudways, and similar managed hosts. No conflicts with host-level WAF, brute force protection, or XML-RPC blocking.

## When You Don't Need a Separate Security Plugin

If your hosting provider handles brute force protection, WAF, and malware scanning (GridPane, WP Engine, Rocket.net, Kinsta, etc.), this MU plugin covers the remaining application-level hardening. You likely don't need Wordfence, Solid Security, or similar heavy security plugins on top of this.

**What this plugin handles that hosting typically doesn't:**

- REST API restriction with per-namespace whitelisting
- Comment system removal
- Author archive and user enumeration blocking
- Application password disabling
- WordPress version stripping
- Noindex detection across multiple SEO plugins
- Agency-branded dashboard support widget

## FAQ

**Will this break my site?**
Every feature is independently toggleable. If something stops working, disable that specific feature via `wp-config.php` and everything else keeps running.

**Will this break Kadence / WooCommerce / my form plugin?**
The REST API whitelist includes namespaces for all major WordPress plugins. If a plugin's REST endpoints get blocked, add its namespace via `GD_REST_EXTRA_NAMESPACES` in `wp-config.php`.

**Can I use this on client sites?**
Yes. MIT licensed. Use it, modify it, white-label it. Set the `GD_SUPPORT_*` constants to brand the dashboard widget with your own agency info.

**Can I use this alongside Wordfence / Solid Security?**
You can, but you probably don't need to if your host provides brute force and WAF protection. If you do run both, consider disabling `GD_RESTRICT_XMLRPC` and `GD_DISABLE_AUTHOR_ARCHIVES` in this plugin since your security plugin likely handles those.

**How do I update it?**
Download the latest version from this repo and upload it to `/wp-content/mu-plugins/` via SFTP, replacing the existing file. No reactivation needed — MU plugins are always active.

## Changelog

### 1.2.0

- Added Kadence (`kb`, `kadence`) to REST API namespace whitelist
- Moved comment database cleanup from `init` to `admin_init` (no longer runs on front-end requests)
- Removed REST API `<link>` and shortlink from `<head>`
- Added application passwords disable (`GD_DISABLE_APP_PASSWORDS`)
- Fixed `wp_kses` escaping in noindex warning banner

### 1.1.0

- Added noindex warning banner with Rank Math and Yoast detection
- Added read-only status page at Settings > Site Hardening

### 1.0.0

- Initial release

## Contributing

Issues and pull requests welcome. If you add support for another plugin's REST API namespace, please submit a PR so everyone benefits.

## Credits

Built and maintained by [Garrett Digital](https://www.garrettdigital.com) — web design, development, and SEO for businesses and organizations.

## License

MIT — see [LICENSE](LICENSE) for details.
