/**
 * Loads for BuddyPress Hello in wp-admin for query string `hello=buddypress`.
 *
 * @since 3.0.0
 */
(function( $, wp ) {
	/**
	 * Open the BuddyPress Hello modal.
	 */
	var bp_hello_open_modal = function() {
		if ( 'function' !== typeof window.tb_show ) {
			return false;
		}

		window.tb_show( 'BuddyPress', '#TB_inline?inlineId=bp-hello-container' );

		$( '#TB_window' ).attr( {
							'role': 'dialog',
							'aria-label': plugininstallL10n.plugin_modal_label
						} )
						.addClass( 'plugin-details-modal' );

		$("#TB_ajaxContent").prop( 'style', 'height: 100%; width: auto; padding: 0; border: none;' );
	};

	$( '#plugin-information-tabs').on( 'click', 'a', function( event ) {
		event.preventDefault();

		var anchor = $( event.currentTarget );

		if ( anchor.hasClass( 'dynamic' ) ) {
			$( '#top-features' ).hide();

			wp.ajax.send( 'bp_external_request', {
				data: {
					url: anchor.data( 'endpoint'),
					context: 'view',
					slug: 'version-4-4-0'
				}
			} ).done( function( data ) {
				console.log( data );
			} ).fail( function( error ) {
				console.log( error );
			} );
			console.log( anchor.data( 'endpoint') );
		} else {
			$( '#top-features' ).show();
		}
	} );

	// Init modal after the screen's loaded.
	$( document ).ready( function() {
		bp_hello_open_modal();
	} )
}( jQuery, window.wp || wp ) );
