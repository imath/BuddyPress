<?php
/**
 * Groups: Single group "Manage" screen handler
 *
 * @package BuddyPress
 * @subpackage GroupsScreens
 * @since 3.0.0
 */

/**
 * Handle the display of a group's Admin pages.
 *
 * @since 1.0.0
 * @since 6.0.0 Uses `bp_rewrites_get_link()` to build the link if Rewrites option is on.
 */
function groups_screen_group_admin() {
	if ( ! bp_is_groups_component() || ! bp_is_current_action( 'admin' ) ) {
		return false;
	}

	if ( bp_action_variables() ) {
		return false;
	}

	$group    = groups_get_current_group();
	$redirect = bp_get_group_permalink( $group ) . 'admin/edit-details/';

	if ( bp_use_wp_rewrites() && isset( $group->slug ) ) {
		$redirect = bp_rewrites_get_link( array(
			'component_id'                 => 'groups',
			'single_item'                  => $group->slug,
			'single_item_action'           => 'admin',
			'single_item_action_variables' => array( 'edit-details' ),
		) );
	}

	bp_core_redirect( $redirect );
}
