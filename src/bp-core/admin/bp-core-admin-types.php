<?php
/**
 * BuddyPress Types Administration screen.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 7.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert a new type into the database.
 *
 * @since 7.0.0
 *
 * @param array  $args {
 *     Array of arguments describing the object type.
 *
 *     @type string $taxonomy   The Type's taxonomy.
 *     @type string $bp_type_id Unique string identifier for the member type.
 *     @see keys of the array returned by bp_get_type_metadata_schema() for the other arguments.
 * }
 */
function bp_core_admin_insert_type( $args = array() ) {
	$default_args = array(
		'taxonomy'   => '',
		'bp_type_id' => '',
	);

	$args = array_map( 'wp_unslash', $args );
	$args = bp_parse_args(
		$args,
		$default_args,
		'admin_insert_type'
	);

	if ( ! $args['bp_type_id'] || ! $args['taxonomy'] ) {
		 return new WP_Error(
			 'invalid_type_taxonomy',
			 __( 'The Type ID value is missing', 'buddypress' ),
			 array(
				'message' => 1,
			 )
		);
	}

	$type_id       = sanitize_title( $args['bp_type_id'] );
	$type_taxonomy = sanitize_key( $args['taxonomy'] );

	/**
	 * Filter here to check for an already existing type.
	 *
	 * @since 7.0.0
	 *
	 * @param boolean $value   True if the type exists. False otherwise.
	 * @param string  $type_id The Type's ID.
	 */
	$type_exists = apply_filters( "{$type_taxonomy}_check_existing_type", false, $type_id );

	if ( false !== $type_exists ) {
		return new WP_Error(
			'type_already_exists',
			__( 'The Type already exists', 'buddypress' ),
			array(
			   'message' => 5,
			)
	   );
	}

	$metadata_schema = bp_get_type_metadata_schema( false, $type_taxonomy );
	$metadata        = wp_list_pluck( $metadata_schema, 'type' );

	// Set default values according to their schema type.
	foreach ( $metadata as $meta_key => $meta_value ) {
		if ( in_array( $meta_value, array( 'boolean', 'integer' ), true ) ) {
			$metadata[ $meta_key ] = 0;
		} else {
			$metadata[ $meta_key ] = '';
		}
	}

	// Validate metadata
	$metas = array_filter( array_intersect_key( $args, $metadata ) );

	// Insert the Type into the database.
	$type_term_id = bp_insert_term(
		$type_id,
		$type_taxonomy,
		array(
			'slug'  => $type_id,
			'metas' => $metas,
		)
	);

	if ( is_wp_error( $type_term_id ) ) {
		$type_term_id->add_data(
			array(
				'message' => 3,
			)
		);

		return $type_term_id;
	}

	/**
	 * Hook here to add code once the type has been inserted.
	 *
	 * @since 7.0.0
	 *
	 * @param integer $type_term_id The Type's term_ID.
	 * @param string  $taxonomy     The Type's taxonomy name.
	 * @param string  $type_id      The Type's ID.
	 */
	do_action( 'bp_type_inserted', $type_term_id, $taxonomy, $type_id );

	// Finally return the inserted Type's term ID.
	return $type_term_id;
}
