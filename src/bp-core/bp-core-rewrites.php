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
