/* global bp */

window.bp = window.bp || {};

( function( bp, $, undefined ) {
	var mentionsQueryCache = [],
		mentionsItem;

	bp.mentions       = bp.mentions || {};
	bp.mentions.users = window.bp.mentions.users || [];

	if ( typeof window.BP_Suggestions === 'object' ) {
		bp.mentions.users = window.BP_Suggestions.friends || bp.mentions.users;
	}

	/**
	 * Adds BuddyPress @mentions to form inputs.
	 *
	 * @param {array} defaultList If array, becomes the suggestions' default data source.
	 * @since 2.1.0
	 */
	$.fn.bp_mentions = function( defaultList ) {
		var debouncer = function(func, wait) {
			var timeout;
			return function() {
				var context = this;
				var args = arguments;

				var callFunction = function() {
				   func.apply(context, args)
				};

				clearTimeout(timeout);
				timeout = setTimeout(callFunction, wait);
			};
		};

		var remoteSearch = function( text, cb ) {
			/**
			* Immediately show the pre-created friends list, if it's populated,
			* and the user has hesitated after hitting @ (no search text provided).
			*/
			if ( text.length === 0 && $.isArray( defaultList ) && defaultList.length > 0 ) {
				cb(defaultList);
				return;
			}

			mentionsItem = mentionsQueryCache[ text ];
			if ( typeof mentionsItem === 'object' ) {
				cb( mentionsItem );
				return;
			}

			return bp.apiRequest( {
				path: 'buddypress/v1/members/?search=' + text,
				type: 'GET'
			} ).done( function( data ) {
				var retval = $.map( data,
					/**
					 * Create a composite index to determine ordering of results;
					 * nicename matches will appear on top.
					 *
					 * @param {array} suggestion A suggestion's original data.
					 * @return {array} A suggestion's new data.
					 * @since 2.1.0
					 */
					function( suggestion ) {
						suggestion.search = suggestion.user_login + ' ' + suggestion.name;
						return suggestion;
					}
				);

				mentionsQueryCache[ text ] = retval;
				cb(retval);
			} ).fail( function( error ) {
				return error;
			} );
		};

		var tributeParams = {
			values: debouncer( function (text, cb) {
				remoteSearch(text, users => cb(users));
			}, 250),
			lookup: 'search',
			fillAttr: 'user_login',
			menuItemTemplate: function (item) {
				return '<img src="' + item.original.avatar_urls.thumb + '" alt="Profile picture of ' + item.original.name + '"> @' + item.string;
			},
		};

		var tribute = new Tribute( tributeParams );

		$( this ).each( function() {
			tribute.attach( document.getElementById( $( this ).attr( "id" ) ) );
		});
	};

	$( document ).ready( function() {
		// Activity/reply, post comments, bp-nouveau messages composer.
		$( '.bp-suggestions, #comments form textarea, .bp-messages-content .send-to-input' ).bp_mentions( bp.mentions.users );
	});

})( bp, jQuery );
