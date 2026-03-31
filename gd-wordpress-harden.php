<?php
/**
 * Plugin Name: GD Site Hardening
 * Description: Garrett Digital agency-standard WordPress hardening. 18 features, all independently toggleable via wp-config.php constants. Disables comments, restricts REST API (auto and strict modes), blocks XML-RPC abuse, removes emoji scripts, adds dashboard support widget, disables author archives, removes version info, disables application passwords, detects environment type with colored admin bar, disables admin email verification nag, obscures login errors, adds custom admin footer, limits post revisions, throttles Heartbeat API, disables oEmbed discovery, and warns when search engine indexing is blocked.
 * Version: 1.3.2
 * Author: Garrett Digital
 * Author URI: https://www.garrettdigital.com
 *
 * INSTALLATION
 * ============
 * Upload this file to: /wp-content/mu-plugins/gd-site-hardening.php
 * It activates automatically. No settings page — configured via constants.
 *
 * CONFIGURATION
 * =============
 * Every feature is ON by default. You only need to add a constant
 * to wp-config.php if you want to TURN SOMETHING OFF.
 *
 * How it works:
 *   true  (or not set) = feature is ON
 *   false              = feature is OFF
 *
 * Example: To turn off comment blocking (let comments work normally):
 *   define( 'GD_BLOCK_COMMENTS', false );
 *
 * All toggles:
 *
 *   SECURITY
 *   define( 'GD_BLOCK_COMMENTS',      false ); // Comments work normally
 *   define( 'GD_LOCK_REST_API',       false ); // REST API stays open
 *   define( 'GD_LOCK_XMLRPC',         false ); // XML-RPC stays open
 *   define( 'GD_BLOCK_APP_PASSWORDS', false ); // Application passwords enabled
 *   define( 'GD_HIDE_LOGIN_ERRORS',   false ); // Specific login error messages shown
 *   define( 'GD_BLOCK_AUTHOR_PAGES',  false ); // Author archive pages work
 *   define( 'GD_HIDE_VERSION',        false ); // WordPress version visible in source
 *   define( 'GD_BLOCK_SELF_PINGS',    false ); // Self-pingbacks allowed
 *
 *   ENVIRONMENT & PERFORMANCE
 *   define( 'GD_ENV_AWARENESS',       false ); // No colored admin bar or environment detection
 *   define( 'GD_WARN_FILE_EDIT',      false ); // No production warning about DISALLOW_FILE_EDIT
 *   define( 'GD_STRIP_EMOJI',         false ); // Emoji scripts stay loaded
 *   define( 'GD_CAP_REVISIONS',       false ); // Unlimited post revisions
 *   define( 'GD_SLOW_HEARTBEAT',      false ); // Default 15s heartbeat everywhere
 *   define( 'GD_BLOCK_OEMBED',        false ); // oEmbed discovery stays on
 *
 *   ADMIN UX
 *   define( 'GD_SUPPORT_WIDGET',      false ); // Default WordPress dashboard widget
 *   define( 'GD_BLOCK_EMAIL_NAG',     false ); // Admin email verification nag shows
 *   define( 'GD_AGENCY_FOOTER',       false ); // Default WordPress admin footer
 *   define( 'GD_NOINDEX_WARNING',     false ); // Noindex warning banner hidden
 *   define( 'GD_STATUS_PAGE',         false ); // Status page hidden
 *
 * REST API MODE
 * ==============
 * Two modes for REST API restriction:
 *
 *   'auto'   (default) — All plugin endpoints work automatically.
 *                         Only sensitive core endpoints are blocked
 *                         (users, settings, application-passwords,
 *                         plugins, themes, widgets). Best for most sites.
 *
 *   'strict' — Everything blocked unless namespace is whitelisted.
 *              Use on high-security sites where you control the stack.
 *
 *   define( 'GD_REST_MODE', 'strict' );
 *
 * REST API NAMESPACE WHITELIST (strict mode only)
 * ================================================
 * Plugins that need public REST API access (Contact Form 7, Formidable,
 * WooCommerce, etc.) are whitelisted by default. To add more:
 *
 *   define( 'GD_REST_EXTRA_NAMESPACES', 'my-plugin/v1,another-plugin' );
 *
 * Comma-separated list of namespace prefixes to allow for non-admins.
 *
 * DASHBOARD WIDGET CONTACT INFO
 * ==============================
 * Override the default Garrett Digital contact info:
 *
 *   define( 'GD_SUPPORT_NAME',  'Your Agency' );
 *   define( 'GD_SUPPORT_EMAIL', 'help@youragency.com' );
 *   define( 'GD_SUPPORT_PHONE', '555-555-5555' );
 *   define( 'GD_SUPPORT_URL',   'https://youragency.com' );
 *   define( 'GD_SUPPORT_NOTE',  'Custom note about your services.' );
 *
 * IMPORTANT NOTES
 * ===============
 * - Comments: The Discussion settings page stays accessible so the
 *   "Disallowed Comment Keys" list remains available for form spam
 *   filtering (used by Formidable Forms and others).
 *
 * - REST API: Restricted to users with manage_options capability
 *   (Administrators) by default. Whitelisted plugin namespaces still
 *   work for unauthenticated requests. To change the required
 *   capability:
 *
 *     define( 'GD_REST_CAPABILITY', 'edit_posts' ); // Editors and above
 *
 * - XML-RPC: Restricted to authenticated users only (not fully
 *   disabled). If you also disable XML-RPC at the server level
 *   (Gridpane, Nginx, .htaccess), this filter won't conflict.
 *
 * - Author archives: Redirects /author/username/ to the homepage
 *   with a 301. Also blocks /?author=N user enumeration.
 *
 * - Noindex warning: Shows a bright red banner across the top of
 *   every admin page when WordPress is set to discourage search
 *   engines. Also checks Rank Math and Yoast global noindex settings.
 *
 * CHANGELOG
 * =========
 * 1.3.2 - Separated file edit warning into its own toggle (GD_WARN_FILE_EDIT)
 *         so you can keep the colored admin bar without the DISALLOW_FILE_EDIT
 *         nag. Total features: 19.
 * 1.3.1 - Renamed all constants for intuitive true/false toggling. No more
 *         double negatives. true = feature ON, false = feature OFF. See
 *         README for the full rename map.
 * 1.3.0 - Added auto/strict REST API modes (auto default: all plugin endpoints
 *         work automatically, only sensitive core endpoints blocked). Added
 *         environment awareness (colored admin bar, auto-suppressed noindex
 *         warning on staging/dev, DISALLOW_FILE_EDIT production warning).
 *         Added admin email verification nag disable. Added login error
 *         message obscuring. Added custom admin footer with agency branding.
 *         Added post revision limiting (default 10). Added Heartbeat API
 *         throttling on non-editor pages (60s). Added oEmbed discovery
 *         disable. Total features: 18, all independently toggleable.
 * 1.2.0 - Added Kadence (kb, kadence) to REST API whitelist. Moved comment DB
 *         cleanup to admin_init (off front-end requests). Removed REST API link
 *         and shortlink from wp_head. Added application passwords disable.
 * 1.1.0 - Added noindex warning banner, read-only status page
 * 1.0.0 - Initial release
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GD_HARDENING_VERSION', '1.3.2' );

/**
 * Helper: check if a feature is enabled.
 * Features default to ON (true). Set the constant to false to disable.
 */
function gd_feature_enabled( $constant ) {
    if ( defined( $constant ) ) {
        return (bool) constant( $constant );
    }
    return true; // ON by default
}

// =====================================================================
// 1. DISABLE COMMENTS SITEWIDE
// =====================================================================
// Removes comment UI from admin, closes comments on all post types,
// hides existing comments, and removes comment-related admin bar items.
// The Discussion settings page remains accessible for the
// "Disallowed Comment Keys" spam filter used by form plugins.
// =====================================================================

if ( gd_feature_enabled( 'GD_BLOCK_COMMENTS' ) ) {

    // Close comments and pingbacks on all post types.
    add_filter( 'comments_open', '__return_false', 20, 2 );
    add_filter( 'pings_open', '__return_false', 20, 2 );

    // Return empty array for any comment queries (hides existing comments).
    add_filter( 'comments_array', '__return_empty_array', 10, 2 );

    // Remove comment count from admin bar.
    add_action( 'wp_before_admin_bar_render', function () {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu( 'comments' );
    });

    // Remove Comments from the admin sidebar menu.
    add_action( 'admin_menu', function () {
        remove_menu_page( 'edit-comments.php' );
    });

    // Redirect any direct access to edit-comments.php.
    add_action( 'admin_init', function () {
        global $pagenow;
        if ( $pagenow === 'edit-comments.php' ) {
            wp_safe_redirect( admin_url() );
            exit;
        }
    });

    // Remove comment-related items from the admin bar on the front end.
    add_action( 'init', function () {
        if ( is_admin_bar_showing() ) {
            remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
        }
    });

    // Remove comment support from all registered post types.
    add_action( 'init', function () {
        $post_types = get_post_types( array(), 'names' );
        foreach ( $post_types as $post_type ) {
            if ( post_type_supports( $post_type, 'comments' ) ) {
                remove_post_type_support( $post_type, 'comments' );
                remove_post_type_support( $post_type, 'trackbacks' );
            }
        }
    }, 100 );

    // Remove Recent Comments widget.
    add_action( 'widgets_init', function () {
        unregister_widget( 'WP_Widget_Recent_Comments' );
    });

    // Remove comment feed links from <head>.
    add_filter( 'feed_links_show_comments_feed', '__return_false' );

    // Remove X-Pingback header.
    add_filter( 'wp_headers', function ( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    });

    // Close comments on existing posts via the database on plugin load.
    // Runs once per day to catch any imported or programmatically created posts.
    // Uses admin_init (not init) to avoid running on front-end requests.
    add_action( 'admin_init', function () {
        $transient_key = 'gd_comments_closed_check';
        if ( false === get_transient( $transient_key ) ) {
            global $wpdb;
            $wpdb->query( "UPDATE {$wpdb->posts} SET comment_status = 'closed', ping_status = 'closed' WHERE comment_status != 'closed' OR ping_status != 'closed'" );
            set_transient( $transient_key, 1, DAY_IN_SECONDS );
        }
    });
}

// =====================================================================
// 2. RESTRICT REST API
// =====================================================================
// Two modes controlled by GD_REST_MODE:
//
//   'auto'   (default) — Allows all plugin REST endpoints. Blocks only
//                         sensitive core endpoints (users, settings,
//                         application-passwords). This is the right
//                         choice for most sites. Plugins handle their
//                         own authentication on their own endpoints.
//
//   'strict' — Blocks ALL REST endpoints for non-admins unless the
//              namespace is in the hardcoded whitelist. Use this on
//              sites that need full lockdown and where you control
//              exactly which plugins need public REST access.
//
//   define( 'GD_REST_MODE', 'strict' ); // in wp-config.php
//
// In both modes, user enumeration via /wp/v2/users is always blocked
// for non-admins.
// =====================================================================

if ( gd_feature_enabled( 'GD_LOCK_REST_API' ) ) {

    add_filter( 'rest_authentication_errors', function ( $result ) {

        // If a previous auth check already failed, respect that.
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        // Logged-in users with the required capability pass through.
        $capability = defined( 'GD_REST_CAPABILITY' ) ? GD_REST_CAPABILITY : 'manage_options';
        if ( current_user_can( $capability ) ) {
            return $result;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
        $mode = defined( 'GD_REST_MODE' ) ? GD_REST_MODE : 'auto';

        // ── AUTO MODE ──
        // Allow everything except sensitive core endpoints.
        // Plugins register their own REST routes and handle their own
        // authentication. We only need to protect the WordPress core
        // endpoints that expose sensitive data.
        if ( 'auto' === $mode ) {

            $blocked = gd_get_blocked_endpoints();

            foreach ( $blocked as $endpoint ) {
                if ( strpos( $request_uri, '/wp-json/' . $endpoint ) !== false
                    || strpos( $request_uri, '?rest_route=/' . $endpoint ) !== false ) {
                    return new WP_Error(
                        'rest_forbidden',
                        'REST API access restricted.',
                        array( 'status' => 403 )
                    );
                }
            }

            // Everything else passes through in auto mode.
            return $result;
        }

        // ── STRICT MODE ──
        // Block everything unless the namespace is whitelisted.
        $whitelisted = gd_get_rest_whitelist();

        foreach ( $whitelisted as $namespace ) {
            if ( strpos( $request_uri, '/wp-json/' . $namespace ) !== false
                || strpos( $request_uri, '?rest_route=/' . $namespace ) !== false ) {
                return $result;
            }
        }

        return new WP_Error(
            'rest_forbidden',
            'REST API access restricted.',
            array( 'status' => 403 )
        );
    }, 20 );

    // Block user enumeration via REST API in both modes.
    add_filter( 'rest_endpoints', function ( $endpoints ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            unset( $endpoints['/wp/v2/users'] );
            unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        }
        return $endpoints;
    });
}

/**
 * Sensitive core endpoints blocked in auto mode.
 * These expose user data, site settings, or authentication tokens
 * that should not be accessible to unauthenticated requests.
 *
 * To add more blocked endpoints:
 *   define( 'GD_REST_EXTRA_BLOCKED', 'wp/v2/comments,wp/v2/search' );
 */
function gd_get_blocked_endpoints() {
    $blocked = array(
        'wp/v2/users',                    // Username enumeration
        'wp/v2/settings',                 // Site configuration
        'wp/v2/application-passwords',    // API token management
        'wp/v2/plugins',                  // Plugin list and status
        'wp/v2/themes',                   // Theme list and status
        'wp/v2/block-types',              // Registered block info
        'wp/v2/sidebars',                 // Widget areas
        'wp/v2/widget-types',             // Available widget types
        'wp/v2/widgets',                  // Active widgets
    );

    if ( defined( 'GD_REST_EXTRA_BLOCKED' ) && GD_REST_EXTRA_BLOCKED ) {
        $extras = array_map( 'trim', explode( ',', GD_REST_EXTRA_BLOCKED ) );
        $blocked = array_merge( $blocked, $extras );
    }

    return $blocked;
}

/**
 * Namespace whitelist for STRICT mode only.
 * Not used in auto mode (auto mode allows all namespaces
 * and blocks only specific sensitive endpoints).
 */
function gd_get_rest_whitelist() {
    $whitelisted = array(
        'contact-form-7',
        'frm',
        'wc',
        'wp-block-editor',
        'oembed',
        'jetpack',
        'aiwu',
        'rankmath',
        'yoast',
        'wpforms',
        'gravityforms',
        'kb',          // Kadence Blocks
        'kadence',     // Kadence Theme
    );

    if ( defined( 'GD_REST_EXTRA_NAMESPACES' ) && GD_REST_EXTRA_NAMESPACES ) {
        $extras = array_map( 'trim', explode( ',', GD_REST_EXTRA_NAMESPACES ) );
        $whitelisted = array_merge( $whitelisted, $extras );
    }

    return $whitelisted;
}

// =====================================================================
// 3. RESTRICT XML-RPC
// =====================================================================
// Allows XML-RPC for authenticated users only. Unauthenticated
// requests (brute force, pingback abuse) get blocked.
// =====================================================================

if ( gd_feature_enabled( 'GD_LOCK_XMLRPC' ) ) {

    add_filter( 'xmlrpc_enabled', function () {
        return is_user_logged_in();
    });

    add_filter( 'xmlrpc_methods', function ( $methods ) {
        unset( $methods['pingback.ping'] );
        unset( $methods['pingback.extensions.getPingbacks'] );
        return $methods;
    });

    add_filter( 'wp_headers', function ( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    });

    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );   // Remove REST API <link> from <head>
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );       // Remove shortlink from <head>
}

// =====================================================================
// 4. REMOVE EMOJI SCRIPTS
// =====================================================================

if ( gd_feature_enabled( 'GD_STRIP_EMOJI' ) ) {

    add_action( 'init', function () {
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    });

    add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
        if ( 'dns-prefetch' === $relation_type ) {
            $urls = array_filter( $urls, function ( $url ) {
                $check = is_array( $url ) ? ( $url['href'] ?? '' ) : $url;
                return strpos( $check, 'https://s.w.org/images/core/emoji/' ) === false;
            });
        }
        return $urls;
    }, 10, 2 );

    add_filter( 'tiny_mce_plugins', function ( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        }
        return $plugins;
    });
}

// =====================================================================
// 5. DASHBOARD SUPPORT WIDGET
// =====================================================================

if ( gd_feature_enabled( 'GD_SUPPORT_WIDGET' ) ) {

    add_action( 'wp_dashboard_setup', function () {
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );

        wp_add_dashboard_widget(
            'gd_support_widget',
            gd_get_support_name() . ' — Website Support',
            'gd_render_support_widget'
        );
    });
}

function gd_get_support_name() {
    return defined( 'GD_SUPPORT_NAME' ) ? GD_SUPPORT_NAME : 'Garrett Digital';
}

function gd_render_support_widget() {
    $email = defined( 'GD_SUPPORT_EMAIL' ) ? GD_SUPPORT_EMAIL : 'support@garrettdigital.com';
    $phone = defined( 'GD_SUPPORT_PHONE' ) ? GD_SUPPORT_PHONE : '512-730-1872';
    $url   = defined( 'GD_SUPPORT_URL' )   ? GD_SUPPORT_URL   : 'https://www.garrettdigital.com';
    $note  = defined( 'GD_SUPPORT_NOTE' )  ? GD_SUPPORT_NOTE  : 'Hosting & Maintenance clients: website updates are included in your plan.';

    echo '<div style="line-height: 1.7;">';
    echo '<p>For website questions, updates, or technical issues:</p>';
    echo '<p>';
    echo '<strong>Email:</strong> <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a><br>';
    echo '<strong>Phone:</strong> <a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a><br>';
    echo '<strong>Web:</strong> <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( preg_replace( '#^https?://(www\.)?#', '', $url ) ) . '</a>';
    echo '</p>';
    if ( $note ) {
        echo '<p style="margin-top: 8px; padding: 8px 12px; background: #f0f6fc; border-left: 3px solid #2271b1; font-size: 13px;">' . esc_html( $note ) . '</p>';
    }
    echo '</div>';
}

// =====================================================================
// 6. DISABLE AUTHOR ARCHIVES
// =====================================================================

if ( gd_feature_enabled( 'GD_BLOCK_AUTHOR_PAGES' ) ) {

    add_action( 'template_redirect', function () {
        if ( is_author() ) {
            wp_redirect( home_url( '/' ), 301 );
            exit;
        }
    });

    add_action( 'init', function () {
        if ( isset( $_GET['author'] ) && ! is_admin() ) {
            wp_redirect( home_url( '/' ), 301 );
            exit;
        }
    });
}

// =====================================================================
// 7. REMOVE WORDPRESS VERSION
// =====================================================================

if ( gd_feature_enabled( 'GD_HIDE_VERSION' ) ) {

    remove_action( 'wp_head', 'wp_generator' );
    add_filter( 'the_generator', '__return_empty_string' );
    add_filter( 'style_loader_src', 'gd_remove_version_query', 15, 1 );
    add_filter( 'script_loader_src', 'gd_remove_version_query', 15, 1 );
}

function gd_remove_version_query( $src ) {
    if ( $src && strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) !== false ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}

// =====================================================================
// 8. DISABLE SELF-PINGBACKS
// =====================================================================

if ( gd_feature_enabled( 'GD_BLOCK_SELF_PINGS' ) ) {

    add_action( 'pre_ping', function ( &$links ) {
        $home = home_url();
        foreach ( $links as $i => $link ) {
            if ( strpos( $link, $home ) === 0 ) {
                unset( $links[ $i ] );
            }
        }
    });
}

// =====================================================================
// 8b. DISABLE APPLICATION PASSWORDS
// =====================================================================
// WordPress 5.6+ includes application passwords, which create
// persistent API tokens. Most agency sites don't need them, and
// they represent an additional attack surface if unused.
// =====================================================================

if ( gd_feature_enabled( 'GD_BLOCK_APP_PASSWORDS' ) ) {

    add_filter( 'wp_is_application_passwords_available', '__return_false' );
}

// =====================================================================
// 9. ENVIRONMENT AWARENESS
// =====================================================================
// Detects WP_ENVIRONMENT_TYPE (local, development, staging, production)
// and adjusts behavior automatically:
//   - Colors the admin bar so you never confuse staging with production
//   - Labels the environment in the admin bar
//   - Suppresses the noindex warning on non-production (noindex expected)
//   - On production: warns if DISALLOW_FILE_EDIT is not set
//
// WP_ENVIRONMENT_TYPE is a WordPress 5.5+ constant. Most managed hosts
// (GridPane, WP Engine, Kinsta, etc.) set it automatically. If not set,
// the plugin defaults to 'production' (safest assumption).
//
//   define( 'WP_ENVIRONMENT_TYPE', 'staging' ); // in wp-config.php
// =====================================================================

if ( gd_feature_enabled( 'GD_ENV_AWARENESS' ) ) {

    add_action( 'admin_head', 'gd_environment_admin_bar_color' );
    add_action( 'wp_head', 'gd_environment_admin_bar_color' );

    // Warn on production if DISALLOW_FILE_EDIT is not enforced.
    // Has its own toggle so you can keep the colored admin bar
    // without the file edit nag.
    if ( gd_feature_enabled( 'GD_WARN_FILE_EDIT' ) ) {
        add_action( 'admin_notices', function () {
            if ( 'production' !== gd_get_environment_type() ) {
                return;
            }
            if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
                return;
            }
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
            echo '<div class="notice notice-warning"><p>';
            echo '<strong>GD Hardening:</strong> This is a production site but <code>DISALLOW_FILE_EDIT</code> is not enabled. ';
            echo 'Add <code>define( \'DISALLOW_FILE_EDIT\', true );</code> to <code>wp-config.php</code>.';
            echo '</p></div>';
        });
    }
}

/**
 * Get the current WordPress environment type.
 * Returns: local, development, staging, or production.
 */
function gd_get_environment_type() {
    if ( function_exists( 'wp_get_environment_type' ) ) {
        return wp_get_environment_type();
    }
    return 'production';
}

/**
 * Color the admin bar based on environment type.
 */
function gd_environment_admin_bar_color() {
    if ( ! is_admin_bar_showing() ) {
        return;
    }

    $env = gd_get_environment_type();

    $env_config = array(
        'local'       => array( 'color' => '#00a32a', 'label' => 'LOCAL' ),
        'development' => array( 'color' => '#d63638', 'label' => 'DEV' ),
        'staging'     => array( 'color' => '#dba617', 'label' => 'STAGING' ),
    );

    if ( ! isset( $env_config[ $env ] ) ) {
        return;
    }

    $color = $env_config[ $env ]['color'];
    $label = $env_config[ $env ]['label'];

    echo '<style>';
    echo '#wpadminbar { background: ' . esc_attr( $color ) . ' !important; }';
    echo '#wpadminbar .ab-empty-item, #wpadminbar a.ab-item, #wpadminbar > #wp-toolbar span.ab-label, #wpadminbar > #wp-toolbar span.noticon { color: #fff !important; }';
    echo '#wpadminbar .ab-item::before, #wpadminbar #adminbarsearch::before { color: rgba(255,255,255,0.8) !important; }';
    echo '#wpadminbar::after { content: "' . esc_attr( $label ) . '"; position: fixed; right: 10px; top: 0; color: rgba(255,255,255,0.7); font-size: 11px; font-weight: 700; letter-spacing: 1px; line-height: 32px; z-index: 99999; pointer-events: none; }';
    echo '</style>';
}

// =====================================================================
// 10. DISABLE ADMIN EMAIL VERIFICATION
// =====================================================================
// WordPress periodically asks administrators to verify their email
// address (every 6 months). On agency-managed client sites, this
// causes confusion and support tickets. Clients don't understand
// the prompt and often ignore it, creating a nagging overlay on
// every admin page visit until they deal with it.
// =====================================================================

if ( gd_feature_enabled( 'GD_BLOCK_EMAIL_NAG' ) ) {

    add_filter( 'admin_email_check_interval', '__return_false' );
}

// =====================================================================
// 11. OBSCURE LOGIN ERROR MESSAGES
// =====================================================================
// By default, WordPress tells you whether the username OR the password
// was wrong ("Unknown username" vs "Incorrect password for user X").
// This helps attackers confirm valid usernames. This feature replaces
// all login error messages with a generic response that doesn't
// reveal which credential was incorrect.
// =====================================================================

if ( gd_feature_enabled( 'GD_HIDE_LOGIN_ERRORS' ) ) {

    add_filter( 'login_errors', function () {
        return '<strong>Error:</strong> The username or password you entered is incorrect. <a href="' . esc_url( wp_lostpassword_url() ) . '">Lost your password?</a>';
    });
}

// =====================================================================
// 12. CUSTOM ADMIN FOOTER
// =====================================================================
// Replaces the default "Thank you for creating with WordPress" admin
// footer with your agency branding. Configurable via constants.
// Links to the same URL as the dashboard support widget by default.
// =====================================================================

if ( gd_feature_enabled( 'GD_AGENCY_FOOTER' ) ) {

    add_filter( 'admin_footer_text', function () {
        $name = defined( 'GD_SUPPORT_NAME' ) ? GD_SUPPORT_NAME : 'Garrett Digital';
        $url  = defined( 'GD_SUPPORT_URL' )  ? GD_SUPPORT_URL  : 'https://www.garrettdigital.com';
        return 'Built by <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $name ) . '</a>';
    });

    // Remove the WordPress version from the right side of the footer.
    add_filter( 'update_footer', '__return_empty_string', 11 );
}

// =====================================================================
// 13. LIMIT POST REVISIONS
// =====================================================================
// WordPress stores unlimited post revisions by default, which bloats
// the database over time. This enforces a sensible default (10) unless
// WP_POST_REVISIONS is already defined in wp-config.php.
//
// Override the default:
//   define( 'GD_REVISION_LIMIT', 20 ); // Store up to 20 revisions
//
// Or disable the limit entirely by setting WP_POST_REVISIONS in
// wp-config.php before this plugin loads (MU plugins load first,
// but wp-config.php constants are set before any plugins).
// =====================================================================

if ( gd_feature_enabled( 'GD_CAP_REVISIONS' ) ) {

    // Only set the limit if WordPress core constant is not already defined.
    // wp-config.php constants are set before MU plugins load, so if
    // WP_POST_REVISIONS exists, the site owner made a deliberate choice.
    if ( ! defined( 'WP_POST_REVISIONS' ) ) {
        $limit = defined( 'GD_REVISION_LIMIT' ) ? (int) GD_REVISION_LIMIT : 10;
        define( 'WP_POST_REVISIONS', $limit );
    }
}

// =====================================================================
// 14. THROTTLE HEARTBEAT API
// =====================================================================
// The WordPress Heartbeat API sends AJAX requests every 15 seconds on
// all admin pages. On the post editor, this powers autosave and
// collaborative editing — useful. On the dashboard and post list pages,
// it just burns server resources.
//
// This throttles the heartbeat to 60 seconds on non-editor pages and
// leaves it at the default (15 seconds) on the post editor where
// autosave matters.
//
// Override the interval:
//   define( 'GD_HEARTBEAT_INTERVAL', 30 ); // seconds (15-120)
// =====================================================================

if ( gd_feature_enabled( 'GD_SLOW_HEARTBEAT' ) ) {

    add_action( 'init', function () {

        // Don't touch the post editor — autosave needs the fast heartbeat.
        global $pagenow;
        if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
            return;
        }

        $interval = defined( 'GD_HEARTBEAT_INTERVAL' ) ? (int) GD_HEARTBEAT_INTERVAL : 60;
        $interval = max( 15, min( 120, $interval ) ); // Clamp to valid range.

        add_filter( 'heartbeat_settings', function ( $settings ) use ( $interval ) {
            $settings['interval'] = $interval;
            return $settings;
        });
    });
}

// =====================================================================
// 15. DISABLE oEMBED DISCOVERY
// =====================================================================
// WordPress lets other sites embed your content with a preview card
// (like how Twitter shows link previews). This feature disables the
// discovery endpoint and removes the oEmbed-related tags from <head>.
//
// This is different from CONSUMING oEmbeds (embedding YouTube videos,
// tweets, etc. in your own content), which still works normally.
//
// Most agency and business sites don't benefit from being embeddable
// by external sites, and the discovery endpoint adds an unnecessary
// public API surface.
// =====================================================================

if ( gd_feature_enabled( 'GD_BLOCK_OEMBED' ) ) {

    // Remove oEmbed discovery link from <head>.
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

    // Remove oEmbed-specific JavaScript from front end.
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );

    // Disable the oEmbed REST API endpoint for external consumers.
    add_filter( 'rest_endpoints', function ( $endpoints ) {
        unset( $endpoints['/oembed/1.0/embed'] );
        return $endpoints;
    });

    // Remove oEmbed response headers.
    remove_filter( 'template_redirect', 'rest_output_link_header', 11 );
}

// =====================================================================
// 16. NOINDEX WARNING BANNER
// =====================================================================
// Shows a persistent red banner across the top of every admin page
// when the site is set to discourage search engines. Checks:
//   - WordPress core: Settings > Reading > "Discourage search engines"
//   - Rank Math: homepage noindex, or posts/pages globally noindexed
//   - Yoast: posts or pages globally set to noindex
//
// Visible to ALL admin users (not just Administrators) so editors
// and content managers can flag the issue too.
// =====================================================================

if ( gd_feature_enabled( 'GD_NOINDEX_WARNING' ) ) {
    add_action( 'admin_notices', 'gd_noindex_warning_notice' );
    add_action( 'admin_head', 'gd_noindex_warning_styles' );
}

function gd_noindex_warning_notice() {

    // In non-production environments, noindex is expected. Don't nag.
    if ( gd_feature_enabled( 'GD_ENV_AWARENESS' ) ) {
        $env = gd_get_environment_type();
        if ( 'production' !== $env ) {
            return;
        }
    }

    $warnings = gd_detect_noindex_settings();

    if ( empty( $warnings ) ) {
        return;
    }

    echo '<div class="gd-noindex-banner">';
    echo '<div class="gd-noindex-banner-inner">';
    echo '<span class="gd-noindex-icon">&#9888;</span>';
    echo '<div class="gd-noindex-content">';
    echo '<strong>SEARCH ENGINE INDEXING IS BLOCKED</strong>';
    echo '<span class="gd-noindex-sep"> — </span>';
    echo '<span>This site is telling Google not to index it. ';

    if ( count( $warnings ) === 1 ) {
        echo wp_kses( $warnings[0], array( 'a' => array( 'href' => array() ) ) );
    } else {
        echo '</span><ul class="gd-noindex-list">';
        foreach ( $warnings as $warning ) {
            echo '<li>' . wp_kses( $warning, array( 'a' => array( 'href' => array() ) ) ) . '</li>';
        }
        echo '</ul>';
    }

    echo '</div>'; // .gd-noindex-content
    echo '</div>'; // .gd-noindex-banner-inner
    echo '</div>'; // .gd-noindex-banner
}

/**
 * Detect all active noindex settings across WordPress core and SEO plugins.
 * Returns an array of warning strings with links to the relevant settings.
 */
function gd_detect_noindex_settings() {
    $warnings = array();

    // 1. WordPress core: Settings > Reading > "Discourage search engines"
    if ( '0' === (string) get_option( 'blog_public', '1' ) ) {
        $url = admin_url( 'options-reading.php' );
        $warnings[] = '<a href="' . esc_url( $url ) . '">Settings &gt; Reading</a> has &#8220;Discourage search engines from indexing this site&#8221; checked.';
    }

    // 2. Rank Math checks.
    $rankmath_general = get_option( 'rank-math-options-general', array() );
    if ( is_array( $rankmath_general ) ) {
        if ( ! empty( $rankmath_general['noindex_homepage'] ) && $rankmath_general['noindex_homepage'] === 'on' ) {
            $url = admin_url( 'admin.php?page=rank-math-options-titles' );
            $warnings[] = '<a href="' . esc_url( $url ) . '">Rank Math</a> has the homepage set to noindex.';
        }
    }

    $rankmath_titles = get_option( 'rank-math-options-titles', array() );
    if ( is_array( $rankmath_titles ) ) {
        foreach ( array( 'post', 'page' ) as $pt ) {
            $key = 'pt_' . $pt . '_robots';
            if ( ! empty( $rankmath_titles[ $key ] ) && is_array( $rankmath_titles[ $key ] ) ) {
                if ( in_array( 'noindex', $rankmath_titles[ $key ], true ) ) {
                    $url = admin_url( 'admin.php?page=rank-math-options-titles' );
                    $warnings[] = '<a href="' . esc_url( $url ) . '">Rank Math</a> has ' . ucfirst( $pt ) . 's globally set to noindex.';
                }
            }
        }
    }

    // 3. Yoast checks.
    $yoast_titles = get_option( 'wpseo_titles', array() );
    if ( is_array( $yoast_titles ) ) {
        foreach ( array( 'post', 'page' ) as $pt ) {
            $key = 'noindex-' . $pt;
            if ( ! empty( $yoast_titles[ $key ] ) ) {
                $url = admin_url( 'admin.php?page=wpseo_page_settings' );
                $warnings[] = '<a href="' . esc_url( $url ) . '">Yoast SEO</a> has ' . ucfirst( $pt ) . 's globally set to noindex.';
            }
        }
    }

    return $warnings;
}

function gd_noindex_warning_styles() {

    // Match the notice suppression — no styles needed on non-production.
    if ( gd_feature_enabled( 'GD_ENV_AWARENESS' ) ) {
        if ( 'production' !== gd_get_environment_type() ) {
            return;
        }
    }

    if ( empty( gd_detect_noindex_settings() ) ) {
        return;
    }

    ?>
    <style>
        .gd-noindex-banner {
            background: #d63638;
            color: #fff;
            margin: -1px 0 20px -20px;
            padding: 0;
            border: none;
            box-shadow: 0 2px 8px rgba(214, 54, 56, 0.3);
            font-size: 14px;
            line-height: 1.5;
            width: calc(100% + 20px);
        }
        .gd-noindex-banner-inner {
            display: flex;
            align-items: flex-start;
            padding: 14px 20px;
        }
        .gd-noindex-icon {
            font-size: 28px;
            margin-right: 14px;
            flex-shrink: 0;
            line-height: 1;
        }
        .gd-noindex-content {
            flex: 1;
        }
        .gd-noindex-content strong {
            font-size: 15px;
            letter-spacing: 0.5px;
            display: inline;
        }
        .gd-noindex-sep {
            margin: 0 2px;
        }
        .gd-noindex-list {
            margin: 6px 0 0 18px;
            padding: 0;
            list-style: disc;
        }
        .gd-noindex-list li {
            margin-bottom: 2px;
        }
        .gd-noindex-banner a {
            color: #fff;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 2px;
            font-weight: 600;
        }
        .gd-noindex-banner a:hover,
        .gd-noindex-banner a:focus {
            text-decoration-thickness: 2px;
        }
    </style>
    <?php
}

// =====================================================================
// 10. STATUS PAGE (Read-Only)
// =====================================================================
// Adds a page at Settings > Site Hardening showing which features
// are active, their configuration source, and relevant details.
// Read-only — no toggles, no form submissions, no database writes.
// Visible only to Administrators (manage_options).
// =====================================================================

if ( gd_feature_enabled( 'GD_STATUS_PAGE' ) ) {

    add_action( 'admin_menu', function () {
        add_options_page(
            'Site Hardening Status',
            'Site Hardening',
            'manage_options',
            'gd-site-hardening',
            'gd_render_status_page'
        );
    });
}

function gd_render_status_page() {

    $features = array(
        array(
            'label'    => 'Disable Comments',
            'constant' => 'GD_BLOCK_COMMENTS',
            'desc'     => 'Hides comment UI, closes comments on all post types. Discussion settings page preserved for form spam filtering.',
        ),
        array(
            'label'    => 'Restrict REST API',
            'constant' => 'GD_LOCK_REST_API',
            'desc'     => 'REST API limited to Administrators. Plugin namespaces whitelisted for public access.',
        ),
        array(
            'label'    => 'Restrict XML-RPC',
            'constant' => 'GD_LOCK_XMLRPC',
            'desc'     => 'XML-RPC requires authentication. Pingback methods disabled.',
        ),
        array(
            'label'    => 'Remove Emoji Scripts',
            'constant' => 'GD_STRIP_EMOJI',
            'desc'     => 'Removes WordPress emoji JS/CSS (~10KB per page).',
        ),
        array(
            'label'    => 'Dashboard Support Widget',
            'constant' => 'GD_SUPPORT_WIDGET',
            'desc'     => 'Replaces WP Events & News widget with agency contact info.',
        ),
        array(
            'label'    => 'Disable Author Archives',
            'constant' => 'GD_BLOCK_AUTHOR_PAGES',
            'desc'     => '301 redirects /author/ pages and /?author=N to homepage.',
        ),
        array(
            'label'    => 'Remove WP Version',
            'constant' => 'GD_HIDE_VERSION',
            'desc'     => 'Strips WordPress version from HTML source and RSS feeds.',
        ),
        array(
            'label'    => 'Disable Self-Pingbacks',
            'constant' => 'GD_BLOCK_SELF_PINGS',
            'desc'     => 'Prevents internal pingbacks when linking to own content.',
        ),
        array(
            'label'    => 'Disable Application Passwords',
            'constant' => 'GD_BLOCK_APP_PASSWORDS',
            'desc'     => 'Removes WP 5.6+ application passwords feature. Reduces API attack surface.',
        ),
        array(
            'label'    => 'Environment Awareness',
            'constant' => 'GD_ENV_AWARENESS',
            'desc'     => 'Colors admin bar by environment, suppresses noindex warning on staging/dev.',
        ),
        array(
            'label'    => 'File Edit Warning',
            'constant' => 'GD_WARN_FILE_EDIT',
            'desc'     => 'Warns on production if DISALLOW_FILE_EDIT is not set. Requires GD_ENV_AWARENESS to be on.',
        ),
        array(
            'label'    => 'Disable Admin Email Check',
            'constant' => 'GD_BLOCK_EMAIL_NAG',
            'desc'     => 'Removes the periodic admin email verification nag screen.',
        ),
        array(
            'label'    => 'Obscure Login Errors',
            'constant' => 'GD_HIDE_LOGIN_ERRORS',
            'desc'     => 'Replaces specific login error messages with a generic response. Prevents username enumeration via login.',
        ),
        array(
            'label'    => 'Custom Admin Footer',
            'constant' => 'GD_AGENCY_FOOTER',
            'desc'     => 'Replaces default WordPress admin footer with agency branding. Uses GD_SUPPORT_NAME and GD_SUPPORT_URL.',
        ),
        array(
            'label'    => 'Limit Post Revisions',
            'constant' => 'GD_CAP_REVISIONS',
            'desc'     => 'Caps post revisions at 10 (default). Override with GD_REVISION_LIMIT or WP_POST_REVISIONS.',
        ),
        array(
            'label'    => 'Throttle Heartbeat API',
            'constant' => 'GD_SLOW_HEARTBEAT',
            'desc'     => 'Slows Heartbeat to 60s on non-editor pages. Editor pages keep the 15s default for autosave.',
        ),
        array(
            'label'    => 'Disable oEmbed Discovery',
            'constant' => 'GD_BLOCK_OEMBED',
            'desc'     => 'Prevents other sites from embedding your content. Consuming oEmbeds (YouTube, etc.) still works.',
        ),
        array(
            'label'    => 'Noindex Warning Banner',
            'constant' => 'GD_NOINDEX_WARNING',
            'desc'     => 'Red banner on all admin pages when search indexing is blocked.',
        ),
        array(
            'label'    => 'Status Page',
            'constant' => 'GD_STATUS_PAGE',
            'desc'     => 'This page. Read-only overview of active hardening features.',
        ),
    );

    ?>
    <div class="wrap">
        <h1>Site Hardening Status</h1>
        <p>
            <strong>GD Site Hardening</strong> v<?php echo esc_html( GD_HARDENING_VERSION ); ?> &mdash; Garrett Digital
            <br>
            <span style="color: #666;">Read-only status page. Features are configured via <code>wp-config.php</code> constants. All features default to on.</span>
        </p>

        <table class="widefat fixed striped" style="max-width: 920px; margin-top: 16px;">
            <thead>
                <tr>
                    <th style="width: 26%;">Feature</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 14%;">Source</th>
                    <th style="width: 50%;">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $features as $f ) :
                    $is_active  = gd_feature_enabled( $f['constant'] );
                    $is_defined = defined( $f['constant'] );
                    $source     = $is_defined ? 'wp-config.php' : 'Default';
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html( $f['label'] ); ?></strong>
                        <br>
                        <code style="font-size: 11px; color: #888;"><?php echo esc_html( $f['constant'] ); ?></code>
                    </td>
                    <td>
                        <?php if ( $is_active ) : ?>
                            <span style="color: #00a32a; font-weight: 600;">&#10003; Active</span>
                        <?php else : ?>
                            <span style="color: #b32d2e; font-weight: 600;">&#10005; Off</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #666;">
                            <?php echo esc_html( $source ); ?>
                        </span>
                    </td>
                    <td style="color: #555; font-size: 13px;"><?php echo esc_html( $f['desc'] ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php // ---- REST API details ---- ?>
        <?php if ( gd_feature_enabled( 'GD_LOCK_REST_API' ) ) :
            $rest_mode = defined( 'GD_REST_MODE' ) ? GD_REST_MODE : 'auto';
        ?>
        <h2 style="margin-top: 30px;">REST API Configuration</h2>
        <table class="widefat fixed striped" style="max-width: 920px;">
            <tbody>
                <tr>
                    <th style="width: 26%;">Mode</th>
                    <td>
                        <code><?php echo esc_html( $rest_mode ); ?></code>
                        <?php if ( 'auto' === $rest_mode ) : ?>
                            <span style="color: #666; font-size: 12px;">(default &mdash; all plugin endpoints allowed, sensitive core endpoints blocked)</span>
                        <?php else : ?>
                            <span style="color: #666; font-size: 12px;">(all endpoints blocked unless namespace is whitelisted)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Required Capability</th>
                    <td>
                        <code><?php echo esc_html( defined( 'GD_REST_CAPABILITY' ) ? GD_REST_CAPABILITY : 'manage_options' ); ?></code>
                        <?php if ( ! defined( 'GD_REST_CAPABILITY' ) ) : ?>
                            <span style="color: #666; font-size: 12px;">(default &mdash; Administrators only)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ( 'auto' === $rest_mode ) : ?>
                <tr>
                    <th>Blocked Endpoints</th>
                    <td>
                        <?php
                        foreach ( gd_get_blocked_endpoints() as $ep ) {
                            echo '<code style="display: inline-block; margin: 2px 4px 2px 0; padding: 2px 8px; background: #fcf0f0; border-radius: 3px; font-size: 12px; color: #b32d2e;">' . esc_html( $ep ) . '</code>';
                        }
                        ?>
                    </td>
                </tr>
                <?php else : ?>
                <tr>
                    <th>Whitelisted Namespaces</th>
                    <td>
                        <?php
                        foreach ( gd_get_rest_whitelist() as $ns ) {
                            echo '<code style="display: inline-block; margin: 2px 4px 2px 0; padding: 2px 8px; background: #f0f6fc; border-radius: 3px; font-size: 12px;">' . esc_html( $ns ) . '</code>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Extra Namespaces</th>
                    <td>
                        <?php if ( defined( 'GD_REST_EXTRA_NAMESPACES' ) && GD_REST_EXTRA_NAMESPACES ) : ?>
                            <code><?php echo esc_html( GD_REST_EXTRA_NAMESPACES ); ?></code>
                            <span style="color: #666; font-size: 12px;">(via wp-config.php)</span>
                        <?php else : ?>
                            <span style="color: #999;">None configured</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>User Enumeration</th>
                    <td>
                        <span style="color: #00a32a; font-weight: 600;">&#10003; Blocked</span>
                        <span style="color: #666; font-size: 12px;">/wp/v2/users endpoint removed for non-admins</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>

        <?php // ---- Search engine indexing status ---- ?>
        <h2 style="margin-top: 30px;">Search Engine Indexing</h2>
        <?php
        $noindex_warnings = gd_detect_noindex_settings();
        if ( empty( $noindex_warnings ) ) : ?>
            <p style="color: #00a32a; font-weight: 600; font-size: 14px;">
                &#10003; No indexing blocks detected. Search engines are allowed to index this site.
            </p>
        <?php else : ?>
            <div style="background: #fcf0f0; border: 1px solid #d63638; border-left: 4px solid #d63638; padding: 12px 16px; max-width: 920px; margin-top: 8px;">
                <strong style="color: #d63638;">Indexing is blocked:</strong>
                <ul style="margin: 8px 0 0 20px; list-style: disc;">
                    <?php foreach ( $noindex_warnings as $warning ) : ?>
                        <li><?php echo wp_kses( $warning, array( 'a' => array( 'href' => array() ) ) ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php // ---- Dashboard widget details ---- ?>
        <?php if ( gd_feature_enabled( 'GD_SUPPORT_WIDGET' ) ) : ?>
        <h2 style="margin-top: 30px;">Dashboard Support Widget</h2>
        <table class="widefat fixed striped" style="max-width: 920px;">
            <tbody>
                <tr>
                    <th style="width: 26%;">Agency Name</th>
                    <td><?php echo esc_html( gd_get_support_name() ); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo esc_html( defined( 'GD_SUPPORT_EMAIL' ) ? GD_SUPPORT_EMAIL : 'support@garrettdigital.com' ); ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?php echo esc_html( defined( 'GD_SUPPORT_PHONE' ) ? GD_SUPPORT_PHONE : '512-730-1872' ); ?></td>
                </tr>
                <tr>
                    <th>Website</th>
                    <td><?php echo esc_html( defined( 'GD_SUPPORT_URL' ) ? GD_SUPPORT_URL : 'https://www.garrettdigital.com' ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>

        <?php // ---- Environment info ---- ?>
        <h2 style="margin-top: 30px;">Environment</h2>
        <table class="widefat fixed striped" style="max-width: 920px;">
            <tbody>
                <tr>
                    <th style="width: 26%;">Environment Type</th>
                    <td>
                        <?php
                        $env = gd_get_environment_type();
                        $env_colors = array( 'local' => '#00a32a', 'development' => '#d63638', 'staging' => '#dba617', 'production' => '#2271b1' );
                        $env_color = isset( $env_colors[ $env ] ) ? $env_colors[ $env ] : '#666';
                        ?>
                        <span style="display: inline-block; padding: 2px 10px; background: <?php echo esc_attr( $env_color ); ?>; color: #fff; border-radius: 3px; font-size: 12px; font-weight: 600; letter-spacing: 0.5px;">
                            <?php echo esc_html( strtoupper( $env ) ); ?>
                        </span>
                        <?php if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) : ?>
                            <span style="color: #666; font-size: 12px;">(not set &mdash; defaulting to production)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>WordPress Version</th>
                    <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                </tr>
                <tr>
                    <th>PHP Version</th>
                    <td><?php echo esc_html( phpversion() ); ?></td>
                </tr>
                <tr>
                    <th>Post Revisions Limit</th>
                    <td>
                        <?php if ( defined( 'WP_POST_REVISIONS' ) ) : ?>
                            <code><?php echo esc_html( WP_POST_REVISIONS ); ?></code>
                            <?php if ( true === WP_POST_REVISIONS ) : ?>
                                <span style="color: #dba617; font-size: 12px;">(unlimited)</span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span style="color: #999;">Not set (WordPress default: unlimited)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>File Editing</th>
                    <td>
                        <?php if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) : ?>
                            <span style="color: #00a32a; font-weight: 600;">&#10003; Disabled</span>
                        <?php else : ?>
                            <span style="color: #dba617; font-weight: 600;">&#9888; Enabled</span>
                            <span style="color: #666; font-size: 12px;">(add DISALLOW_FILE_EDIT to wp-config.php)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Plugin File</th>
                    <td><code style="font-size: 12px;"><?php echo esc_html( __FILE__ ); ?></code></td>
                </tr>
            </tbody>
        </table>

        <p style="margin-top: 30px; color: #999; font-size: 12px;">
            To change any setting, add or modify the corresponding <code>GD_*</code> constant in <code>wp-config.php</code>.
            See the setup guide for full documentation.
        </p>
    </div>
    <?php
}
