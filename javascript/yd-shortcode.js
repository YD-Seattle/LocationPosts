/**
 * Wrap our js to avoid conflicts and use $
 */
function ydWrapper( $, YDLocationPosts ) {
	// What type of location map are we generating?
	var type = $('#yd-maps-container').attr('data-type');

	// Setup the map with stored lat lng values
	var mapOptions = {
		zoom: 8,
		center: new google.maps.LatLng( 47.6, -122.3 ),  // Welcome to Seattle
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.LARGE,
			position: google.maps.ControlPosition.RIGHT_BOTTOM
		},
		panControl: false,
		mapTypeControl: false,
		scaleControl: false,
		streetViewControl: false,
		overviewMapControl: false,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map( document.getElementById( 'yd-map-canvas' ), mapOptions );
	var markers = [];

	// Now that the map is setup, lets start our app
	var ydLocationPosts = new YDLocationPosts( type );
	// Fetch the initial data
	ydLocationPosts.fetchPosts();
	// Setup callbacks
	ydLocationPosts.on( 'single', function( post ) {
		// Since there is only one post we just center the map on that marker.
		var marker = generateMarker( post );
		map.setCenter( marker.getPosition() );
	});
	ydLocationPosts.on( 'all', function( posts ) {
		var bounds = new google.maps.LatLngBounds();
		posts.forEach( function( post ) {
			var marker = generateMarker( post );
			bounds.extend( marker.getPosition() );
		});
		map.fitBounds( bounds );
	});



	// Setup the draggable marker
	function generateMarker( post ) {
		var newMarker = new google.maps.Marker({
			position: new google.maps.LatLng( post.lat, post.lng ),
			map: map,
			title: post.title
		});
		// Attach the event handler for the marker
		google.maps.event.addListener( newMarker, 'click', function( event ) {
			// TODO: modal time
			alert('Dont touch that.');
		});
		return newMarker;
	}


	// Load the maps library and provide our callback ;)
	
}

// If the maps container doesnt exist, then the user hasnt input an api key...
window.onload = function() {
	var $ = jQuery;
	if ( $('#yd-maps-container') ) {
		var apiKey = $('#yd-maps-container').attr('data-api-key');

		/**
		 *	This class handles all the interaction between google maps and our server.
		 *	@param type   The type of shortcode we are implementing (as of now this may
		 *				  be one of the following: single, all, bounds).
		 */
		function YDLocationPosts( type ) {
			this.type = type; // single, all, bounds
			if ( this.type == 'bounds' )
				this.bounds = $('#yd-posts-data').attr('data-posts-data');
			if ( this.type == 'single' )
				this.post_id = $('#yd-posts-data').attr('data-posts-data');
			this.rest = {};
			this.rest.all = '/wp-json/yd/locations/all';
			this.rest.bounds = '/wp-json/yd/locations/bounds';
			this.rest.single = '/wp-json/yd/location';
		}

		/**
		 *	Update the bounds of YDLocationPosts.
		 *	@param google.maps.LatLngBounds newBounds
		 */
		YDLocationPosts.prototype.boundsChanged = function( newBounds ) {
			var ne = newBounds.getNorthEast();
			var sw = newBounds.getSouthWest();
			this.bounds = sw.lat() + ',' + sw.lng() + ',' + ne.lat() + ',' + ne.lng();
		};

		/**
		 *	Fetch the LocationPost(s) based on the internal representation of this object.
		 *	In other words, fetch posts based on the type (ex: single, all, bounds) and parameters. 
		 */
		YDLocationPosts.prototype.fetchPosts = function() {
			// 1. type == single, we only need to fetch one post
			var self = this;
			if ( this.type == 'single' ) {
				$.get( this.rest.single, { id: this.post_id }, function( data ) {
					console.log( data );
					if ( data && data.post ) {
						if ( self.singleCallback ) self.singleCallback( data.post );
						$('#yd-maps-loading').fadeOut();
					} else {
						console.log( 'No post with id [%s] found.', self.post_id );
					}
				});
			} else if ( this.type == 'all' ) {
				$.get( this.rest.all, function( data ) {
					console.log( data );
					if ( data && data.posts ) {
						if ( self.allCallback ) self.allCallback( data.posts );
						$('#yd-maps-loading').fadeOut();
					} else {
						console.log( 'No posts found.' );
					}
				});
			} else if ( this.type == 'bounds' ) {
				var self = this;
				$.get( this.rest.bounds, { bounds: this.bounds }, function( data ) {
					console.log( data );
					if ( data && data.posts ) {
						if ( self.boundsCallback ) self.boundsCallback( data.posts );
						$('#yd-maps-loading').fadeOut();
					} else {
						console.log( 'No posts within bounds [%s].', self.bounds );
					}
				});
			}
		};

		YDLocationPosts.prototype.on = function( method, callback ) {
			switch( method ) {
				case 'single':
					this.singleCallback = callback; break;
				case 'all':
					this.allCallback = callback; break;
				case 'bounds':
					this.boundsCallback = callback; break;
				default:
					break;
			}
		};


		function ydInit() {
			ydWrapper( jQuery, YDLocationPosts );
		}

		window.ydInit = ydInit;

		(function loadScript() {
			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&callback=ydInit';
			document.body.appendChild(script);
		})();
	}
}
