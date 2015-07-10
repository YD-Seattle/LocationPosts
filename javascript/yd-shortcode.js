/**
 * Wrap our js to avoid conflicts and use $
 */
function ydWrapper( $, YDLocationPosts ) {
	// What type of location map are we generating?
	var type = $('#yd-maps-container').attr('data-query-type');
	// Compile the handlebars template once
	var source   = $("#yd-location-post-template").html();
	var template = Handlebars.compile(source);

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
	ydLocationPosts.on( 'data', function( posts ) {
		var bounds = new google.maps.LatLngBounds();
		posts.forEach( function( post ) {
			var marker = generateMarker( post );
			markers.push( marker );
			bounds.extend( marker.getPosition() );
		});
		if ( posts.length === 1 )
			map.setCenter( markers[0].getPosition() );
		else
			map.fitBounds( bounds );
	});

	function generateMarker( post ) {
		var newMarker = new google.maps.Marker({
			position: new google.maps.LatLng( post.lat, post.lng ),
			map: map,
			title: post.title,
			post_data: post
		});
		// Attach the event handler for the marker
		google.maps.event.addListener( newMarker, 'click', function() {
			// TODO: modal time
			$('#yd-maps-container').append( template(this.post_data) );
		});
		return newMarker;
	}
	
}

// If the maps container doesnt exist, then the user hasnt input an api key...
window.onload = function() {
	var $ = jQuery;
	if ( $('#yd-maps-container') ) {
		var apiKey = $('#yd-maps-container').attr('data-api-key');

		/**
		 *	This class handles all the interaction between google maps and our server.
		 *	@param query_type   The type of query we are implementing (as of now this may
		 *				  		be one of the following: post_ids, all, bounds).
		 */
		function YDLocationPosts( query_type ) {
			this.query_type = query_type;
			if ( this.query_type == 'bounds' )
				this.bounds = $('#yd-posts-data').attr('data-posts-data');
			if ( this.query_type == 'post_ids' )
				this.post_ids = $('#yd-posts-data').attr('data-posts-data');
			this.rest_endpoint = '/wp-json/yd/locations';
			this.posts = [];  // keep track of which posts we are representing...
		}

		/**
		 *	Fetch the LocationPost(s) based on the internal representation of this object.
		 *	In other words, fetch posts based on the type (ex: single, all, bounds) and parameters. 
		 */
		YDLocationPosts.prototype.fetchPosts = function() {
			// 1. type == single, we only need to fetch one post
			var self = this;
			var params = {
				q: this.query_type,
				bounds: this.bounds,
				post_ids: this.post_ids
			};

			$.get( this.rest_endpoint, params, function( data ) {
				console.log( data );
				if ( data && data.posts ) {
					if ( self.dataCallback ) self.dataCallback( data.posts );
					$('#yd-maps-loading').fadeOut();
				} else {
					console.log( 'No posts were found for query:', params );
				}
			});
		};

		YDLocationPosts.prototype.on = function( method, callback ) {
			switch( method ) {
				case 'data':
					this.dataCallback = callback; break;
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
