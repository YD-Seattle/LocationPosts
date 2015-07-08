/**
 * Wrapper function to safely use $
 */
function ydWrapper( $ ) {
	// Fetch the api key first.
	var apiKey = $('#yd-maps-container').attr('data-api-key');
	var storedLat = parseFloat( $('input#yd-location-lat').val() );
	var storedLng = parseFloat( $('input#yd-location-lng').val() );

	var MapState = {
		map: undefined,
		center: undefined,
		marker: undefined,
		search: undefined,
		defaultLat: storedLat || -34.397,
		defaultLng: storedLng || 150.644,
	};
	console.log( MapState );

	// Setup the map with stored lat lng values
	function mapsInitialize() {
		var mapOptions = {
			zoom: 8,
			center: new google.maps.LatLng( MapState.defaultLat, MapState.defaultLng ),
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
		MapState.center = mapOptions.center;
		MapState.map = new google.maps.Map( document.getElementById( 'yd-map-canvas' ), mapOptions );
		setupMarker();
		setupSearchBox();
	}

	// Setup the draggable marker
	function setupMarker() {
		MapState.marker = new google.maps.Marker({
			position: MapState.center,
			draggable: true,
			map: MapState.map,
			title: $('input[name=post_title]').val()
		});
		// Attach the event handler for the marker
		google.maps.event.addListener( MapState.marker, 'dragend', function( event ) {
			updateLatLngInput( event.latLng.A, event.latLng.F );
		});
	}

	function setupSearchBox() {
		// Create the search box and link it to the UI element.
		var input = document.getElementById('yd-map-search');
		// Prevent form submition on maps search
		$(input).on('keypress keyup', function(e) {
			var code = e.keyCode || e.which;
			if (code == 13) { 
				e.preventDefault();
				return false;
			}
		}).show();
		MapState.map.controls[google.maps.ControlPosition.TOP_RIGHT].push( input );
		MapState.searchBox = new google.maps.places.SearchBox( input );
		// Listen for the event fired when the user selects an item from the
		// pick list. Retrieve the matching places for that item.
		google.maps.event.addListener( MapState.searchBox, 'places_changed', function() {
			var places = MapState.searchBox.getPlaces();
			console.log( places );
			if (places.length == 0) {
				return;
			}
			var location = places[0].geometry.location;
			var newLatLng = new google.maps.LatLng( location.A, location.F );
			// Update the marker position
			MapState.marker.setPosition( newLatLng );
			// Update the map position
			MapState.map.panTo( newLatLng );
			// Update the inputs now that marker has moved
			updateLatLngInput( location.A, location.F );
		});
	}

	// Update the inputs for lat lng
	function updateLatLngInput( lat, lng ) {
		$('input#yd-location-lat').val( lat );
		$('input#yd-location-lng').val( lng );
		$('.yd-warning-container').slideDown();
	}







	// Had to attach to window for callback =(
	window.mapsInitialize = mapsInitialize;

	function loadScript() {
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&callback=mapsInitialize&libraries=places';
		document.body.appendChild(script);
	}

	window.onload = loadScript;

}

ydWrapper( jQuery );
