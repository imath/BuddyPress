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
function bp_unhook_legacy_url_parser() {
	if ( bp_use_wp_rewrites() ) {
		remove_action( 'bp_init', 'bp_core_set_uri_globals', 2 );
	}
}
add_action( 'bp_init', 'bp_unhook_legacy_url_parser', 1 );

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

	if ( ! bp_is_active( $component_id ) || ! isset( $bp->{$component_id}->directory_slug ) ) {
		return false;
	}

	// Back up request uri.
	$reset_server_request_uri = $_SERVER['REQUEST_URI'];

	// Temporarly override it.
	$_SERVER['REQUEST_URI'] = str_replace( $wp->request, $bp->{$component_id}->directory_slug . '/' . $wp->request, $reset_server_request_uri );

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
