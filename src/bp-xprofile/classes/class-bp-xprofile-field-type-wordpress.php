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
 * Base class for xprofile field types that set/get WordPress profile data from usermeta.
 *
 * @since 8.0.0
 */
abstract class BP_XProfile_Field_Type_WordPress extends BP_XProfile_Field_Type {

	/**
	 * The usermeta key for the WordPress field.
	 *
	 * @since 8.0.0
	 * @var string The meta key name of this WordPress field.
	 */
	public $meta_key = '';

	/**
	 * Constructor for the URL field type
	 *
	 * @since 8.0.0
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * Fires inside __construct() method for BP_XProfile_Field_Type_WordPress class.
		 *
		 * @since 8.0.0
		 *
		 * @param BP_XProfile_Field_Type_URL $this Instance of the field type object.
		 */
		do_action( 'bp_xprofile_field_type_wordpress', $this );

		// Use the `$wpdb->usermeta` table instead of the $bp->profile->table_name_data one.
		add_filter( 'bp_xprofile_set_field_data_pre_save', array( $this, 'set_field_value' ), 10, 2 );
	}

	/**
	 * Sets the WordPress field value.
	 *
	 * @since 8.0.0
	 *
	 * @param boolean $retval Whether to shortcircuit the $bp->profile->table_name_data table.
	 *                        Default `false`.
	 * @param array $field_args {
	 *     An array of arguments.
	 *
	 *     @type object            $field_type_obj Field type object.
	 *     @type BP_XProfile_Field $field          Field object.
	 *     @type integer           $user_id        The user ID.
	 *     @type mixed             $value          Value passed to xprofile_set_field_data().
	 *     @type boolean           $is_required    Whether or not the field is required.
	 * }
	 * @return boolean Whether to shortcircuit the $bp->profile->table_name_data table.
	 */
	public function set_field_value( $retval = false, $field_args = array() ) {
		/**
		 * Check for additional keys
		 * @see _get_additional_user_keys
		 * We should use `register_meta()` instead.
		 */
		if ( ! isset( $field_args['field_type_obj']->meta_key ) || $this->meta_key !== $field_args['field_type_obj']->meta_key ) {
			return false;
		}

		$retval = wp_update_user(
				array(
				'ID'            => (int) $field_args['user_id'],
				$this->meta_key => $field_args['value'],
			)
		);

		if ( ! is_wp_error( $retval ) ) {
			$retval = true;
		}

		return $retval;
	}

	public function get_field_value( $user_id ) {
		global $wpdb;
		$meta = array(
			'id'         => 0,
			'value'      => '',
			'table_name' => $wpdb->usermeta,
		);

		// Let's get the meta_id for the meta_key.
		$meta['value'] = get_user_meta( $user_id, $this->meta_key, true );
		if ( $meta['value'] ) {
			$meta['id'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT umeta_id FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s ORDER BY umeta_id ASC", $user_id, $this->meta_key ) );
		}

		return $meta;
	}
}
