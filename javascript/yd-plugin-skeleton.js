/**
 * Wrapper function to safely use $
 */
function ydWrapper( $ ) {
	var yd = {

		/**
		 * Main entry point
		 */
		init: function () {
			yd.prefix      = 'yd_';
			yd.templateURL = $( '#template-url' ).val();
			yd.ajaxPostURL = $( '#ajax-post-url' ).val();

			yd.registerEventHandlers();
		},

		/**
		 * Registers event handlers
		 */
		registerEventHandlers: function () {
			$( '#example-container' ).children( 'a' ).click( yd.exampleHandler );
		},

		/**
		 * Example event handler
		 *
		 * @param object event
		 */
		exampleHandler: function ( event ) {
			alert( $( this ).attr( 'href' ) );

			event.preventDefault();
		}
	}; // end yd

	$( document ).ready( yd.init );

} // end ydWrapper()

ydWrapper( jQuery );
