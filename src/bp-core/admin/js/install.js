/* global bpInstallData */
/**
 * Bulk Install BuddyPress Plugins.
 *
 * @since 6.0.0
 */
(function( $, wp ) {

	$( document ).ready( function() {
		// Bail if we do not have the needed data.
		if ( typeof bpInstallData === 'undefined' ) {
			return;
		}

		var template = wp.template( 'buddypress-plugin' );

		// Add a plugin card for each plugin to install.
		$.each( bpInstallData.bpPlugins, function( p, plugin ) {
			$( '#the-list' ).append( template( plugin ) );

			// Trigger Install button clicks.
			$( '#the-list .install-now' ).trigger( 'click' );
		} );
	} );

	// Wait for all plugins to be installed before submitting the form.
	$( document ).on( 'wp-plugin-install-success', function( event ) {
		$( '#the-list .plugin-card' ).first().remove();

		if ( 0 === $( '#the-list .plugin-card' ).length ) {
			wp.updates.ajaxLocked = false;
			$( '#plugin-filter' ).submit();
		}
	} );

}( jQuery, window.wp || {} ) );
