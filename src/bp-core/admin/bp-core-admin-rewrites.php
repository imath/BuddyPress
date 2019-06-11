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

				<h2><?php echo esc_html( $directory_data->title ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="<?php printf( esc_attr__( '%s-directory-slug', 'buddypress' ), $component_id ); ?>">
								<?php printf( esc_html__( 'Directory slug', 'buddypress' ), $directory_data->title ); ?>
							</label>
						</th>
						<td>
							<input type="text" class="code" name="<?php printf( 'components[%d][post_name]', absint( $directory_data->id ) ); ?>" id="<?php printf( esc_attr__( '%s-directory-slug', 'buddypress' ), $component_id ); ?>" value="<?php echo esc_attr( $directory_data->slug ); ?>">
						</td>
					</tr>
				</table>

				<?php if ( 'members' === $component_id ) : ?>
					<h3><?php esc_html_e( 'Single Member primary navigation slugs', 'buddypress' ); ?></h3>
					<table class="form-table" role="presentation">
						<?php foreach ( $bp->members->nav->get_primary() as $primary_nav_item ) :
							if ( ! isset( $primary_nav_item['rewrite_id'] ) || ! $primary_nav_item['rewrite_id'] ) {
								continue;
							}
						?>
							<tr>
								<th scope="row">
									<label style="margin-left: 2em; display: inline-block; vertical-align: middle" for="<?php printf( esc_attr__( '%s-slug', 'buddypress' ), $primary_nav_item['rewrite_id'] ); ?>">
										<?php printf( esc_html__( '"%s" slug', 'buddypress' ), $primary_nav_item['name'] ); ?>
									</label>
								</th>
								<td>
									<input type="text" class="code" name="<?php printf( 'components[%1$d][_bp_component_slugs][%2$s]', absint( $directory_data->id ), esc_attr( $primary_nav_item['rewrite_id'] ) ); ?>" id="<?php printf( esc_attr__( '%s-slug', 'buddypress' ), $primary_nav_item['rewrite_id'] ); ?>" value="<?php echo esc_attr( bp_rewrites_get_slug( $component_id, $primary_nav_item['rewrite_id'], $primary_nav_item['slug'] ) ); ?>">
								</td>
							</tr>
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

/**
 * Switch directory pages between the `page` & the `bp_directories`
 * post types and update WP Nav items.
 *
 * This is what allowes a user to test our new parser, making sure he can
 * come back to the legacy one in case a plugin/theme is not ready yet.
 *
 * @since 6.0.0
 *
 * @param bool $use_rewrite Whether to use the Legacy parser or the WP Rewrites.
 */
function bp_core_admin_rewrites_update_directory_pages( $use_rewrite = false ) {
	$bp_pages          = bp_core_get_directory_pages();
	$nav_menu_item_ids = array();

	$post_type   = 'page';
	$item_object = 'bp_directories';
	if ( $use_rewrite ) {
		$post_type = 'bp_directories';
		$item_object = 'page';
	}

	foreach ( $bp_pages as $bp_page ) {
		$nav_menu_item_ids[] = $bp_page->id;

		// Switch the post type.
		wp_update_post( array( 'ID' => $bp_page->id, 'post_type' => $post_type ) );
	}

	// Update nav menu items!
	$nav_menus = wp_get_nav_menus( array( 'hide_empty' => true ) );
	foreach ( $nav_menus as $nav_menu ) {
		$items = wp_get_nav_menu_items( $nav_menu->term_id );

		foreach( $items as $item ) {
			if ( $item_object !== $item->object || ! in_array( $item->object_id, $nav_menu_item_ids, true ) ) {
				continue;
			}

			wp_update_nav_menu_item( $nav_menu->term_id, $item->ID, array(
				'menu-item-db-id'       => $item->db_id,
				'menu-item-object-id'   => $item->object_id,
				'menu-item-object'      => $post_type,
				'menu-item-parent-id'   => $item->menu_item_parent,
				'menu-item-position'    => $item->menu_order,
				'menu-item-type'        => 'post_type',
				'menu-item-title'       => $item->title,
				'menu-item-url'         => $item->url,
				'menu-item-description' => $item->description,
				'menu-item-attr-title'  => $item->attr_title,
				'menu-item-target'      => $item->target,
				'menu-item-classes'     => implode( ' ', (array) $item->classes ),
				'menu-item-xfn'         => $item->xfn,
				'menu-item-status'      => 'publish',
			) );
		}
	}
}

/**
 * Handle saving of the BuddyPress customizable slugs.
 *
 * @since 6.0.0
 */
function bp_core_admin_rewrites_setup_handler() {

	if ( ! isset( $_POST['bp-admin-rewrites-submit'] ) ) {
		return;
	}

	check_admin_referer( 'bp-admin-rewrites-setup' );

	$base_url = bp_get_admin_url( add_query_arg( 'page', 'bp-rewrites-settings', 'admin.php' ) );

	if ( ! isset( $_POST['components'] ) ) {
		wp_safe_redirect( add_query_arg( 'error', 'true', $base_url ) );
	}

	$current_page_slugs   = wp_list_pluck( bp_core_get_directory_pages(), 'slug', 'id' );
	$directory_slug_edits = array();
	foreach ( $_POST['components'] as $page_id => $slugs ) {
		$postarr = array();

		if ( ! isset( $current_page_slugs[ $page_id ] ) )  {
			continue;
		}

		$postarr['ID'] = $page_id;

		if ( $current_page_slugs[ $page_id ] !== $slugs['post_name'] ) {
			$directory_slug_edits[] = $page_id;
			$postarr['post_name'] = $slugs['post_name'];
		}

		if ( isset( $slugs['_bp_component_slugs'] ) && is_array( $slugs['_bp_component_slugs'] ) ) {
			$postarr['meta_input']['_bp_component_slugs'] = array_map( 'sanitize_title', $slugs['_bp_component_slugs'] );
		}

		wp_update_post( $postarr );
	}

	// Make sure the WP rewrites will be regenarated at next page load.
	if ( $directory_slug_edits ) {
		bp_delete_rewrite_rules();
	}

	wp_safe_redirect( add_query_arg( 'updated', 'true', $base_url ) );
}
add_action( 'bp_admin_init', 'bp_core_admin_rewrites_setup_handler' );
