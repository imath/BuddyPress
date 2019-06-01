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
 * Resets the query to fit our permalink structure if needed.
 *
 * This is used for specific cases such as Root Member's profile.
 *
 * @since 6.0.0
 *
 * @param string $component_id The BuddyPress component's ID (eg: members).
 * @param WP_Query $query The WordPress query object.
 */
function bp_reset_query( $component_id = '', WP_Query $query ) {
	global $wp;
	$bp = buddypress();

	if ( ! bp_is_active( $component_id ) || ! isset( $bp->{$component_id}->root_slug ) ) {
		return false;
	}

	// Back up request uri.
	$reset_server_request_uri = $_SERVER['REQUEST_URI'];

	// Temporarly override it.
	$_SERVER['REQUEST_URI'] = str_replace( $wp->request, $bp->{$component_id}->root_slug . '/' . $wp->request, $reset_server_request_uri );

	// Reparse request.
	$wp->parse_request();

	// Reparse query.
	bp_remove_all_filters( 'parse_query' );
	$query->parse_query( $wp->query_vars );
	bp_restore_all_filters( 'parse_query' );

	// Restore request uri.
	$_SERVER['REQUEST_URI'] = $reset_server_request_uri;

	// The query is reset.
	return true;
}
