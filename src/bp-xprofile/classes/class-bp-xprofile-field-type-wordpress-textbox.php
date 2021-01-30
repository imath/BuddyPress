<?php
/**
 * BuddyPress XProfile Classes.
 *
 * @package BuddyPress
 * @subpackage XProfileClasses
 * @since 8.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WordPress xProfile regular field type.
 *
 * @since 8.0.0
 */
class BP_XProfile_Field_Type_WordPress_Textbox extends BP_XProfile_Field_Type_WordPress {

	/**
	 * Constructor for the WordPress regular field type.
	 *
	 * @since 8.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->category            = _x( 'WordPress Fields', 'xprofile field type category', 'buddypress' );
		$this->name                = _x( 'Text field', 'xprofile field type', 'buddypress' );
		$this->accepts_null_value  = true;
		$this->do_settings_section = true;

		$this->set_format( '/^.*$/', 'replace' );

		/**
		 * Fires inside __construct() method for BP_XProfile_Field_Type_WordPress_Textbox class.
		 *
		 * @since 8.0.0
		 *
		 * @param BP_XProfile_Field_Type_WordPress_Textbox $this Instance of the field type object.
		 */
		do_action( 'bp_xprofile_field_type_wordpress_textbox', $this );
	}

	/**
	 * Output the edit field HTML for this field type.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 8.0.0
	 *
	 * @param array $raw_properties Optional key/value array of
	 *                              {@link http://dev.w3.org/html5/markup/textarea.html permitted attributes}
	 *                              that you want to add.
	 */
	public function edit_field_html( array $raw_properties = array() ) {
		/*
		 * User_id is a special optional parameter that certain other fields
		 * types pass to {@link bp_the_profile_field_options()}.
		 */
		if ( ! is_admin() && isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$r = wp_parse_args( $raw_properties, array(
			'type'  => 'text',
			'value' => get_user_meta( $user_id, $this->meta_key, true ),
		) );

		$user_id = bp_displayed_user_id();
		if ( isset( $r['user_id'] ) && $r['user_id'] ) {
			$user_id = (int) $r['user_id'];
			unset( $r['user_id'] );
		}
		?>

		<legend id="<?php bp_the_profile_field_input_name(); ?>-1">
			<?php bp_the_profile_field_name(); ?>
			<?php bp_the_profile_field_required_label(); ?>
		</legend>

		<?php

		/** This action is documented in bp-xprofile/bp-xprofile-classes */
		do_action( bp_get_the_profile_field_errors_action() ); ?>

		<input <?php echo $this->get_edit_field_html_elements( $r ); ?> aria-labelledby="<?php bp_the_profile_field_input_name(); ?>-1" aria-describedby="<?php bp_the_profile_field_input_name(); ?>-3">

		<?php if ( bp_get_the_profile_field_description() ) : ?>
			<p class="description" id="<?php bp_the_profile_field_input_name(); ?>-3"><?php bp_the_profile_field_description(); ?></p>
		<?php endif; ?>

		<?php
	}

	/**
	 * Output HTML for this field type on the wp-admin Profile Fields screen.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 8.0.0
	 *
	 * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
	 */
	public function admin_field_html( array $raw_properties = array() ) {
		$r = wp_parse_args( $raw_properties, array(
			'type' => 'text'
		) ); ?>

		<label for="<?php bp_the_profile_field_input_name(); ?>" class="screen-reader-text"><?php
			/* translators: accessibility text */
			esc_html_e( 'WordPress field', 'buddypress' );
		?></label>
		<input <?php echo $this->get_edit_field_html_elements( $r ); ?>>

		<?php
	}

	/**
	 * This method usually outputs HTML for this field type's children options on the wp-admin Profile Fields
	 * "Add Field" and "Edit Field" screens, but for this field type, we don't want it, so it's stubbed out.
	 *
	 * @since 8.0.0
	 *
	 * @param BP_XProfile_Field $current_field The current profile field on the add/edit screen.
	 * @param string            $control_type  Optional. HTML input type used to render the
	 *                                         current field's child options.
	 */
	public function admin_new_field_html( BP_XProfile_Field $current_field, $control_type = '' ) {
		$type = array_search( get_class( $this ), bp_xprofile_get_field_types() );

		if ( false === $type ) {
			return;
		}

		$style = 'margin-top: 15px;';
		if ( $current_field->type !== $type ) {
			$style .= ' display: none;';
		};

		//$settings = self::get_field_settings( $current_field->id );

		$wp_labels = array_merge(
			array(
				'first_name' => _x( 'First Name', 'xpofile wp-textbox field type label', 'buddypress' ),
				'last_name'  => _x( 'Last Name', 'xpofile wp-textbox field type label', 'buddypress' ),
				'nickname'   => _x( 'Nickname', 'xpofile wp-textbox field type label', 'buddypress' ),
			),
			wp_get_user_contact_methods()
		);
		?>
		<div id="<?php echo esc_attr( $type ); ?>" class="postbox bp-options-box" style="<?php echo esc_attr( $style ); ?>">
			<h3><?php esc_html_e( 'Select the User information you want to use for your field.', 'buddypress' ); ?></h3>

			<div class="inside" aria-live="polite" aria-atomic="true" aria-relevant="all">
				<div class="bp-option">
					<ul>
						<?php
						foreach ( $this->supported_keys as $key ) {
							if ( 'description' === $key || ! isset( $wp_labels[ $key ] ) ) {
								continue;
							}

							printf(
								'<li><label for="wp-textbox-meta-key-%1$s">
									<input type="radio" id="wp-textbox-meta-key-%1$s" name="wp-textbox-meta-key" value="%1$s" />
									%2$s
								</label></li>',
								esc_attr( $key ),
								esc_html( $wp_labels[ $key ] )
							);
						}
						?>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}
}
