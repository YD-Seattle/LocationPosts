<?php
	$requiredSettings = get_option( 'yd_settings' )[ 'required' ];
	$mapsApiKey = isset( $requiredSettings[ 'yd-google-maps-api-key' ] ) ? $requiredSettings[ 'yd-google-maps-api-key'] : null;
	if ( $mapsApiKey != null && $mapsApiKey != '' ) :
?>
	<p>Select a location. Update the location by dragging the marker or selecting a location from the search box.</p>
	<div id="yd-maps-container" data-api-key="<?php echo esc_attr_e( $mapsApiKey ); ?>">
		<input id="yd-map-search" class="controls" type="text" placeholder="Search Box">
		<div id="yd-map-canvas"></div>
		<input id="yd-location-lat" name="yd-location-lat" type="hidden" value="<?php echo esc_attr_e( $variables['yd-location-lat'] ); ?>" />
		<input id="yd-location-lng" name="yd-location-lng" type="hidden" value="<?php echo esc_attr_e( $variables['yd-location-lng'] ); ?>" />
		<div class="yd-warning-container"><input type="submit" name="save" id="save-post-location" value="Save Changes" class="button"></div>
	</div>
<?php else: ?>
	<p id="yd-invalid-settings">
		Warning! Before setting a location you need to provide a valid Google Maps API key (<a href="https://console.developers.google.com//flows/enableapi?apiid=maps_backend&keyType=CLIENT_SIDE&reusekey=true">Get a key here</a>).
		Once you have your API key, go to the settings for this plugin and save it in the input field labeled 'Google Maps API Key'.
	</p>
<?php endif; ?>