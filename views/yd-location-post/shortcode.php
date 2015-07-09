<div id="yd-maps-container" data-api-key="<?= esc_attr_e( $attributes['mapsApiKey'] ) ?>" data-query-type="<?= esc_attr_e( $attributes['q'] ) ?>">
	<?php
		// Attach data according to the type 
		$data = '';
		if ( isset($attributes[ 'bounds' ]) ) {
			$data = $attributes[ 'bounds' ];
		} else if ( isset($attributes[ 'post_ids' ]) ) {
			$data = $attributes[ 'post_ids' ];
		}
	?>
	<div id="yd-posts-data" data-posts-data="<?= $data ?>"></div>
	<div id="yd-map-canvas"></div>
	<div id="yd-maps-loading"></div>
</div>
