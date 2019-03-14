<?php
/**
 * BuddyPress Activity Theme Compatibility.
 *
 * @package BuddyPress
 * @since 1.7.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The main theme compat class for BuddyPress Activity.
 *
 * This class sets up the necessary theme compatibility actions to safely output
 * activity template parts to the_title and the_content areas of a theme.
 *
 * @since 1.7.0
 */
class BP_Activity_Theme_Compat {
	/**
	 * @var WP_Post
	 *
	 * @since 5.0.0
	 */
	private $post;

	/**
	 * Set up the activity component theme compatibility.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		add_action( 'bp_setup_theme_compat', array( $this, 'is_activity' ) );
	}

	/**
	 * Set up the theme compatibility hooks, if we're looking at an activity page.
	 *
	 * @since 1.7.0
	 */
	public function is_activity() {

		// Bail if not looking at a group.
		if ( ! bp_is_activity_component() )
			return;

		// Activity Directory.
		if ( ! bp_displayed_user_id() && ! bp_current_action() ) {
			bp_update_is_directory( true, 'activity' );

			/** This action is documented in bp-activity/bp-activity-screens.php */
			do_action( 'bp_activity_screen_index' );

			add_filter( 'bp_get_buddypress_template',                array( $this, 'directory_template_hierarchy' ) );
			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			add_filter( 'bp_replace_the_content',                    array( $this, 'directory_content'    ) );

		// Single activity.
		} elseif ( bp_is_single_activity() ) {
			add_filter( 'bp_get_buddypress_template',                array( $this, 'single_template_hierarchy' ) );
			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'single_dummy_post' ) );
			add_filter( 'bp_replace_the_content',                    array( $this, 'single_dummy_content'    ) );
		}
	}

	/** Directory *************************************************************/

	/**
	 * Add template hierarchy to theme compat for the activity directory page.
	 *
	 * This is to mirror how WordPress has {@link https://codex.wordpress.org/Template_Hierarchy template hierarchy}.
	 *
	 * @since 1.8.0
	 *
	 * @param string $templates The templates from bp_get_theme_compat_templates().
	 * @return array $templates Array of custom templates to look for.
	 */
	public function directory_template_hierarchy( $templates ) {

		/**
		 * Filters the template hierarchy for the activity directory page.
		 *
		 * @since 1.8.0
		 *
		 * @param array $index-directory Array holding template names to be merged into template list.
		 */
		$new_templates = apply_filters( 'bp_template_hierarchy_activity_directory', array(
			'activity/index-directory.php'
		) );

		// Merge new templates with existing stack
		// @see bp_get_theme_compat_templates().
		$templates = array_merge( (array) $new_templates, $templates );

		return $templates;
	}

	/**
	 * Set Activity's page thumbnail.
	 *
	 * @since 5.0.0
	 *
	 * @param  string       $html              The post thumbnail HTML.
	 * @param  int          $post_id           The post ID.
	 * @param  string       $post_thumbnail_id The post thumbnail ID.
	 * @param  string|array $size              The post thumbnail size. Image size or array of width and height
	 *                                         values (in that order). Default 'post-thumbnail'.
	 * @param  string       $attr              Query string of attributes.
	 * @return string                          The post thumbnail image tag.
	 */
	function set_page_thumbnail( $html = '', $post_ID = 0, $post_thumbnail_id = 0, $size = '', $attr = '' ) {
		// Prevent infinite loops.
		remove_filter( 'post_thumbnail_html', array( $this, 'set_page_thumbnail' ) );

		// Restore the Activity page thumbnail.
		return get_the_post_thumbnail( $this->post, $size, $attr );
	}

	/**
	 * Set the Activity's page attributes.
	 *
	 * @since 5.0.0
	 */
	function set_page_attributes() {
		$bp             = buddypress();
		$this->post     = get_post();
		$this->post->ID = (int) $bp->pages->activity->id;

		if ( has_post_thumbnail( $this->post ) ) {
			add_filter( 'has_post_thumbnail', '__return_true' );
			add_filter( 'post_thumbnail_html', array( $this, 'set_page_thumbnail' ), 10, 5 );
		}

		if ( function_exists( 'has_block' ) && has_block( 'image', $this->post ) )  {
			$blocks         = parse_blocks( $this->post->post_content );
			$block          = reset( $blocks );

			// Set Activity's page attributes.
			$bp->activity->page_attributes = wp_parse_args( $block['attrs'],
				array(
					'align' => '',
				)
			);
		}
	}

	/**
	 * Update the global $post with directory data.
	 *
	 * @since 1.7.0
	 */
	public function directory_dummy_post() {
		$this->set_page_attributes();

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => bp_get_directory_title( 'activity' ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with the groups index template part.
	 *
	 * @since 1.7.0
	 */
	public function directory_content() {
		return bp_buffer_template_part( 'activity/index', null, false );
	}

	/** Single ****************************************************************/

	/**
	 * Add custom template hierarchy to theme compat for activity permalink pages.
	 *
	 * This is to mirror how WordPress has {@link https://codex.wordpress.org/Template_Hierarchy template hierarchy}.
	 *
	 * @since 1.8.0
	 *
	 * @param string $templates The templates from bp_get_theme_compat_templates().
	 * @return array $templates Array of custom templates to look for.
	 */
	public function single_template_hierarchy( $templates ) {

		/**
		 * Filters the template hierarchy for the activity permalink pages.
		 *
		 * @since 1.8.0
		 *
		 * @param array $index Array holding template names to be merged into template list.
		 */
		$new_templates = apply_filters( 'bp_template_hierarchy_activity_single_item', array(
			'activity/single/index.php'
		) );

		// Merge new templates with existing stack
		// @see bp_get_theme_compat_templates().
		$templates = array_merge( (array) $new_templates, $templates );

		return $templates;
	}

	/**
	 * Update the global $post with the displayed user's data.
	 *
	 * @since 1.7.0
	 */
	public function single_dummy_post() {
		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => __( 'Activity', 'buddypress' ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Filter the_content with the members' activity permalink template part.
	 *
	 * @since 1.7.0
	 */
	public function single_dummy_content() {
		return bp_buffer_template_part( 'activity/single/home', null, false );
	}
}
