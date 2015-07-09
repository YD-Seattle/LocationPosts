/**
 * Wrapper function to safely use $
 */
function ydWrapper( $ ) {
	// Fetch the api key first.
	var apiKey = $('#yd-maps-container').attr('data-api-key');
	// var storedLat = parseFloat( $('input#yd-location-lat').val() );
	// var storedLng = parseFloat( $('input#yd-location-lng').val() );

	var MapState = {
		map: undefined,
		center: undefined,
		marker: undefined,
		defaultLat: 0 || -34.397,
		defaultLng: 0 || 150.644,
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
		// setupMarker();
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

// If the maps container doesnt exist, then the user hasnt input an api key...
if ( jQuery('#yd-maps-container') )
	ydWrapper( jQuery );
