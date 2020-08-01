<?php
/**
 * BuddyPress Members Admin
 *
 * @package BuddyPress
 * @subpackage MembersAdmin
 * @since 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Load the BP Members admin.
add_action( 'bp_init', array( 'BP_Members_Admin', 'register_members_admin' ) );

/**
 * Create Users submenu to manage BuddyPress types.
 *
 * @since 7.0.0
 */
function bp_members_type_admin_menu() {
	if ( ! bp_is_root_blog() ) {
		return;
	}

	if ( bp_is_network_activated() && is_network_admin() ) {
		// Adds a users.php submenu to go to the root blog Member types screen.
		$member_type_admin_url = add_query_arg( 'taxonomy', bp_get_member_type_tax_name(), get_admin_url( bp_get_root_blog_id(), 'edit-tags.php' ) );

		add_submenu_page(
			'users.php',
			__( 'Member types', 'buddypress' ),
			__( 'Member types', 'buddypress' ),
			'bp_moderate',
			esc_url( $member_type_admin_url )
		);

	} elseif ( ! is_network_admin() ) {
		add_submenu_page(
			'users.php',
			__( 'Member types', 'buddypress' ),
			__( 'Member types', 'buddypress' ),
			'bp_moderate',
			basename( add_query_arg( 'taxonomy', bp_get_member_type_tax_name(), bp_get_admin_url( 'edit-tags.php' ) ) )
		);
	}
}
add_action( 'bp_admin_menu', 'bp_members_type_admin_menu' );
