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
 * Are pretty links active ?
 *
 * @since 6.0.0
 *
 * @return bool True if pretty links are on. False otherwise.
 */
function bp_has_pretty_links() {
	return !! get_option( 'permalink_structure', '' );
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

	// These hooks need to happen later.
	remove_action( 'bp_init', 'bp_setup_canonical_stack', 5 );
	add_action( 'bp_parse_query', 'bp_setup_canonical_stack', 11 );
	remove_action( 'bp_init', 'bp_setup_title', 8 );
	add_action( 'bp_parse_query', 'bp_setup_title', 14 );

	/**
	 * This hook needs to happen later on front-end only.
	 *
	 * @see `bp_nav_menu_get_loggedin_pages()`
	 */
	if ( ! is_admin() ) {
		remove_action( 'bp_init', 'bp_setup_nav', 6 );
		add_action( 'bp_parse_query', 'bp_setup_nav', 12 );
	}

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

	/**
	 * Then start using a new way of building BuddyPress URLs.
	 *
	 * Using filters let us safelyt edit our legacy way of building URLs.
	 */
	$filters = array(
		'bp_core_get_user_domain' => array(
			'function' => '_bp_rewrites_get_user_link',
			'num_args' => 3,
		),
		'bp_get_members_directory_permalink' => array(
			'function' => '_bp_rewrites_get_users_link',
			'num_args' => 1,
		),
		'bp_get_member_type_directory_permalink' => array(
			'function' => '_bp_rewrites_get_users_type_link',
			'num_args' => 2,
		),
		'bp_members_nav_add_item_link' => array(
			'function' => '_bp_rewrites_members_nav_link',
			'num_args' => 1,
		),
		'bp_members_subnav_add_item_link' => array(
			'function' => '_bp_rewrites_members_nav_link',
			'num_args' => 1,
		),
		'bp_activity_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
		),
		'bp_blogs_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
		),
		'bp_friends_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
		),
		'bp_groups_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
		),
		'bp_messages_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
		),
		'bp_notifications_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
		),
		'bp_settings_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
			'priority' => 3, // After xProfile filter.
		),
		'bp_xprofile_admin_nav' => array(
			'function' => '_bp_rewrites_user_admin_nav_link',
			'num_args' => 1,
		),
		'bp_members_edit_profile_url' => array(
			'function' => '_bp_rewrites_edit_profile_url',
			'num_args' => 3,
		),
		'bp_get_signup_page'  => array(
			'function' => '_bp_rewrites_get_signup_link',
			'num_args' => 1,
		),
		'bp_get_activation_page'  => array(
			'function' => '_bp_rewrites_get_activation_link',
			'num_args' => 3,
		),
		'bp_get_group_permalink' => array(
			'function' => '_bp_rewrites_get_group_url',
			'num_args' => 2,
		),
		'bp_get_groups_directory_permalink' => array(
			'function' => '_bp_rewrites_get_groups_url',
			'num_args' => 1,
		),
		'bp_get_group_type_directory_permalink' => array(
			'function' => '_bp_rewrites_get_group_type_url',
			'num_args' => 2,
		),
		'bp_groups_subnav_add_item_link' => array(
			'function' => '_bp_rewrites_groups_nav_link',
			'num_args' => 1,
		),
		'bp_get_groups_action_link' => array(
			'function' => '_bp_rewrites_get_group_admin_link',
			'num_args' => 4,
		),
		'bp_get_group_create_link' => array(
			'function' => '_bp_rewrites_get_group_create_link',
			'num_args' => 2,
		),
		'bp_get_activity_directory_permalink' => array(
			'function' => '_bp_rewrites_get_activities_url',
			'num_args' => 1,
		),
		'bp_get_activity_post_form_action' => array(
			'function' => '_bp_rewrites_get_activity_post_form_action',
			'num_args' => 1,
		),
		'bp_get_activity_comment_form_action' => array(
			'function' => '_bp_rewrites_get_activity_comment_form_action',
			'num_args' => 1,
		),
		'bp_activity_get_permalink' => array(
			'function' => '_bp_rewrites_get_activity_url',
			'num_args' => 2,
		),
		'bp_activity_permalink_redirect_url' => array(
			'function' => '_bp_rewrites_get_activity_permalink_redirect_url',
			'num_args' => 2,
		),
		'bp_get_activity_comment_link' => array(
			'function' => '_bp_rewrites_get_activity_comment_url',
			'num_args' => 1,
		),
		'bp_get_activity_favorite_link' => array(
			'function' => '_bp_rewrites_get_activity_favorite_url',
			'num_args' => 1,
		),
		'bp_get_activity_unfavorite_link' => array(
			'function' => '_bp_rewrites_get_activity_unfavorite_url',
			'num_args' => 1,
		),
		'bp_get_activity_delete_url' => array(
			'function' => '_bp_rewrites_get_activity_delete_url',
			'num_args' => 1,
		),
		'bp_get_sitewide_activity_feed_link' => array(
			'function' => '_bp_rewrites_get_sitewide_activity_feed_url',
			'num_args' => 1,
		),
		'bp_get_activities_member_rss_link' => array(
			'function' => '_bp_rewrites_get_activities_member_rss_url',
			'num_args' => 1,
		),
	);

	foreach ( $filters as $legacy => $rewrite ) {
		if ( ! isset( $rewrite['priority'] ) ) {
			$rewrite['priority'] = 1;
		}

		add_filter( $legacy, $rewrite['function'], $rewrite['priority'], $rewrite['num_args'] );
	}
}
add_action( 'bp_init', 'bp_disable_legacy_url_parser', 1 );

/**
 * Make sure WP Nav Menus still use the right links.
 *
 * @since 6.0.0
 *
 * @param  string  $link The post type link.
 * @param  WP_Post $post The post type object.
 * @return string        The post type link.
 */
function bp_page_directory_link( $link, WP_Post $post ) {
	if ( 'bp_directories' !== get_post_type( $post ) ) {
		return $link;
	}

	$directory_pages = wp_filter_object_list( (array) bp_core_get_directory_pages(), array( 'id' => $post->ID ) ) ;
	$component       = key( $directory_pages );

	return bp_rewrites_get_link( array( 'component_id' => $component ) );
}
add_filter( 'post_type_link', 'bp_page_directory_link', 1, 2 );

/**
 * Get needed data to find a member single item from the request.
 *
 * @since 6.0.0
 *
 * @param string $request The request used during parsing.
 * @return array Data to find a member single item from the request.
 */
function bp_rewrites_get_member_data( $request = '' ) {
	$member_data = array( 'field' => 'slug' );

	if ( bp_is_username_compatibility_mode() ) {
		$member_data = array( 'field' => 'login' );
	}

	if ( bp_core_enable_root_profiles() ) {
		if ( ! $request ) {
			$request = $GLOBALS['wp']->request;
		}

		$request_chunks = explode( '/', ltrim( $request, '/' ) );
		$member_chunk   = reset( $request_chunks );

		// Try to get an existing member to eventually reset the WP Query.
		$member_data['object'] = get_user_by( $member_data['field'], $member_chunk );
	}

	return $member_data;
}

function bp_rewrites_get_member_slug( $user_id = 0 ) {
	$bp = buddypress();

	$prop = 'user_nicename';
	if ( bp_is_username_compatibility_mode() ) {
		$prop = 'user_login';
	}

	if ( (int) $user_id === (int) bp_displayed_user_id() ) {
		$slug = isset( $bp->displayed_user->userdata->{$prop} ) ? $bp->displayed_user->userdata->{$prop} : null;
	} elseif ( (int) $user_id === (int) bp_loggedin_user_id() ) {
		$slug = isset( $bp->loggedin_user->userdata->{$prop} ) ? $bp->loggedin_user->userdata->{$prop} : null;
	} else {
		$slug = bp_core_get_username( $user_id );
	}

	return $slug;
}

/**
 * Makes sure BuddyPress globals are set during Ajax requests.
 *
 * @since 6.0.0
 */
function bp_parse_ajax_referer_query() {
	if ( ! wp_doing_ajax() || ! bp_use_wp_rewrites() ) {
		return;
	}

	$bp       = buddypress();
	$bp->ajax = (object) array(
		'WP' => new WP(),
	);

	bp_reset_query( bp_get_referer_path(), $GLOBALS['wp_query'] );
}
add_action( 'bp_admin_init', 'bp_parse_ajax_referer_query' );

/**
 * Resets the query to fit our permalink structure if needed.
 *
 * This is used for specific cases such as Root Member's profile or Ajax.
 *
 * @since 6.0.0
 *
 * @param string $bp_request A specific BuddyPress request.
 * @param WP_Query $query The WordPress query object.
 */
function bp_reset_query( $bp_request = '', WP_Query $query ) {
	global $wp;
	$bp = buddypress();

	// Back up request uri.
	$reset_server_request_uri = $_SERVER['REQUEST_URI'];

	// Temporarly override it.
	if ( isset( $wp->request ) ) {
		$_SERVER['REQUEST_URI'] = str_replace( $wp->request, $bp_request, $reset_server_request_uri );

		// Reparse request.
		$wp->parse_request();

		// Reparse query.
		bp_remove_all_filters( 'parse_query' );
		$query->parse_query( $wp->query_vars );
		bp_restore_all_filters( 'parse_query' );

	} elseif ( isset( $bp->ajax ) ) {
		// Extra step for root profiles
		$member = bp_rewrites_get_member_data( $bp_request );
		if ( isset( $member['object'] ) && $member['object'] ) {
			$bp_request = '/' . $bp->members->root_slug . $bp_request;
		}

		$_SERVER['REQUEST_URI'] = $bp_request;

		if ( bp_has_pretty_links() ) {
			$bp->ajax->WP->parse_request();
			$matched_query = $bp->ajax->WP->matched_query;
		} else {
			$matched_query = wp_parse_url( $bp_request, PHP_URL_QUERY );
		}

		$query->parse_query( $matched_query );

		// Do this only once.
		remove_action( 'parse_query', 'bp_parse_query', 2 );
	}

	// Restore request uri.
	$_SERVER['REQUEST_URI'] = $reset_server_request_uri;

	// The query is reset.
	return true;
}

/**
 * Returns the slug to use for the nav item of the requested component.
 *
 * @since 6.0.0
 *
 * @param string $component_id The BuddyPress component's ID.
 * @param string $rewrite_id   The nav item's rewrite ID.
 * @param string $default_slug The nav item's default slug.
 * @return string              The slug to use for the nav item of the requested component.
 */
function bp_rewrites_get_slug( $component_id = '', $rewrite_id = '', $default_slug = '' ) {
	$directory_pages = bp_core_get_directory_pages();
	$slug            = $default_slug;

	if ( ! isset( $directory_pages->{$component_id}->custom_slugs ) || ! $rewrite_id ) {
		return $slug;
	}

	$custom_slugs = (array) $directory_pages->{$component_id}->custom_slugs;
	if ( isset( $custom_slugs[ $rewrite_id ] ) && $custom_slugs[ $rewrite_id ] ) {
		$slug = $custom_slugs[ $rewrite_id ];
	}

	return $slug;
}

function bp_rewrites_get_custom_slug_rewrite_id( $component_id = '', $slug = '' ) {
	$directory_pages = bp_core_get_directory_pages();

	if ( ! isset( $directory_pages->{$component_id}->custom_slugs ) || ! $slug ) {
		return null;
	}

	$custom_slugs = (array) $directory_pages->{$component_id}->custom_slugs;

	// If there's a match it's a custom slug.
	return array_search( $slug, $custom_slugs );
}

/**
 * Builds a BuddyPress link using the WP Rewrite API.
 *
 * @todo Allow customization using `bp_rewrites_get_slug()`
 *       Describe parameter.
 *
 * @since 6.0.0
 *
 * @param array $args
 * @return string The BuddyPress link.
 */
function bp_rewrites_get_link( $args = array() ) {
	$bp   = buddypress();
	$link = '#';

	$r = wp_parse_args( $args, array(
		'component_id'                 => '',
		'directory_type'               => '',
		'single_item'                  => '',
		'single_item_component'        => '',
		'single_item_action'           => '',
		'single_item_action_variables' => array(),
	) );

	$is_pretty_links = bp_has_pretty_links();

	if ( ! isset( $bp->{$r['component_id']} ) ) {
		return $link;
	}

	$component = $bp->{$r['component_id']};
	unset( $r['component_id'] );

	// Using plain links.
	if ( ! $is_pretty_links ) {
		if ( ! isset( $r['member_register'] ) && ! isset( $r['member_activate'] ) ) {
			$r['directory'] = 1;
		}

		$r              = array_filter( $r );
		$qv             = array();

		foreach ( $component->rewrite_ids as $key => $rewrite_id ) {
			if ( ! isset( $r[ $key ] ) ) {
				continue;
			}

			$qv[ $rewrite_id ] = $r[ $key ];
		}

		$link = add_query_arg( $qv, home_url( '/' ) );

	// Using pretty links.
	} else {
		if ( ! isset( $component->rewrite_ids['directory'] ) || ! isset( $component->directory_permastruct ) ) {
			return $link;
		}

		if ( isset( $r['member_register'] ) ) {
			$link = str_replace( '%' . $component->rewrite_ids['member_register'] . '%', '', $component->register_permastruct );
			unset( $r['member_register'] );
		} elseif ( isset( $r['member_activate'] ) ) {
			$link = str_replace( '%' . $component->rewrite_ids['member_activate'] . '%', '', $component->activate_permastruct );
			unset( $r['member_activate'] );
		} elseif ( isset( $r['create_single_item'] ) ) {
			$link = str_replace( '%' . $component->rewrite_ids['directory'] . '%', 'create', $component->directory_permastruct );
			unset( $r['create_single_item'] );
		} else {
			$link = str_replace( '%' . $component->rewrite_ids['directory'] . '%', $r['single_item'], $component->directory_permastruct );
			unset( $r['single_item'] );
		}

		$r = array_filter( $r );

		if ( isset( $r['directory_type'] ) && $r['directory_type'] ) {
			if ( 'members' === $component->id ) {
				array_unshift( $r, bp_get_members_member_type_base() );
			} elseif ( 'groups' === $component->id ) {
				array_unshift( $r, bp_get_groups_group_type_base() );
			} else {
				unset( $r['directory_type'] );
			}
		}

		if ( isset( $r['single_item_action_variables'] ) && $r['single_item_action_variables'] ) {
			$r['single_item_action_variables'] = join( '/', (array) $r['single_item_action_variables'] );
		}

		if ( isset( $r['create_single_item_variables'] ) && $r['create_single_item_variables'] ) {
			$r['create_single_item_variables'] = join( '/', (array) $r['create_single_item_variables'] );
		}

		$link = home_url( user_trailingslashit( '/' . rtrim( $link, '/' ) . '/' . join( '/', $r ) ) );
	}

	return $link;
}

function _bp_rewrites_get_user_link( $link = '', $user_id = 0, $username = '' ) {
	if ( ! $user_id ) {
		return $link;
	}

	$bp = buddypress();
	if ( ! $username ) {
		$username = bp_rewrites_get_member_slug( $user_id );
	}

	$link = bp_rewrites_get_link( array(
		'component_id' => 'members',
		'single_item'  => $username,
	) );

	if ( bp_core_enable_root_profiles() && bp_has_pretty_links() ) {
		$link = str_replace( $bp->members->root_slug . '/', '', $link );
	}

	return $link;
}

function _bp_rewrites_get_users_link( $link = '' ) {
	return bp_rewrites_get_link( array(
		'component_id' => 'members',
	) );
}

function _bp_rewrites_get_users_type_link( $link = '', $type = null ) {
	if ( ! isset( $type->directory_slug ) ) {
		return $link;
	}

	return bp_rewrites_get_link( array(
		'component_id'   => 'members',
		'directory_type' => $type->directory_slug,
	) );
}

/**
 * Edit the link parameter of the members primary/secondary nav item links.
 *
 * @see bp_core_create_nav_link() for description of parameters.
 *
 * @since 6.0.0
 *
 * @param  array $args The arguments used to create the primary/secondary nav item.
 * @return array       The arguments used to create the primary/secondary nav item.
 */
function _bp_rewrites_members_nav_link( $args = array() ) {
	$bp      = buddypress();
	$user_id = bp_displayed_user_id();
	if ( ! $user_id ) {
		$user_id = bp_loggedin_user_id();
	}

	$username = bp_rewrites_get_member_slug( $user_id );
	if ( ! $username ) {
		return $args;
	}

	if ( 'bp_members_nav_add_item_link' === current_filter() ) {
		$link_params = array(
			'component_id'          => 'members',
			'single_item'           => $username,
			'single_item_component' => bp_rewrites_get_slug( 'members', $args['rewrite_id'], $args['slug'] ),
		);
	} else {
		$parent_nav = $bp->members->nav->get_primary( array( 'slug' => $args['parent_slug'] ), false );
		if ( ! $parent_nav ) {
			return $args;
		}

		$parent_nav = reset( $parent_nav );
		if ( ! isset( $parent_nav->rewrite_id ) ) {
			return $args;
		}

		$link_params = array(
			'component_id'          => 'members',
			'single_item'           => $username,
			'single_item_component' => bp_rewrites_get_slug( 'members', $parent_nav->rewrite_id, $args['parent_slug'] ),
			'single_item_action'    => $args['slug'],
		);
	}

	if ( ! isset( $link_params ) ) {
		return $args;
	}

	$link = bp_rewrites_get_link( $link_params );

	if ( bp_core_enable_root_profiles() && bp_has_pretty_links() ) {
		$link = str_replace( $bp->members->root_slug . '/', '', $link );
	}

	$args['link'] = $link;

	return $args;
}

function _bp_rewrites_user_admin_nav_link( $wp_admin_nav = array() ) {
	$bp = buddypress();
	$username = bp_rewrites_get_member_slug( bp_loggedin_user_id() );
	if ( ! $username ) {
		return $wp_admin_nav;
	}

	$parent         = '';
	$component_slug = '';
	$link_args      = array(
		'component_id' => 'members',
		'single_item'  => $username,
	);

	foreach ( $wp_admin_nav as $index_nav => $nav_item ) {
		$component      = '';
		$component_url  = '';

		if ( bp_has_pretty_links() ) {
			$url_parts = explode( '/', rtrim( wp_parse_url( $wp_admin_nav[ $index_nav ]['href'], PHP_URL_PATH ), '/' ) );
		} else {
			$url_parts = wp_parse_args( wp_parse_url( $wp_admin_nav[ $index_nav ]['href'], PHP_URL_QUERY ), array() );

			if ( isset( $url_parts['bp_member'] ) ) {
				// Move added slugs to the `bp_member` query var at the end of the URL parts.
				$url_parts = array_merge(
					$url_parts,
					explode( '/', rtrim( str_replace( $username, '', $url_parts['bp_member'] ), '/' ) )
				);

				// Make sure the `bp_member` query var is consistent.
				$url_parts['bp_member'] = $username;
			}
		}

		$slug = end( $url_parts );

		// Make sure to reset the item action at each loop.
		$link_args['single_item_action']    = '';

		// This is the single item compnent's main nav item.
		if ( $bp->my_account_menu_id === $nav_item['parent'] ) {
			/**
			 * Make sure to reset the item component, component slug and parent
			 * at each main nav item change.
			 */
			$link_args['single_item_component'] = '';
			$component_slug                     = '';
			$parent                             = '';

			$component      = str_replace( 'my-account-', '', $nav_item['id'] );
			$parent         = $nav_item['id'];
			$component_slug = $slug;

			// Specific to Extended Profiles.
			if ( 'xprofile' === $component ) {
				$component = 'profile';
			}

			$link_args['single_item_component'] = bp_rewrites_get_slug( 'members', 'bp_member_' . $component, $component_slug );
			$wp_admin_nav[ $index_nav ]['href'] = bp_rewrites_get_link( $link_args );
		}

		if ( $component_slug && $parent === $nav_item['parent'] && false !== strpos( $nav_item['href'], $username ) ) {
			$link_args['single_item_action']    = $slug !== $component_slug ? $slug : '';
			$wp_admin_nav[ $index_nav ]['href'] = bp_rewrites_get_link( $link_args );
		}
	}

	return $wp_admin_nav;
}

function _bp_rewrites_edit_profile_url( $profile_link = '', $url = '', $user_id = 0 ) {
	if ( ! is_admin() && bp_is_active( 'xprofile' ) ) {
		$profile_link = bp_rewrites_get_link( array(
			'component_id'          => 'members',
			'single_item'           => bp_rewrites_get_member_slug( $user_id ),
			'single_item_component' => bp_rewrites_get_slug( 'members', 'bp_member_profile', bp_get_profile_slug() ),
			'single_item_action'    => 'edit'
		) );
	}

	return $profile_link;
}

function _bp_rewrites_get_signup_link( $link = '' ) {
	return bp_rewrites_get_link( array(
		'component_id'    => 'members',
		'member_register' => 1,
	) );
}

function _bp_rewrites_get_activation_link( $link = '', $key = '', $has_custom_activation_page = false ) {
	if ( ! $has_custom_activation_page ) {
		return $link;
	}

	$link_params = array(
		'component_id'    => 'members',
		'member_activate' => 1,
	);

	if ( $key ) {
		$link_params['member_activate_key'] = $key;
	}

	return bp_rewrites_get_link( $link_params );
}

function _bp_rewrites_get_group_url( $link = '', $group = null ) {
	if ( ! isset( $group->id ) || ! $group->id ) {
		return $link;
	}

	return bp_rewrites_get_link( array(
		'component_id' => 'groups',
		'single_item'  => bp_get_group_slug( $group ),
	) );
}

function _bp_rewrites_get_groups_url( $link = '' ) {
	return bp_rewrites_get_link( array(
		'component_id' => 'groups',
	) );
}

function _bp_rewrites_get_group_type_url( $link = '', $type = null ) {
	if ( ! isset( $type->directory_slug ) ) {
		return $link;
	}

	return bp_rewrites_get_link( array(
		'component_id'   => 'groups',
		'directory_type' => $type->directory_slug,
	) );
}

/**
 * Edit the link parameter of the group's secondary nav item links.
 *
 * @see bp_core_create_subnav_link() for description of parameters.
 *
 * @since 6.0.0
 *
 * @param  array $args The arguments used to create the secondary nav item.
 * @return array       The arguments used to create the secondary nav item.
 */
function _bp_rewrites_groups_nav_link( $args = array() ) {
	if ( ! isset( $args['parent_slug'] ) || ! isset( $args['slug'] ) ) {
		return $args;
	}

	$single_item        = $args['parent_slug'];
	$single_item_action = $args['slug'];

	if ( false !== strpos( $single_item, '_manage' ) ) {
		$single_item_action = 'admin';
		$single_item = str_replace( '_manage', '', $single_item );
	}

	$link_params = array(
		'component_id'       => 'groups',
		'single_item'        => $single_item,
		'single_item_action' => $single_item_action,
	);

	if ( 'admin' === $single_item_action && 'admin' !== $args['slug'] ) {
		$link_params['single_item_action_variables'] = explode( '/', $args['slug'] );
	}

	$args['link'] = bp_rewrites_get_link( $link_params );

	return $args;
}

function _bp_rewrites_get_group_admin_link( $link = '', $action = '', $query_args = array(), $nonce = false ) {
	if ( ! $action ) {
		return $link;
	}

	$group = groups_get_current_group();
	if ( ! isset( $group->slug ) ) {
		return $link;
	}

	$single_item_action_variables = explode( '/', rtrim( $action, '/' ) );
	$single_item_action           = array_shift( $single_item_action_variables );

	$link = bp_rewrites_get_link( array(
		'component_id'                 => 'groups',
		'single_item'                  => $group->slug,
		'single_item_action'           => $single_item_action,
		'single_item_action_variables' => $single_item_action_variables,
	) );

	if ( $query_args && is_array( $query_args ) ) {
		$link = add_query_arg( $query_args, $link );
	}

	if ( true === $nonce ) {
		$link = wp_nonce_url( $link );
	} elseif ( is_string( $nonce ) ) {
		$link = wp_nonce_url( $link, $nonce );
	}

	return $link;
}

function _bp_rewrites_get_group_create_link( $link = '', $step = '' ) {
	$link_params = array(
		'component_id'       => 'groups',
		'create_single_item' => 1,
	);

	if ( $step ) {
		$link_params['create_single_item_variables'] = array( 'step', $step );
	}

	return bp_rewrites_get_link( $link_params );
}

function _bp_rewrites_get_activities_url( $link = '' ) {
	return bp_rewrites_get_link( array(
		'component_id' => 'activity',
	) );
}

function _bp_rewrites_get_activity_post_form_action( $link = '' ) {
	return bp_rewrites_get_link( array(
		'component_id'       => 'activity',
		'single_item_action' => 'post',
	) );
}

function _bp_rewrites_get_activity_comment_form_action( $link = '' ) {
	return bp_rewrites_get_link( array(
		'component_id'       => 'activity',
		'single_item_action' => 'reply',
	) );
}

function _bp_rewrites_get_activity_url( $link = '', $activity = null ) {
	if ( ! isset( $activity->primary_link ) || $link === $activity->primary_link ) {
		return $link;
	}

	$link_params = array(
		'component_id'                 => 'activity',
		'single_item_action'           => 'p',
		'single_item_action_variables' => array( $activity->id ),
	);

	if ( 'activity_comment' === $activity->type ) {
		$link_params['single_item_action_variables'] = array( $activity->item_id );
	}

	$link = bp_rewrites_get_link( $link_params );

	if ( 'activity_comment' === $activity->type ) {
		$link .= '#acomment-' . $activity->id;
	}

	return $link;
}

function _bp_rewrites_get_activity_comment_url( $link = '' ) {
	if ( bp_has_pretty_links() ) {
		return $link;
	}

	$url_parts = explode( '/', $link );
	$query_var = wp_parse_args( ltrim( $url_parts[0], '?' ), array( 'ac' => 0 ) );
	$anchor    = end( $url_parts );

	if ( bp_is_activity_directory() ) {
		$link = _bp_rewrites_get_activities_url();

	} else {
		global $activities_template;

		$link = bp_rewrites_get_link( array(
			'component_id'                 => 'activity',
			'single_item_action'           => 'p',
			'single_item_action_variables' => array( $activities_template->activity->id ),
		) );
	}

	return add_query_arg( $query_var, $link ) . $anchor;
}

function _bp_rewrites_get_activity_favorite_url( $link = '' ) {
	global $activities_template;

	$link = bp_rewrites_get_link( array(
		'component_id'                 => 'activity',
		'single_item_action'           => 'favorite',
		'single_item_action_variables' => array( $activities_template->activity->id ),
	) );

	return wp_nonce_url( $link, 'mark_favorite' );
}

function _bp_rewrites_get_activity_unfavorite_url(  $link = '' ) {
	global $activities_template;

	$link = bp_rewrites_get_link( array(
		'component_id'                 => 'activity',
		'single_item_action'           => 'unfavorite',
		'single_item_action_variables' => array( $activities_template->activity->id ),
	) );

	return wp_nonce_url( $link, 'unmark_favorite' );
}

function _bp_rewrites_get_activity_delete_url( $link = '' ) {
	global $activities_template;
	$query_vars = wp_parse_args( wp_parse_url( $link, PHP_URL_QUERY ), array() );

	$link = bp_rewrites_get_link( array(
		'component_id'                 => 'activity',
		'single_item_action'           => 'delete',
		'single_item_action_variables' => array( $activities_template->activity->id ),
	) );

	return add_query_arg( $query_vars, $link );
}

function _bp_rewrites_get_sitewide_activity_feed_url( $link = '' ) {
	return bp_rewrites_get_link( array(
		'component_id'       => 'activity',
		'single_item_action' => 'feed',
	) );
}

function _bp_rewrites_get_activities_member_rss_url( $link = '' ) {
	$link_params = array(
		'component_id'          => 'members',
		'single_item'           => bp_rewrites_get_member_slug( bp_displayed_user_id() ),
		'single_item_component' => bp_rewrites_get_slug( 'members', 'bp_member_activity', bp_get_activity_slug() ),
	);

	if ( bp_is_user_activity() ) {
		if ( bp_is_user_friends_activity() ) {
			$link_params['single_item_action'] = bp_get_friends_slug();
			$link_params['single_item_action_variables'] = 'feed';
		} elseif( bp_is_user_groups_activity() ) {
			$link_params['single_item_action'] = bp_get_groups_slug();
			$link_params['single_item_action_variables'] = 'feed';
		} elseif( 'favorites' === bp_current_action() ) {
			$link_params['single_item_action'] = 'favorites';
			$link_params['single_item_action_variables'] = 'feed';
		} elseif( 'mentions' === bp_current_action() && bp_activity_do_mentions() ) {
			$link_params['single_item_action'] = 'mentions';
			$link_params['single_item_action_variables'] = 'feed';
		} else {
			$link_params['single_item_action'] = 'feed';
		}
	}

	return bp_rewrites_get_link( $link_params );
}

function _bp_rewrites_get_activity_permalink_redirect_url( $redirect = '', $activity = null ) {
	if ( ! $redirect || ! isset( $activity->user_id ) ) {
		return $redirect;
	}

	// This shouldn't happen so often!
	if ( bp_is_active( 'groups') && 'groups' === $activity->component && ! $activity->user_id ) {
		$group = groups_get_group( $activity->item_id );

		$link_params = array(
			'component_id'                 => 'groups',
			'single_item'                  => bp_get_group_slug( $group ),
			'single_item_action'           => bp_get_activity_slug(),
			'single_item_action_variables' => array( $activity->id ),
		);
	} else {
		$link_params = array(
			'component_id'          => 'members',
			'single_item'           => bp_rewrites_get_member_slug( $activity->user_id ),
			'single_item_component' => bp_rewrites_get_slug( 'members', 'bp_member_activity', bp_get_activity_slug() ),
			'single_item_action'    => $activity->id,
		);
	}

	$redirect = bp_rewrites_get_link( $link_params );

	// If set, add the original query string back onto the redirect URL.
	if ( isset( $_SERVER['QUERY_STRING'] ) ) {
		$query_vars = array();
		wp_parse_str( $_SERVER['QUERY_STRING'], $query_vars );
		$exclude_vars = array_intersect_key( $query_vars, array_flip( buddypress()->activity->rewrite_ids ) );
		$query_vars = array_diff_key( $query_vars, $exclude_vars );

		if ( $query_vars ) {
			$redirect = add_query_arg( urlencode_deep( $query_vars ), $redirect );
		}
	}

	return $redirect;
}
