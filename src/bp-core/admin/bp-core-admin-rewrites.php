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
	$bp = buddypress();
?>

	<div class="wrap">

		<h1><?php esc_html_e( 'BuddyPress URLs', 'buddypress' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'URLs', 'buddypress' ) ); ?></h2>
		<form action="" method="post" id="bp-admin-rewrites-form">

			<?php foreach ( $bp->pages as $component_id => $directory_data ) : ?>

				<h3><?php echo esc_html( $directory_data->title ); ?></h3>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="<?php printf( esc_attr__( '%s-directory-slug', 'buddypress' ), $component_id ); ?>">
								<?php printf( esc_html__( 'Directory slug', 'buddypress' ), $directory_data->title ); ?>
							</label>
						</th>
						<td>
							<input type="text" class="code" name="<?php printf( esc_attr__( '%s-directory-slug', 'buddypress' ), $component_id ); ?>" id="<?php printf( esc_attr__( '%s-directory-slug', 'buddypress' ), $component_id ); ?>" value="<?php echo esc_attr( $directory_data->slug ); ?>">
						</td>
					</tr>
				</table>

				<?php if ( 'members' === $component_id ) : ?>
					<h4><?php esc_html_e( 'Single item slugs', 'buddypress' ); ?></h4>
					<table class="form-table" role="presentation">
						<?php foreach ( $bp->members->nav->get_primary() as $primary_item ) : ?>
							<?php foreach ( $bp->members->nav->get_secondary( array( 'parent_slug' => $primary_item->slug ) ) as $secondary_item ) : ?>
								<tr>
									<?php if ( $secondary_item['slug'] === $primary_item['default_subnav_slug'] ) : ?>
										<th scope="row">
											<label style="margin-left: 2em; display: inline-block; vertical-align: middle" for="<?php printf( esc_attr__( '%1$s-%2$s-slug', 'buddypress' ), $component_id, $secondary_item['component_id'] ); ?>">
												<?php printf( esc_html__( '%s main nav slug', 'buddypress' ), $primary_item['name'] ); ?>
											</label>
										</th>
										<td>
											<input type="text" class="code" name="<?php printf( esc_attr__( '%1$s-%2$s-slug', 'buddypress' ), $component_id, $secondary_item['component_id'] ); ?>" id="<?php printf( esc_attr__( '%1$s-%2$s-slug', 'buddypress' ), $component_id, $secondary_item['component_id'] ); ?>" value="<?php echo esc_attr( $primary_item['slug'] ); ?>">
										</td>

									<?php else : ?>
										<th scope="row">
											<label style="margin-left: 2em;  display: inline-block; vertical-align: middle" for="<?php echo esc_attr( $secondary_item['screen_function'] ); ?>">
												<?php printf( esc_html__( '"%s" subnav slug', 'buddypress' ), $secondary_item['name'] ); ?>
											</label>
										</th>
										<td>
											<input type="text" class="code" name="<?php echo esc_attr( $secondary_item['screen_function'] ); ?>" id="<?php echo esc_attr( $secondary_item['screen_function'] ); ?>" value="<?php echo esc_attr( $secondary_item['slug'] ); ?>">
										</td>

									<?php endif ; ?>
								</tr>
							<?php endforeach ; ?>
						<?php endforeach ; ?>
					</table>
				<?php endif ; ?>

			<?php endforeach ; ?>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="bp-admin-rewrites-submit" id="bp-admin-rewrites-submit" value="<?php esc_attr_e( 'Save Settings', 'buddypress' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'bp-admin-rewrites-setup' ); ?>

		</form>
	</div>

<?php
}
