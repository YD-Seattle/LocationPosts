<?php
/*
 * Basic Section
 */
?>

<?php if ( 'yd-google-maps-api-key' == $field['label_for'] ) : ?>
	<?php
		$mapsApiKey = isset( $settings['required']['yd-google-maps-api-key'] ) ? $settings['required']['yd-google-maps-api-key'] : '';
	?>

	<input id="<?php esc_attr_e( 'yd_settings[required][yd-google-maps-api-key]' ); ?>" name="<?php esc_attr_e( 'yd_settings[required][yd-google-maps-api-key]' ); ?>" class="regular-text" value="<?php esc_attr_e( $mapsApiKey ); ?>" />
	<span class="example"> <a href="https://console.developers.google.com//flows/enableapi?apiid=maps_backend&keyType=CLIENT_SIDE&reusekey=true">Need Help?</a> </span>

<?php endif; ?>
