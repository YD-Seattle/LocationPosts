<div id="yd-maps-container" data-api-key="<?php echo esc_attr_e( $attributes['mapsApiKey'] ); ?>" data-query-type="<?php echo esc_attr_e( $attributes['q'] ); ?>">
	<?php
		// Attach data according to the type 
		$data = '';
		if ( isset($attributes[ 'bounds' ]) ) {
			$data = $attributes[ 'bounds' ];
		} else if ( isset($attributes[ 'post_ids' ]) ) {
			$data = $attributes[ 'post_ids' ];
		}
	?>
	<div id="yd-posts-data" data-posts-data="<?php echo esc_attr_e( $data ); ?>"></div>
	<div id="yd-map-canvas"></div>
	<div id="yd-maps-loading"></div>
</div>
<div class="modal fade" id="yd-modal" tabindex="-1" role="dialog">
	<div class="yd-modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<!-- This is where the goods go =) -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
