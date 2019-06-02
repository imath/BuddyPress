<?php
/**
 * BuddyPress Rewrites.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 6.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Delete rewrite rules, so that they are automatically rebuilt on
 * the subsequent page load.
 *
 * @since 6.0.0
 */
function bp_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}

/**
 * Let's experiment WordPress URL rewriting in BuddyPress!
 *
 * If the Custom URLs option is active, this will neutralize our
 * legacy parser in favor of the WP URL Rewrites API.
 *
 * @since 6.0.0
 */
function bp_disable_legacy_url_parser() {
	if ( ! bp_use_wp_rewrites() ) {
		return;
	}

	// First let's neutalize our legacy URL parser.
	remove_action( 'bp_init', 'bp_core_set_uri_globals', 2 );

	// This hook needs to happen later.
	remove_action( 'bp_init', 'bp_setup_canonical_stack', 5 );
	add_action( 'bp_parse_query', 'bp_setup_canonical_stack', 11 );

	/**
	 * This hook needs to happen later on front-end only.
	 *
	 * @see `bp_nav_menu_get_loggedin_pages()`
	 */
	if ( ! is_admin() ) {
		remove_action( 'bp_init', 'bp_setup_nav', 6 );
		add_action( 'bp_parse_query', 'bp_setup_nav', 12 );
	}

	// Then register a custom post type to use for the directory pages.
	register_post_type( 'bp_directories', array(
		'label'               => _x( 'BuddyPress directories', 'Post type label used in the Admin menu.', 'buddypress' ),
		'labels'              => array(
			'singular_name' => _x( 'BuddyPress directory', 'Post type singular name', 'buddypress' ),
		),
		'description'         => __( 'The BuddyPress directories post type is used when the custom URLs option is active.', 'buddypress' ),
		'public'              => false,
		'hierarchical'        => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'show_in_nav_menus'   => true,
		'show_in_rest'        => false,
		'supports'            => array( 'title' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'delete_with_user'    => false,
	) );
}
add_action( 'bp_init', 'bp_disable_legacy_url_parser', 1 );

/**
 * Maybe temporary ?
 *
 * Used to make sure WP Nav Menus still use the same links.
 */
function bp_directory_link( $link, WP_Post $post ) {
	if ( 'bp_directories' !== get_post_type( $post ) ) {
		return $link;
	}

	$directory_pages = wp_list_pluck( bp_core_get_directory_pages(), 'slug', 'id' );
	if ( ! isset( $directory_pages[ $post->ID ] ) ) {
		return $link;
	}

	return home_url( user_trailingslashit( $directory_pages[ $post->ID ] ) );
}
add_filter( 'post_type_link', 'bp_directory_link', 1, 2 );

/**
 * Get needed data to find a member single item from the request.
 *
 * @since 6.0.0
 *
 * @param string $request The request used during parsing.
 * @return array Data to find a member single item from the request.
 */
function bp_rewrites_get_member_data( $request = '' ) {
	$member_data = array( 'field' => 'slug' );

	if ( bp_is_username_compatibility_mode() ) {
		$member_data = array( 'field' => 'login' );
	}

	if ( bp_core_enable_root_profiles() ) {
		if ( ! $request ) {
			$request = $GLOBALS['wp']->request;
		}

		$request_chunks = explode( '/', ltrim( $request, '/' ) );
		$member_chunk   = reset( $request_chunks );

		// Try to get an existing member to eventually reset the WP Query.
		$member_data['object'] = get_user_by( $member_data['field'], $member_chunk );
	}

	return $member_data;
}

/**
 * Makes sure BuddyPress globals are set during Ajax requests.
 *
 * @since 6.0.0
 */
function bp_parse_ajax_referer_query() {
	if ( ! wp_doing_ajax() || ! bp_use_wp_rewrites() ) {
		return;
	}

	$bp       = buddypress();
	$bp->ajax = (object) array(
		'WP' => new WP(),
	);

	bp_reset_query( bp_get_referer_path(), $GLOBALS['wp_query'] );
}
add_action( 'bp_admin_init', 'bp_parse_ajax_referer_query' );

/**
 * Resets the query to fit our permalink structure if needed.
 *
 * This is used for specific cases such as Root Member's profile or Ajax.
 *
 * @since 6.0.0
 *
 * @param string $bp_request A specific BuddyPress request.
 * @param WP_Query $query The WordPress query object.
 */
function bp_reset_query( $bp_request = '', WP_Query $query ) {
	global $wp;
	$bp = buddypress();

	// Back up request uri.
	$reset_server_request_uri = $_SERVER['REQUEST_URI'];

	// Temporarly override it.
	if ( isset( $wp->request ) ) {
		$_SERVER['REQUEST_URI'] = str_replace( $wp->request, $bp_request, $reset_server_request_uri );

		// Reparse request.
		$wp->parse_request();

		// Reparse query.
		bp_remove_all_filters( 'parse_query' );
		$query->parse_query( $wp->query_vars );
		bp_restore_all_filters( 'parse_query' );

	} elseif ( isset( $bp->ajax ) ) {
		// Extra step for root profiles
		$member = bp_rewrites_get_member_data( $bp_request );
		if ( isset( $member['object'] ) && $member['object'] ) {
			$bp_request = '/' . $bp->members->root_slug . $bp_request;
		}

		$_SERVER['REQUEST_URI'] = $bp_request;

		$bp->ajax->WP->parse_request();
		$query->parse_query( $bp->ajax->WP->matched_query );
	}

	// Restore request uri.
	$_SERVER['REQUEST_URI'] = $reset_server_request_uri;

	// The query is reset.
	return true;
}

/**
 * Returns the slug to use for the nav item of the requested component.
 *
 * @since 6.0.0
 *
 * @param string $component_id The BuddyPress component's ID.
 * @param string $rewrite_id   The nav item's rewrite ID.
 * @param string $default_slug The nav item's default slug.
 * @return string              The slug to use for the nav item of the requested component.
 */
function bp_rewrites_get_slug( $component_id = '', $rewrite_id = '', $default_slug = '' ) {
	$directory_pages = bp_core_get_directory_pages();
	$slug            = $default_slug;

	if ( ! isset( $directory_pages->{$component_id}->custom_slugs ) || ! $rewrite_id ) {
		return $slug;
	}

	$custom_slugs = (array) $directory_pages->{$component_id}->custom_slugs;
	if ( isset( $custom_slugs[ $rewrite_id ] ) && $custom_slugs[ $rewrite_id ] ) {
		$slug = $custom_slugs[ $rewrite_id ];
	}

	return $slug;
}
