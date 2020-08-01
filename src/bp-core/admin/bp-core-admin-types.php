<?php
/**
 * BuddyPress Types Administration screen.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 7.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function bp_core_admin_head_types_screen() {
	global $parent_file, $taxnow;
	$bp_taxonomies = wp_list_pluck( bp_get_default_taxonomies(), 'component' );

	if ( ! isset( $bp_taxonomies[ $taxnow ] ) ) {
		return;
	}

	if ( 'members' === $bp_taxonomies[ $taxnow ] ) {
		$parent_file = 'users.php';
	} else {
		$parent_file = 'bp-' . $bp_taxonomies['component'];
	}
}

function bp_core_admin_load_types_screen() {
	$taxonomy       = '';
	$current_screen = get_current_screen();

	if ( ! isset( $current_screen->taxonomy ) || ! $current_screen->taxonomy ) {
		return;
	}

	$taxonomy      = $current_screen->taxonomy;
	$screen_id     = $current_screen->id;
	$bp_taxonomies = array_keys( bp_get_default_taxonomies() );

	if ( ! in_array( $taxonomy, $bp_taxonomies, true ) ) {
		return;
	}

	add_action( 'admin_head-edit-tags.php', 'bp_core_admin_head_types_screen' );
	add_action( 'admin_head-term.php', 'bp_core_admin_head_types_screen' );
}
add_action( 'load-edit-tags.php', 'bp_core_admin_load_types_screen' );
