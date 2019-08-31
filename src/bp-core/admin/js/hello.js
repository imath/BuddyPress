/**
 * Loads for BuddyPress Hello in wp-admin for query string `hello=buddypress`.
 *
 * @since 3.0.0
 */
(function( $, bp ) {
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
						.addClass( 'plugin-details-modal' )
						.removeClass( 'thickbox-loading' );

		$("#TB_ajaxContent").prop( 'style', 'height: 100%; width: auto; padding: 0; border: none;' );
	};

	$( '#plugin-information-tabs').on( 'click', 'a', function( event ) {
		event.preventDefault();

		var anchor = $( event.currentTarget ), target = $( '#dynamic-content' );

		if ( anchor.hasClass( 'dynamic' ) ) {
			$( '#top-features' ).hide();
			target.html( '' );
			target.addClass( 'show' );

			$( '#TB_window' ).addClass( 'thickbox-loading' );

			bp.apiRequest( {
				url: anchor.data( 'endpoint' ),
				type: 'GET',
				beforeSend: function( xhr, settings ) {
					settings.url = settings.url.replace( '&_wpnonce=none', '' );
				},
				data: {
					context: 'view',
					slug: anchor.data( 'slug' ),
					_wpnonce: 'none',
				}
			} ).done( function( data ) {
				var page = _.first( data );
				target.html( page.content.rendered );

				$( '#TB_window' ).removeClass( 'thickbox-loading' );
			} ).fail( function( error ) {
				console.log( error );
			} );
		} else {
			$( '#top-features' ).show();
			target.html( '' );
			target.removeClass( 'show' );
		}
	} );

	// Init modal after the screen's loaded.
	$( document ).ready( function() {
		bp_hello_open_modal();
	} )
}( jQuery, window.bp || {} ) );
