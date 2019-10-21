<?php
/**
 * BuddyPress Admin Component Functions.
 *
 * @package BuddyPress
 * @subpackage CoreAdministration
 * @since 2.3.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders the Component Setup admin panel.
 *
 * @since 1.6.0
 *
 */
function bp_core_admin_components_settings() {
	$is_installable_components = isset( $_GET['action'] ) && 'installable' === $_GET['action'];

	$form_action = '';
	if ( $is_installable_components ) {
		$form_action = add_query_arg(
			array(
				'action' => 'install_bp_components',
			),
			network_admin_url( 'update.php' )
		);
	}
?>

	<div class="wrap">

		<h1><?php _e( 'BuddyPress Settings', 'buddypress' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'Components', 'buddypress' ) ); ?></h2>
		<form action="<?php echo esc_url( $form_action ); ?>" method="post" id="bp-admin-component-form">

			<?php bp_core_admin_components_options(); ?>

			<?php if ( $is_installable_components ) : ?>
				<p class="submit clear">
					<input class="button-primary" type="submit" name="bp-admin-component-install" id="bp-admin-component-install" value="<?php esc_attr_e( 'Install', 'buddypress' ) ?>"/>
				</p>
			<?php else : ?>
				<p class="submit clear">
					<input class="button-primary" type="submit" name="bp-admin-component-submit" id="bp-admin-component-submit" value="<?php esc_attr_e( 'Save Settings', 'buddypress' ) ?>"/>
				</p>
			<?php endif ; ?>

			<?php wp_nonce_field( 'bp-admin-component-setup' ); ?>

		</form>
	</div>

<?php
}

/**
 * Creates reusable markup for component setup on the Components and Pages dashboard panel.
 *
 * @since 1.6.0
 *
 * @todo Use settings API
 */
function bp_core_admin_components_options() {

	// Declare local variables.
	$deactivated_components = array();

	/**
	 * Filters the array of available components.
	 *
	 * @since 1.5.0
	 *
	 * @param mixed $value Active components.
	 */
	$active_components = apply_filters( 'bp_active_components', bp_get_option( 'bp-active-components' ) );

	// The default components (if none are previously selected).
	$default_components = array(
		'xprofile' => array(
			'title'       => __( 'Extended Profiles', 'buddypress' ),
			'description' => __( 'Customize your community with fully editable profile fields that allow your users to describe themselves.', 'buddypress' )
		),
		'settings' => array(
			'title'       => __( 'Account Settings', 'buddypress' ),
			'description' => __( 'Allow your users to modify their account and notification settings directly from within their profiles.', 'buddypress' )
		),
		'notifications' => array(
			'title'       => __( 'Notifications', 'buddypress' ),
			'description' => __( 'Notify members of relevant activity with a toolbar bubble and/or via email, and allow them to customize their notification settings.', 'buddypress' )
		),
	);

	$optional_components    = bp_core_admin_get_components( 'optional'    );
	$required_components    = bp_core_admin_get_components( 'required'    );
	$installable_components = bp_core_admin_get_components( 'installable' );

	// Merge optional and required together.
	$all_components = $optional_components + $required_components;

	// If this is an upgrade from before BuddyPress 1.5, we'll have to convert
	// deactivated components into activated ones.
	if ( empty( $active_components ) ) {
		$deactivated_components = bp_get_option( 'bp-deactivated-components' );
		if ( !empty( $deactivated_components ) ) {

			// Trim off namespace and filename.
			$trimmed = array();
			foreach ( array_keys( (array) $deactivated_components ) as $component ) {
				$trimmed[] = str_replace( '.php', '', str_replace( 'bp-', '', $component ) );
			}

			// Loop through the optional components to create an active component array.
			foreach ( array_keys( (array) $optional_components ) as $ocomponent ) {
				if ( !in_array( $ocomponent, $trimmed ) ) {
					$active_components[$ocomponent] = 1;
				}
			}
		}
	}

	// On new install, set active components to default.
	if ( empty( $active_components ) ) {
		$active_components = $default_components;
	}

	// Core component is always active.
	$active_components['core'] = $all_components['core'];
	$inactive_components       = array_diff( array_keys( $all_components ) , array_keys( $active_components ) );

	/** Display **************************************************************
	 */

	// Get the total count of all plugins.
	$all_count = count( $all_components );
	$page      = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';
	$action    = !empty( $_GET['action'] ) ? $_GET['action'] : 'all';

	switch( $action ) {
		case 'all' :
			$current_components = $all_components;
			break;
		case 'active' :
			foreach ( array_keys( $active_components ) as $component ) {
				$current_components[$component] = $all_components[$component];
			}
			break;
		case 'inactive' :
			foreach ( $inactive_components as $component ) {
				$current_components[$component] = $all_components[$component];
			}
			break;
		case 'mustuse' :
			$current_components = $required_components;
			break;
		case 'installable' :
			$current_components = $installable_components;
			break;
	} ?>

	<h3 class="screen-reader-text"><?php
		/* translators: accessibility text */
		_e( 'Filter components list', 'buddypress' );
	?></h3>

	<ul class="subsubsub">
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'bp-components', 'action' => 'all'         ), bp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'all'         ) : ?>class="current"<?php endif; ?>><?php printf( _nx( 'All <span class="count">(%s)</span>',         'All <span class="count">(%s)</span>',         $all_count,            'plugins', 'buddypress' ), number_format_i18n( $all_count                        ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'bp-components', 'action' => 'active'      ), bp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'active'      ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Active <span class="count">(%s)</span>',      'Active <span class="count">(%s)</span>',      count( $active_components      ), 'buddypress' ), number_format_i18n( count( $active_components       ) ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'bp-components', 'action' => 'inactive'    ), bp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'inactive'    ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Inactive <span class="count">(%s)</span>',    'Inactive <span class="count">(%s)</span>',    count( $inactive_components    ), 'buddypress' ), number_format_i18n( count( $inactive_components     ) ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'bp-components', 'action' => 'mustuse'     ), bp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'mustuse'     ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Must-Use <span class="count">(%s)</span>',    'Must-Use <span class="count">(%s)</span>',    count( $required_components    ), 'buddypress' ), number_format_i18n( count( $required_components     ) ) ); ?></a> | </li>
		<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'bp-components', 'action' => 'installable' ), bp_get_admin_url( $page ) ) ); ?>" <?php if ( $action === 'installable' ) : ?>class="current"<?php endif; ?>><?php printf( _n(  'Installable <span class="count">(%s)</span>', 'Installable <span class="count">(%s)</span>', count( $installable_components ), 'buddypress' ), number_format_i18n( count( $installable_components  ) ) ); ?></a></li>
	</ul>

	<h3 class="screen-reader-text"><?php
		/* translators: accessibility text */
		_e( 'Components list', 'buddypress' );
	?></h3>

	<table class="wp-list-table widefat plugins">
		<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox" <?php checked( empty( $inactive_components ) ); ?>>
					<label class="screen-reader-text" for="cb-select-all-1"><?php
					/* translators: accessibility text */
					_e( 'Enable or disable all optional components in bulk', 'buddypress' );
				?></label></td>
				<th scope="col" id="name" class="manage-column column-title column-primary"><?php _e( 'Component', 'buddypress' ); ?></th>
				<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', 'buddypress' ); ?></th>
			</tr>
		</thead>

		<tbody id="the-list">

			<?php if ( !empty( $current_components ) ) : ?>

				<?php foreach ( $current_components as $name => $labels ) : ?>

					<?php if ( !in_array( $name, array( 'core', 'members' ) ) ) :
						$class = isset( $active_components[esc_attr( $name )] ) ? 'active' : 'inactive';
					else :
						$class = 'active';
					endif; ?>

					<tr id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $name ) . ' ' . esc_attr( $class ); ?>">
						<th scope="row" class="check-column">

							<?php if ( !in_array( $name, array( 'core', 'members' ) ) ) : ?>

								<input type="checkbox" id="<?php echo esc_attr( "bp_components[$name]" ); ?>" name="<?php echo esc_attr( "bp_components[$name]" ); ?>" value="1"<?php checked( isset( $active_components[esc_attr( $name )] ) ); ?> /><label for="<?php echo esc_attr( "bp_components[$name]" ); ?>" class="screen-reader-text"><?php
									/* translators: accessibility text */
									printf( __( 'Select %s', 'buddypress' ), esc_html( $labels['title'] ) ); ?></label>

							<?php endif; ?>

						</th>
						<td class="plugin-title column-primary">
							<label for="<?php echo esc_attr( "bp_components[$name]" ); ?>">
								<?php if ( isset( $labels['icon'] ) && $labels['icon'] ) : ?>
									<span class="dashicons <?php echo sanitize_html_class( $labels['icon'] ); ?>" aria-hidden="true"></span>
								<?php else : ?>
									<span aria-hidden="true"></span>
								<?php endif ; ?>
								<strong><?php echo esc_html( $labels['title'] ); ?></strong>
							</label>
						</td>

						<td class="column-description desc">
							<div class="plugin-description">
								<p><?php echo $labels['description']; ?></p>
							</div>

						</td>
					</tr>

				<?php endforeach ?>

			<?php else : ?>

				<tr class="no-items">
					<td class="colspanchange" colspan="3"><?php _e( 'No components found.', 'buddypress' ); ?></td>
				</tr>

			<?php endif; ?>

		</tbody>

		<tfoot>
			<tr>
				<td class="manage-column column-cb check-column"><input id="cb-select-all-2" type="checkbox" <?php checked( empty( $inactive_components ) ); ?>>
					<label class="screen-reader-text" for="cb-select-all-2"><?php
					/* translators: accessibility text */
					_e( 'Enable or disable all optional components in bulk', 'buddypress' );
				?></label></td>
				<th class="manage-column column-title column-primary"><?php _e( 'Component', 'buddypress' ); ?></th>
				<th class="manage-column column-description"><?php _e( 'Description', 'buddypress' ); ?></th>
			</tr>
		</tfoot>

	</table>

	<input type="hidden" name="bp_components[members]" value="1" />

	<?php
}

/**
 * Handle saving the Component settings.
 *
 * @since 1.6.0
 *
 * @todo Use settings API when it supports saving network settings
 */
function bp_core_admin_components_settings_handler() {

	// Bail if not saving settings.
	if ( ! isset( $_POST['bp-admin-component-submit'] ) ) {
		return;
	}

	// Bail if nonce fails.
	if ( ! check_admin_referer( 'bp-admin-component-setup' ) ) {
		return;
	}

	// Settings form submitted, now save the settings. First, set active components.
	if ( isset( $_POST['bp_components'] ) ) {

		// Load up BuddyPress.
		$bp = buddypress();

		// Save settings and upgrade schema.
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		require_once( $bp->plugin_dir . '/bp-core/admin/bp-core-admin-schema.php' );

		$submitted = stripslashes_deep( $_POST['bp_components'] );
		$bp->active_components = bp_core_admin_get_active_components_from_submitted_settings( $submitted );

		// Get the installed BuddyPress plugins basenames.
		$basenames = wp_list_pluck( bp_core_get_installed_components(), 'basename' );

		if ( $basenames ) {
			// Find the components who have been activated or deactivated.
			$active_components = bp_get_option( 'bp-active-components', array() );
			$activated         = array_diff_key( $bp->active_components, $active_components );
			$deactivated       = array_diff_key( $active_components, $bp->active_components );

			// Activate plugins if needed.
			if ( $activated ) {
				$activate_plugins = array_intersect_key( $basenames, $activated );

				if ( $activate_plugins ) {
					activate_plugins( $activate_plugins, '', bp_is_network_activated() );
				}
			}

			// Deactivate plugins if needed.
			if ( $deactivated ) {
				$deactivate_plugins = array_intersect_key( $basenames, $deactivated );

				if ( $deactivate_plugins ) {
					deactivate_plugins( $deactivate_plugins, '', bp_is_network_activated() );
				}
			}
		}

		bp_core_install( $bp->active_components );
		bp_core_add_page_mappings( $bp->active_components );
		bp_update_option( 'bp-active-components', $bp->active_components );
	}

	// Where are we redirecting to?
	$base_url = bp_get_admin_url( add_query_arg( array( 'page' => 'bp-components', 'updated' => 'true' ), 'admin.php' ) );

	// Redirect.
	wp_redirect( $base_url );
	die();
}
add_action( 'bp_admin_init', 'bp_core_admin_components_settings_handler' );

/**
 * Calculates the components that should be active after save, based on submitted settings.
 *
 * The way that active components must be set after saving your settings must
 * be calculated differently depending on which of the Components subtabs you
 * are coming from:
 * - When coming from All or Active, the submitted checkboxes accurately
 *   reflect the desired active components, so we simply pass them through
 * - When coming from Inactive, components can only be activated - already
 *   active components will not be passed in the $_POST global. Thus, we must
 *   parse the newly activated components with the already active components
 *   saved in the $bp global
 * - When activating a Retired component, the situation is similar to Inactive.
 * - When deactivating a Retired component, no value is passed in the $_POST
 *   global (because the component settings are checkboxes). So, in order to
 *   determine whether a retired component is being deactivated, we retrieve a
 *   list of retired components, and check each one to ensure that its checkbox
 *   is not present, before merging the submitted components with the active
 *   ones.
 *
 * @since 1.7.0
 *
 * @param array $submitted This is the array of component settings coming from the POST
 *                         global. You should stripslashes_deep() before passing to this function.
 * @return array The calculated list of component settings
 */
function bp_core_admin_get_active_components_from_submitted_settings( $submitted ) {
	$current_action = 'all';

	if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'active', 'inactive', 'retired' ) ) ) {
		$current_action = $_GET['action'];
	}

	$current_components = buddypress()->active_components;

	switch ( $current_action ) {
		case 'retired' :
			$retired_components = bp_core_admin_get_components( 'retired' );
			foreach ( array_keys( $retired_components ) as $retired_component ) {
				if ( ! isset( $submitted[ $retired_component ] ) ) {
					unset( $current_components[ $retired_component ] );
				}
			} // Fall through.


		case 'inactive' :
			$components = array_merge( $submitted, $current_components );
			break;

		case 'all' :
		case 'active' :
		default :
			$components = $submitted;
			break;
	}

	return $components;
}

/**
 * Return a list of component information.
 *
 * We use this information both to build the markup for the admin screens, as
 * well as to do some processing on settings data submitted from those screens.
 *
 * @since 1.7.0
 *
 * @param string $type Optional; component type to fetch. Default value is 'all', or 'optional', 'retired', 'required'.
 * @return array Requested components' data.
 */
function bp_core_admin_get_components( $type = 'all' ) {
	$components = bp_core_get_components( $type );

	/**
	 * Filters the list of component information.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $components Array of component information.
	 * @param string $type       Type of component list requested.
	 *                           Possible values include 'all', 'optional',
	 *                           'retired', 'required'.
	 */
	return apply_filters( 'bp_core_admin_get_components', $components, $type );
}

/**
 * Bulk install installable components using JavaScript.
 *
 * @since 1.0.0
 *
 * @global string $parent_file
 * @global string $submenu_file
 */
function bp_core_admin_install_components() {
	global $parent_file, $submenu_file;

	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_die( __( 'Sorry, you are not allowed to install plugins on this site.', 'buddypress' ) );
	}

	check_admin_referer( 'bp-admin-component-setup' );

	$bp_plugins = array();
	if ( isset( $_POST['bp_components'] ) ) {
		$bp_plugins = array_diff_key(
			$_POST['bp_components'],
			array( 'members' => 1 )
		);
	}

	$wp_die_title = __( 'Component Installation error', 'buddypress' );

	if ( ! $bp_plugins ) {
		wp_die(
			__( 'Sorry, there are no selected components to install.', 'buddypress' ),
			$wp_die_title,
			array( 'back_link' => true )
		);
	}

	$installable_components = bp_core_get_installable_components( 'bp_plugins' );
	$installable_plugins    = wp_list_pluck( $installable_components, 'basename', 'component_id' );
	$bp_supported_plugins   = array_intersect_key( $installable_plugins, $bp_plugins );

	if ( ! $bp_supported_plugins ) {
		wp_die(
			__( 'Sorry, there are no components supported by the BuddyPress community to install.', 'buddypress' ),
			$wp_die_title,
			array( 'back_link' => true )
		);
	}

	// Set the active menu & submenu.
	$parent_file  = bp_core_do_network_admin() ? 'settings.php' : 'options-general.php';;
	$submenu_file = 'bp-components';

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$base_url = add_query_arg(
		array( 'action' => 'install-plugin' ),
		self_admin_url( 'update.php' )
	);

	foreach ( $installable_components as $key => $component ) {
		if ( ! isset( $bp_supported_plugins[ $component->component_id ] ) ) {
			unset( $installable_components[ $key ] );
		} elseif ( current_user_can( 'install_plugins' ) ) {
			$installable_components[ $key ]->url = wp_nonce_url(
				add_query_arg( 'plugin', $component->slug, $base_url ),
				'install-plugin_' . $component->slug
			);
		}
	}

	$plugin_num        = count( $installable_components );
	$active_components = bp_get_option( 'bp-active-components', array() );
	$form_action       = add_query_arg( 'page', 'bp-components', bp_get_admin_url( 'admin.php' ) );

	wp_enqueue_script( 'bp-install-js' );
	wp_localize_script( 'bp-install-js', 'bpInstallData', array(
		'bpPlugins' => $installable_components,
	) );
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Component Installation', 'buddypress' ); ?></h1>

		<p class="description">
			<?php
			/* translators %s is the number of plugins to install */
			echo esc_html(
				sprintf(
					_n(
						'Please wait until the download and activation of the plugin is finished.',
						'Please wait until the download and activation of the %s plugins is finished.',
						$plugin_num,
						'buddypress'
					),
					$plugin_num
				)
			);
			?>
		</p>

		<form id="plugin-filter" action="<?php echo esc_url( $form_action ); ?>" method="post">
			<div class="tablenav top">
				<div class="tablenav-pages one-page">
					<span class="displaying-num">
						<?php
						/* translators %s is the number of plugins to install */
						echo esc_html( sprintf( _n( '%s component', '%s components', $plugin_num, 'buddypress' ), $plugin_num ) );
						?>
					</span>
				</div>
				<br class="clear">
			</div>
			<div class="wp-list-table widefat plugin-install">
				<h2 class="screen-reader-text"><?php esc_html_e( 'List of BuddyPress plugins being installed', 'buddypress' ); ?></h2>

				<?php
				// Makes sure the active components will remain active.
				foreach ( $active_components as $component => $active ) : ?>
					<input type="hidden" name="bp_components[<?php echo esc_html( $component ); ?>]" value="<?php echo absint( $active ); ?>">
				<?php endforeach ; ?>

				<?php
				// Makes sure the installed components will have their plugins activated.
				foreach ( $installable_components as $plugin ) : ?>
					<input type="hidden" name="bp_components[<?php echo esc_html( $plugin->component_id ); ?>]" value="1">
				<?php endforeach ; ?>

				<input type="hidden" name="bp-admin-component-submit" value="1">
				<div id="the-list" data-list="buddypress-plugins"></div>
			</div>

			<?php wp_nonce_field( 'bp-admin-component-setup' ); ?>
		</form>
	</div>

	<?php
	wp_print_request_filesystem_credentials_modal();
	wp_print_admin_notice_templates();

	// Use the following JS Template to output plugins to install.
	?>
	<script type="text/html" id="tmpl-buddypress-plugin">
		<div class="plugin-card plugin-card-{{data.slug}}">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<span class="plugin-icon dashicons {{data.icon}}"></span>
						{{data.title}}
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<li>
							<a class="install-now button" data-slug="{{data.slug}}" href="{{{data.url}}}" aria-label="<?php esc_attr_e( 'Install now', 'buddypress' ); ?>" data-name="{{data.name}}"><?php esc_html_e( 'Install', 'BuddyPress' ); ?></a>
						</li>
					</ul>
				</div>
				<div class="desc column-description">
					<p>{{data.description}}</p>
				</div>
			</div>
		</div>
	</script>

	<?php
	include ABSPATH . 'wp-admin/admin-footer.php';
}
add_action( 'update-custom_install_bp_components', 'bp_core_admin_install_components' );
