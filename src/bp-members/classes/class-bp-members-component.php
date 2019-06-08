<?php
/**
 * BuddyPress Member Loader.
 *
 * @package BuddyPress
 * @subpackage Members
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Defines the BuddyPress Members Component.
 *
 * @since 1.5.0
 */
class BP_Members_Component extends BP_Component {

	/**
	 * Member types.
	 *
	 * @see bp_register_member_type()
	 *
	 * @since 2.2.0
	 * @var array
	 */
	public $types = array();

	/**
	 * Start the members component creation process.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {
		parent::start(
			'members',
			__( 'Members', 'buddypress' ),
			buddypress()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 20,
				'search_query_arg' => 'members_search',
			)
		);
	}

	/**
	 * Include bp-members files.
	 *
	 * @since 1.5.0
	 *
	 * @see BP_Component::includes() for description of parameters.
	 *
	 * @param array $includes See {@link BP_Component::includes()}.
	 */
	public function includes( $includes = array() ) {

		// Always include these files.
		$includes = array(
			'filters',
			'template',
			'adminbar',
			'functions',
			'widgets',
			'cache',
		);

		if ( bp_is_active( 'activity' ) ) {
			$includes[] = 'activity';
		}

		// Include these only if in admin.
		if ( is_admin() ) {
			$includes[] = 'admin';
		}

		parent::includes( $includes );
	}

	/**
	 * Late includes method.
	 *
	 * Only load up certain code when on specific pages.
	 *
	 * @since 3.0.0
	 */
	public function late_includes() {
		// Bail if PHPUnit is running.
		if ( defined( 'BP_TESTS_DIR' ) ) {
			return;
		}

		// Members.
		if ( bp_is_members_component() ) {
			// Actions - Random member handler.
			if ( isset( $_GET['random-member'] ) ) {
				require $this->path . 'bp-members/actions/random.php';
			}

			// Screens - Directory.
			if ( bp_is_members_directory() ) {
				require $this->path . 'bp-members/screens/directory.php';
			}
		}

		// Members - User main nav screen.
		if ( bp_is_user() ) {
			require $this->path . 'bp-members/screens/profile.php';
		}

		// Members - Theme compatibility.
		if ( bp_is_members_component() || bp_is_user() ) {
			new BP_Members_Theme_Compat();
		}

		// Registration / Activation.
		if ( bp_is_register_page() || bp_is_activation_page() ) {
			if ( bp_is_register_page() ) {
				require $this->path . 'bp-members/screens/register.php';
			} else {
				require $this->path . 'bp-members/screens/activate.php';
			}

			// Theme compatibility.
			new BP_Registration_Theme_Compat();
		}
	}

	/**
	 * Set up bp-members global settings.
	 *
	 * The BP_MEMBERS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5.0
	 *
	 * @see BP_Component::setup_globals() for description of parameters.
	 *
	 * @param array $args See {@link BP_Component::setup_globals()}.
	 */
	public function setup_globals( $args = array() ) {
		global $wpdb;

		$bp = buddypress();

		/** Component Globals ************************************************
		 */

		// Define a slug, as a fallback for backpat.
		if ( !defined( 'BP_MEMBERS_SLUG' ) ) {
			define( 'BP_MEMBERS_SLUG', $this->id );
		}

		// Fetch the default directory title.
		$default_directory_titles = bp_core_get_directory_page_default_titles();
		$default_directory_title  = $default_directory_titles[$this->id];

		// Override any passed args.
		$args = array(
			'slug'            => BP_MEMBERS_SLUG,
			'root_slug'       => isset( $bp->pages->members->slug ) ? $bp->pages->members->slug : BP_MEMBERS_SLUG,
			'has_directory'   => true,
			'rewrite_ids'     => array(
				'directory'                    => 'bp_members',
				'directory_type'               => 'bp_members_type',
				'single_item'                  => 'bp_member',
				'single_item_component'        => 'bp_member_component',
				'single_item_action'           => 'bp_member_action',
				'single_item_action_variables' => 'bp_member_action_variables',
			),
			'directory_title' => isset( $bp->pages->members->title ) ? $bp->pages->members->title : $default_directory_title,
			'search_string'   => __( 'Search Members...', 'buddypress' ),
			'global_tables'   => array(
				'table_name_last_activity' => bp_core_get_table_prefix() . 'bp_activity',
				'table_name_signups'       => $wpdb->base_prefix . 'signups', // Signups is a global WordPress table.
			)
		);

		parent::setup_globals( $args );

		/** Logged in user ***************************************************
		 */

		// The core userdata of the user who is currently logged in.
		$bp->loggedin_user->userdata       = bp_core_get_core_userdata( bp_loggedin_user_id() );

		// Fetch the full name for the logged in user.
		$bp->loggedin_user->fullname       = isset( $bp->loggedin_user->userdata->display_name ) ? $bp->loggedin_user->userdata->display_name : '';

		// Hits the DB on single WP installs so get this separately.
		$bp->loggedin_user->is_super_admin = $bp->loggedin_user->is_site_admin = is_super_admin( bp_loggedin_user_id() );

		// The domain for the user currently logged in. eg: http://example.com/members/andy.
		$bp->loggedin_user->domain         = bp_core_get_user_domain( bp_loggedin_user_id() );

		/** Displayed user ***************************************************
		 */

		// The core userdata of the user who is currently being displayed.
		$bp->displayed_user->userdata = bp_core_get_core_userdata( bp_displayed_user_id() );

		// Fetch the full name displayed user.
		$bp->displayed_user->fullname = isset( $bp->displayed_user->userdata->display_name ) ? $bp->displayed_user->userdata->display_name : '';

		// The domain for the user currently being displayed.
		$bp->displayed_user->domain   = bp_core_get_user_domain( bp_displayed_user_id() );

		// Initialize the nav for the members component.
		$this->nav = new BP_Core_Nav();

		// If A user is displayed, check if there is a front template
		if ( bp_get_displayed_user() ) {
			$bp->displayed_user->front_template = bp_displayed_user_get_front_template();
		}

		/** Signup ***********************************************************
		 */

		$bp->signup = new stdClass;

		/** Profiles Fallback ************************************************
		 */

		if ( ! bp_is_active( 'xprofile' ) ) {
			$bp->profile       = new stdClass;
			$bp->profile->slug = 'profile';
			$bp->profile->id   = 'profile';
		}
	}

	/**
	 * Set up canonical stack for this component.
	 *
	 * @since 2.1.0
	 */
	public function setup_canonical_stack() {
		$bp = buddypress();

		/** Default Profile Component ****************************************
		 */
		if ( bp_displayed_user_has_front_template() ) {
			$bp->default_component = 'front';
		} elseif ( bp_is_active( 'activity' ) && isset( $bp->pages->activity ) ) {
			$bp->default_component = bp_get_activity_slug();
		} else {
			$bp->default_component = ( 'xprofile' === $bp->profile->id ) ? 'profile' : $bp->profile->id;
		}

		if ( defined( 'BP_DEFAULT_COMPONENT' ) && BP_DEFAULT_COMPONENT ) {
			$default_component = BP_DEFAULT_COMPONENT;
			if ( 'profile' === $default_component ) {
				$default_component = 'xprofile';
			}

			if ( bp_is_active( $default_component ) ) {
				$bp->default_component = BP_DEFAULT_COMPONENT;
			}
		}

		/** Canonical Component Stack ****************************************
		 */

		if ( bp_displayed_user_id() ) {
			$bp->canonical_stack['base_url'] = bp_displayed_user_domain();

			if ( bp_current_component() ) {
				$item_component = bp_current_component();
				$bp->canonical_stack['component'] = $item_component;

				if ( bp_use_wp_rewrites() ) {
					$bp->canonical_stack['component'] = bp_rewrites_get_slug( 'members', 'bp_member_' . $item_component, $item_component );
				}
			}

			if ( bp_current_action() ) {
				$bp->canonical_stack['action'] = bp_current_action();
			}

			if ( !empty( $bp->action_variables ) ) {
				$bp->canonical_stack['action_variables'] = bp_action_variables();
			}

			// Looking at the single member root/home, so assume the default.
			if ( ! bp_current_component() ) {
				$bp->current_component = $bp->default_component;

			// The canonical URL will not contain the default component.
			} elseif ( bp_is_current_component( $bp->default_component ) && ! bp_current_action() ) {
				unset( $bp->canonical_stack['component'] );
			}

			// If we're on a spammer's profile page, only users with the 'bp_moderate' cap
			// can view subpages on the spammer's profile.
			//
			// users without the cap trying to access a spammer's subnav page will get
			// redirected to the root of the spammer's profile page.  this occurs by
			// by removing the component in the canonical stack.
			if ( bp_is_user_spammer( bp_displayed_user_id() ) && ! bp_current_user_can( 'bp_moderate' ) ) {
				unset( $bp->canonical_stack['component'] );
			}
		}
	}

	/**
	 * Set up fall-back component navigation if XProfile is inactive.
	 *
	 * @since 1.5.0
	 *
	 * @see BP_Component::setup_nav() for a description of arguments.
	 *
	 * @param array $main_nav Optional. See BP_Component::setup_nav() for
	 *                        description.
	 * @param array $sub_nav  Optional. See BP_Component::setup_nav() for
	 *                        description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Don't set up navigation if there's no member.
		if ( ! is_user_logged_in() && ! bp_is_user() ) {
			return;
		}

		$is_xprofile_active = bp_is_active( 'xprofile' );

		// Bail if XProfile component is active and there's no custom front page for the user.
		if ( ! bp_displayed_user_has_front_template() && $is_xprofile_active ) {
			return;
		}

		// Determine user to use.
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		// Set slug to profile in case the xProfile component is not active
		$slug = bp_get_profile_slug();

		// Defaults to empty navs
		$this->main_nav = array();
		$this->sub_nav  = array();

		if ( ! $is_xprofile_active ) {
			$this->main_nav = array(
				'name'                => _x( 'Profile', 'Member profile main navigation', 'buddypress' ),
				'slug'                => $slug,
				'position'            => 20,
				'screen_function'     => 'bp_members_screen_display_profile',
				'default_subnav_slug' => 'public',
				'item_css_id'         => buddypress()->profile->id,
				'rewrite_id'          => 'bp_member_profile',
			);
		}

		/**
		 * Setup the subnav items for the member profile.
		 *
		 * This is required in case there's a custom front or in case the xprofile component
		 * is not active.
		 */
		$this->sub_nav = array(
			'name'            => _x( 'View', 'Member profile view', 'buddypress' ),
			'slug'            => 'public',
			'parent_url'      => trailingslashit( $user_domain . $slug ),
			'parent_slug'     => $slug,
			'screen_function' => 'bp_members_screen_display_profile',
			'position'        => 10
		);

		/**
		 * If there's a front template the members component nav
		 * will be there to display the user's front page.
		 */
		if ( bp_displayed_user_has_front_template() ) {
			$main_nav = array(
				'name'                => _x( 'Home', 'Member Home page', 'buddypress' ),
				'slug'                => 'front',
				'position'            => 5,
				'screen_function'     => 'bp_members_screen_display_profile',
				'default_subnav_slug' => 'public',
				'rewrite_id'          => 'bp_member_front',
			);

			// We need a dummy subnav for the front page to load.
			$front_subnav = $this->sub_nav;
			$front_subnav['parent_slug'] = 'front';

			// In case the subnav is displayed in the front template
			$front_subnav['parent_url'] = trailingslashit( $user_domain . 'front' );

			// Set the subnav
			$sub_nav[] = $front_subnav;

			/**
			 * If the profile component is not active, we need to create a new
			 * nav to display the WordPress profile.
			 */
			if ( ! $is_xprofile_active ) {
				add_action( 'bp_members_setup_nav', array( $this, 'setup_profile_nav' ) );
			}

		/**
		 * If there's no front template and xProfile is not active, the members
		 * component nav will be there to display the WordPress profile
		 */
		} else {
			$main_nav  = $this->main_nav;
			$sub_nav[] = $this->sub_nav;
		}


		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up a profile nav in case the xProfile
	 * component is not active and a front template is
	 * used.
	 *
	 * @since 2.6.0
	 */
	public function setup_profile_nav() {
		if ( empty( $this->main_nav ) || empty( $this->sub_nav ) ) {
			return;
		}

		// Add the main nav
		bp_core_new_nav_item( $this->main_nav, 'members' );

		// Add the sub nav item.
		bp_core_new_subnav_item( $this->sub_nav, 'members' );
	}

	/**
	 * Set up the title for pages and <title>.
	 *
	 * @since 1.5.0
	 */
	public function setup_title() {
		$bp = buddypress();

		if ( bp_is_my_profile() ) {
			$bp->bp_options_title = __( 'You', 'buddypress' );
		} elseif ( bp_is_user() ) {
			$bp->bp_options_title  = bp_get_displayed_user_fullname();
			$bp->bp_options_avatar = bp_core_fetch_avatar( array(
				'item_id' => bp_displayed_user_id(),
				'type'    => 'thumb',
				'alt'     => sprintf( __( 'Profile picture of %s', 'buddypress' ), $bp->bp_options_title )
			) );
		}

		parent::setup_title();
	}

	/**
	 * Setup cache groups.
	 *
	 * @since 2.2.0
	 */
	public function setup_cache_groups() {

		// Global groups.
		wp_cache_add_global_groups( array(
			'bp_last_activity',
			'bp_member_type'
		) );

		parent::setup_cache_groups();
	}

	/**
	 * Add the component's rewrite tags.
	 *
	 * @since 6.0.0
	 *
	 * @param array $rewrite_tags Optional. See BP_Component::add_rewrite_tags() for
	 *                            description.
	 */
	public function add_rewrite_tags( $rewrite_tags = array() ) {
		if ( ! bp_use_wp_rewrites() ) {
			return parent::add_rewrite_tags( $rewrite_tags );
		}

		$rewrite_tags = array(
			'directory' => array(
				'id'    => '%' . $this->rewrite_ids['directory'] . '%',
				'regex' => '([1]{1,})',
			),
			'directory-type' => array(
				'id'    => '%' . $this->rewrite_ids['directory_type'] . '%',
				'regex' => '([^/]+)',
			),
			'single-item' => array(
				'id'      => '%' . $this->rewrite_ids['single_item'] . '%',
				'regex'   => '([^/]+)',
			),
			'single-item-component' => array(
				'id'      => '%' . $this->rewrite_ids['single_item_component'] . '%',
				'regex'   => '([^/]+)',
			),
			'single-item-action' => array(
				'id'      => '%' . $this->rewrite_ids['single_item_action'] . '%',
				'regex'   => '([^/]+)',
			),
			'single-item-action-variables' => array(
				'id'      => '%' . $this->rewrite_ids['single_item_action_variables'] . '%',
				'regex'   => '([^/]+)',
			),
		);

		parent::add_rewrite_tags( $rewrite_tags );
	}

	/**
	 * Add the component's rewrite rules.
	 *
	 * @since 6.0.0
	 *
	 * @param array $rewrite_rules Optional. See BP_Component::add_rewrite_rules() for
	 *                             description.
	 */
	public function add_rewrite_rules( $rewrite_rules = array() ) {
		if ( ! bp_use_wp_rewrites() ) {
			return parent::add_rewrite_rules( $rewrite_rules );
		}

		$rewrite_rules = array(
			'paged-directory-type' => array(
				'regex' => $this->root_slug . '/' . bp_get_members_member_type_base() . '/([^/]+)/page/?([0-9]{1,})/?$' ,
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_type'] . '=$matches[1]&paged=$matches[2]',
			),
			'directory-type' => array(
				'regex' => $this->root_slug . '/' . bp_get_members_member_type_base() . '/([^/]+)/?$' ,
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_type'] . '=$matches[1]',
			),
			'paged-directory' => array(
				'regex' => $this->root_slug . '/page/?([0-9]{1,})/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&paged=$matches[1]',
			),
			'single-item-action-variables' => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)\/([^/]+)\/(.+?)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['single_item'] . '=$matches[1]&' . $this->rewrite_ids['single_item_component'] . '=$matches[2]&' . $this->rewrite_ids['single_item_action'] . '=$matches[3]&' . $this->rewrite_ids['single_item_action_variables'] . '=$matches[4]',
			),
			'single-item-action' => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['single_item'] . '=$matches[1]&' . $this->rewrite_ids['single_item_component'] . '=$matches[2]&' . $this->rewrite_ids['single_item_action'] . '=$matches[3]',
			),
			'single-item-component' => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['single_item'] . '=$matches[1]&' . $this->rewrite_ids['single_item_component'] . '=$matches[2]',
			),
			'single-item' => array(
				'regex' => $this->root_slug . '/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['single_item'] . '=$matches[1]',
			),
			'directory' => array(
				'regex' => $this->root_slug,
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1',
			),
		);

		parent::add_rewrite_rules( $rewrite_rules );
	}

	/**
	 * Add the component's directory permastructs.
	 *
	 * @since 6.0.0
	 *
	 * @param array $structs Optional. See BP_Component::add_permastructs() for
	 *                       description.
	 */
	public function add_permastructs( $structs = array() ) {
		if ( ! bp_use_wp_rewrites() ) {
			return parent::add_permastructs( $structs );
		}

		$permastructs = array(
			// Directory permastruct.
			$this->rewrite_ids['directory'] => array(
				'struct' => $this->directory_permastruct,
				'args'   => array(),
			),
		);

		parent::add_permastructs( $permastructs );
	}

	/**
	 * Parse the WP_Query and eventually display the component's directory or single item.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_Query $query Required. See BP_Component::parse_query() for
	 *                        description.
	 */
	public function parse_query( WP_Query $query ) {
		if ( ! bp_use_wp_rewrites() ) {
			return parent::parse_query( $query );
		}

		// Init the current member.
		$member       = false;
		$member_data = bp_rewrites_get_member_data();

		if ( isset( $member_data['object'] ) && $member_data['object'] ) {
			bp_reset_query( trailingslashit( $this->root_slug ) . $GLOBALS['wp']->request, $query );
			$member = $member_data['object'];
		}

		$is_members_component   = 1 === (int) $query->get( $this->rewrite_ids['directory'] );
		$bp                     = buddypress();

		if ( $is_members_component ) {
			$bp->current_component = 'members';
			$member_slug           = $query->get( $this->rewrite_ids['single_item'] );
			$member_type_slug      = $query->get( $this->rewrite_ids['directory_type'] );


			if ( $member_slug ) {
				// Unless root profiles are on, the member shouldn't be set yet.
				if ( ! $member ) {
					$member = get_user_by( $member_data['field'], $member_slug );
					$bp->current_component = '';

					if ( ! $member ) {
						bp_do_404();
						return;
					}
				}

				// Set the displayed user.
				$bp->displayed_user->id = $member->ID;

				/**
				 * @todo Take care of spammers
				 */

				// The core userdata of the user who is currently being displayed.
				if ( ! isset( $bp->displayed_user->userdata ) || ! $bp->displayed_user->userdata ) {
					$bp->displayed_user->userdata = bp_core_get_core_userdata( bp_displayed_user_id() );
				}

				// Fetch the full name displayed user.
				if ( ! isset( $bp->displayed_user->fullname ) || ! $bp->displayed_user->fullname ) {
					$bp->displayed_user->fullname = '';
					if ( isset( $bp->displayed_user->userdata->display_name ) ) {
						$bp->displayed_user->fullname = $bp->displayed_user->userdata->display_name;
					}
				}

				// The domain for the user currently being displayed.
				if ( ! isset( $bp->displayed_user->domain ) || ! $bp->displayed_user->domain ) {
					$bp->displayed_user->domain   = bp_core_get_user_domain( bp_displayed_user_id() );
				}

				// If A user is displayed, check if there is a front template
				if ( bp_get_displayed_user() ) {
					$bp->displayed_user->front_template = bp_displayed_user_get_front_template();
				}

				$member_component = $query->get( $this->rewrite_ids['single_item_component'] );
				if ( $member_component ) {

					// Check if the member's component slug has been customized.
					$item_component_rewrite_id = bp_rewrites_get_custom_slug_rewrite_id( 'members', $member_component );
					if ( $item_component_rewrite_id ) {
						$member_component = str_replace( 'bp_member_', '', $item_component_rewrite_id );
					}

					$bp->current_component = $member_component;
				}

				$current_action = $query->get( $this->rewrite_ids['single_item_action'] );
				if ( $current_action ) {
					$bp->current_action = $current_action;
				}

				$action_variables = $query->get( $this->rewrite_ids['single_item_action_variables'] );
				if ( $action_variables ) {
					if ( ! is_array( $action_variables ) )  {
						$bp->action_variables = explode( '/', ltrim( $action_variables, '/' ) );
					} else {
						$bp->action_variables = $action_variables;
					}
				}

				/**
				 * We can't rely on the BP Nav screen functions so far...
				 * Let's make sure the screen will be loaded.
				 */
				add_action( 'bp_screens', 'bp_members_screen_display_profile', 2 );

			// Is this a member type query ?
			} elseif ( $member_type_slug ) {
				$member_type = bp_get_member_types( array(
					'has_directory'  => true,
					'directory_slug' => $member_type_slug,
				) );

				if ( $member_type ) {
					$bp->current_member_type = reset( $member_type );
				} else {
					$bp->current_component = '';
					bp_do_404();
					return;
				}
			}

			/**
			 * This is temporary to avoid `bp_core_catch_no_access()`
			 * to generate a 404.
			 */
			$query->queried_object = get_post( $bp->pages->members->id );
		}

		parent::parse_query( $query );
	}
}
