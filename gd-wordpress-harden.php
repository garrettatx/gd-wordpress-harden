<?php
/**
 * Plugin Name: GD Site Hardening
 * Description: Garrett Digital agency-standard WordPress hardening. Disables comments, restricts REST API and XML-RPC, removes emoji scripts, adds dashboard support widget, disables author archives, removes version info, disables application passwords, and warns when search engine indexing is blocked. Each feature is independently toggleable via wp-config.php constants.
 * Version: 1.2.0
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
 * Every feature is ON by default. To disable a specific feature,
 * add the corresponding constant to wp-config.php:
 *
 *   define( 'GD_DISABLE_COMMENTS',        false ); // Keep comments enabled
 *   define( 'GD_RESTRICT_REST_API',       false ); // Leave REST API open
 *   define( 'GD_RESTRICT_XMLRPC',         false ); // Leave XML-RPC open
 *   define( 'GD_REMOVE_EMOJI',            false ); // Keep emoji scripts
 *   define( 'GD_DASHBOARD_WIDGET',        false ); // Skip support widget
 *   define( 'GD_DISABLE_AUTHOR_ARCHIVES', false ); // Keep author archives
 *   define( 'GD_REMOVE_WP_VERSION',       false ); // Keep WP version in head
 *   define( 'GD_DISABLE_SELF_PINGBACKS',  false ); // Keep self-pingbacks
 *   define( 'GD_NOINDEX_WARNING',         false ); // Hide noindex warning banner
 *   define( 'GD_STATUS_PAGE',             false ); // Hide the status page
 *   define( 'GD_DISABLE_APP_PASSWORDS',   false ); // Keep application passwords enabled
 *
 * REST API NAMESPACE WHITELIST
 * ============================
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
 * 1.2.0 - Added Kadence (kb, kadence) to REST API whitelist. Moved comment DB
 *         cleanup to admin_init (off front-end requests). Removed REST API link
 *         and shortlink from wp_head. Added application passwords disable.
 * 1.1.0 - Added noindex warning banner, read-only status page
 * 1.0.0 - Initial release
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GD_HARDENING_VERSION', '1.2.0' );

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

if ( gd_feature_enabled( 'GD_DISABLE_COMMENTS' ) ) {

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
// Limits REST API access to administrators (manage_options) by default.
// Plugin namespaces that need public access are whitelisted.
// =====================================================================

if ( gd_feature_enabled( 'GD_RESTRICT_REST_API' ) ) {

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

        // Check if the request matches a whitelisted namespace.
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

        // Build the whitelist.
        $whitelisted = gd_get_rest_whitelist();

        // Check if the request URI contains a whitelisted namespace.
        foreach ( $whitelisted as $namespace ) {
            if ( strpos( $request_uri, '/wp-json/' . $namespace ) !== false
                || strpos( $request_uri, '?rest_route=/' . $namespace ) !== false ) {
                return $result;
            }
        }

        // Block everything else for unauthenticated/non-admin users.
        return new WP_Error(
            'rest_forbidden',
            'REST API access restricted.',
            array( 'status' => 403 )
        );
    }, 20 );

    // Block user enumeration via REST API specifically.
    add_filter( 'rest_endpoints', function ( $endpoints ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            unset( $endpoints['/wp/v2/users'] );
            unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        }
        return $endpoints;
    });
}

/**
 * Get the REST API namespace whitelist.
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

if ( gd_feature_enabled( 'GD_RESTRICT_XMLRPC' ) ) {

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

if ( gd_feature_enabled( 'GD_REMOVE_EMOJI' ) ) {

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

if ( gd_feature_enabled( 'GD_DASHBOARD_WIDGET' ) ) {

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

if ( gd_feature_enabled( 'GD_DISABLE_AUTHOR_ARCHIVES' ) ) {

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

if ( gd_feature_enabled( 'GD_REMOVE_WP_VERSION' ) ) {

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

if ( gd_feature_enabled( 'GD_DISABLE_SELF_PINGBACKS' ) ) {

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

if ( gd_feature_enabled( 'GD_DISABLE_APP_PASSWORDS' ) ) {

    add_filter( 'wp_is_application_passwords_available', '__return_false' );
}

// =====================================================================
// 9. NOINDEX WARNING BANNER
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
            'constant' => 'GD_DISABLE_COMMENTS',
            'desc'     => 'Hides comment UI, closes comments on all post types. Discussion settings page preserved for form spam filtering.',
        ),
        array(
            'label'    => 'Restrict REST API',
            'constant' => 'GD_RESTRICT_REST_API',
            'desc'     => 'REST API limited to Administrators. Plugin namespaces whitelisted for public access.',
        ),
        array(
            'label'    => 'Restrict XML-RPC',
            'constant' => 'GD_RESTRICT_XMLRPC',
            'desc'     => 'XML-RPC requires authentication. Pingback methods disabled.',
        ),
        array(
            'label'    => 'Remove Emoji Scripts',
            'constant' => 'GD_REMOVE_EMOJI',
            'desc'     => 'Removes WordPress emoji JS/CSS (~10KB per page).',
        ),
        array(
            'label'    => 'Dashboard Support Widget',
            'constant' => 'GD_DASHBOARD_WIDGET',
            'desc'     => 'Replaces WP Events & News widget with agency contact info.',
        ),
        array(
            'label'    => 'Disable Author Archives',
            'constant' => 'GD_DISABLE_AUTHOR_ARCHIVES',
            'desc'     => '301 redirects /author/ pages and /?author=N to homepage.',
        ),
        array(
            'label'    => 'Remove WP Version',
            'constant' => 'GD_REMOVE_WP_VERSION',
            'desc'     => 'Strips WordPress version from HTML source and RSS feeds.',
        ),
        array(
            'label'    => 'Disable Self-Pingbacks',
            'constant' => 'GD_DISABLE_SELF_PINGBACKS',
            'desc'     => 'Prevents internal pingbacks when linking to own content.',
        ),
        array(
            'label'    => 'Disable Application Passwords',
            'constant' => 'GD_DISABLE_APP_PASSWORDS',
            'desc'     => 'Removes WP 5.6+ application passwords feature. Reduces API attack surface.',
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
        <?php if ( gd_feature_enabled( 'GD_RESTRICT_REST_API' ) ) : ?>
        <h2 style="margin-top: 30px;">REST API Configuration</h2>
        <table class="widefat fixed striped" style="max-width: 920px;">
            <tbody>
                <tr>
                    <th style="width: 26%;">Required Capability</th>
                    <td>
                        <code><?php echo esc_html( defined( 'GD_REST_CAPABILITY' ) ? GD_REST_CAPABILITY : 'manage_options' ); ?></code>
                        <?php if ( ! defined( 'GD_REST_CAPABILITY' ) ) : ?>
                            <span style="color: #666; font-size: 12px;">(default &mdash; Administrators only)</span>
                        <?php endif; ?>
                    </td>
                </tr>
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
        <?php if ( gd_feature_enabled( 'GD_DASHBOARD_WIDGET' ) ) : ?>
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
                    <th style="width: 26%;">WordPress Version</th>
                    <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                </tr>
                <tr>
                    <th>PHP Version</th>
                    <td><?php echo esc_html( phpversion() ); ?></td>
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
