<?php
	$mapsApiKey = get_option( 'yd_settings' )[ 'required' ][ 'yd-google-maps-api-key' ];

?>

<p>Choose the location...</p>

<div id="yd-maps-container" data-api-key="<?= $mapsApiKey ?>">
	<input id="yd-map-search" class="controls" type="text" placeholder="Search Box">
	<div id="yd-map-canvas"></div>
	<input id="yd-location-lat" name="yd-location-lat" type="hidden" value="<?php echo $variables['yd-location-lat']; ?>" />
	<input id="yd-location-lng" name="yd-location-lng" type="hidden" value="<?php echo $variables['yd-location-lng']; ?>" />
	<div class="yd-warning-container"><input type="submit" name="save" id="save-post-location" value="Save Changes" class="button"></div>
</div>
