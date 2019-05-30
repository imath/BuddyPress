<?php
/**
 * BuddyPress Admin Rewrites Functions.
 *
 * @package BuddyPress
 * @subpackage CoreAdministration
 * @since 6.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders BuddyPress URLs admin panel.
 *
 * @since 6.0.O
 */
function bp_core_admin_rewrites_settings() {
?>

	<div class="wrap">

		<h1><?php _e( 'BuddyPress URLs', 'buddypress' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'URLs', 'buddypress' ) ); ?></h2>
		<form action="" method="post" id="bp-admin-rewrites-form">

			<h3><?php esc_html_e( 'Customise URL slugs.', 'buddypress' ); ?></h3>
			<p><strong>@todo</strong></p>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="bp-admin-rewrites-submit" id="bp-admin-rewrites-submit" value="<?php esc_attr_e( 'Save Settings', 'buddypress' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'bp-admin-rewrites-setup' ); ?>

		</form>
	</div>

<?php
}
