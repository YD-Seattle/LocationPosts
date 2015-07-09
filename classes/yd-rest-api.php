<?php

if ( ! class_exists( 'YD_REST_API' ) ) {

	class YD_REST_API {

		const BASE_URI = '/yd';
		const LOCATIONS_ALL_URI = '/yd/locations/all';
		const LOCATIONS_BOUNDS_URI = '/yd/locations/bounds';
		const LOCATION_SINGLE_URI = '/yd/location';

		/**
		 *	Register all of our routes.
		 */
		public function register_routes( $routes ) {
			$routes[ self::LOCATIONS_BOUNDS_URI ] = array(
				array( array( $this, 'get_yd_posts_by_bounds' ), WP_JSON_Server::READABLE ),
			);
			$routes[ self::LOCATIONS_ALL_URI ] = array(
				array( array( $this, 'get_yd_posts_all' ), WP_JSON_Server::READABLE ),
			);
			$routes[ self::LOCATION_SINGLE_URI ] = array(
				array( array( $this, 'get_yd_posts_single' ), WP_JSON_Server::READABLE ),
			);
			return $routes;
		}

		/**
		 * GET @ /wp-json/yd/locations/bounds
		 * @param  string $q   Comma seperated list representing the geographical bounds of the map. [ sw_lat, sw_lng, ne_lat, ne_lng ]
		 * @param  [type] $_headers Request headers
		 * @param  string $type     [description]
		 * @return [type]           [description]
		 */
		public function get_yd_posts_by_bounds( $q, $_headers, $type = YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG  ) {
			if ( !isset( $q ) ) {
				return new WP_Error( 'yd_rest_api_invalid_request', __( 'Invalid request parameters (missing bounds).' ), array( 'status' => 400 ) );
			}
			$bounds = explode(',', $q);
			if ( self::validate_bounds( $bounds ) == false ) {
				return new WP_Error( 'yd_rest_api_invalid_request', __( 'Invalid request parameters (bounds).' ), array( 'status' => 400 ) );
			}
			// query with bounds....
			$bounds[0] = $bounds[0];
			$bounds[1] = $bounds[1];
			$bounds[2] = $bounds[2];
			$bounds[3] = $bounds[3];
			$query = array(
				'post_type' => array( YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG ),
				'meta_query' => array(
						array(
							'key' => YD_LOCATION_CUSTOM_POST::LOCATION_LAT,
							'value' => array( $bounds[0], $bounds[2] ),
							'compare' => 'BETWEEN',
							'type' => 'SIGNED'
						),
						array(
							'key' => YD_LOCATION_CUSTOM_POST::LOCATION_LNG,
							'value' => array( $bounds[1], $bounds[3] ),
							'compare' => 'BETWEEN',
							'type' => 'SIGNED'
						)
					)
			);

			$post_query = new WP_Query();
			$posts_list = $post_query->query( $query );

			$response   = new WP_JSON_Response();
			
			$results = array( 'posts' => array() );
			foreach ( $posts_list as $post ) {
				$results[ 'posts' ][] = self::filter_post_data( $post );
			}

			$response->set_data( $results );

			return $response;
		}

		/**
		 * GET @ /wp-json/yd/locations/all
		 * @param  [type] $_headers Request headers
		 * @param  string $type     [description]
		 * @return [type]           [description]
		 */
		public function get_yd_posts_all( $_headers, $type = YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG  ) {
			$query = array(
				'post_type' => array( YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG )
			);

			$post_query = new WP_Query();
			$posts_list = $post_query->query( $query );

			$response   = new WP_JSON_Response();
			
			$results = array( 'posts' => array() );
			foreach ( $posts_list as $post ) {
				$results[ 'posts' ][] = self::filter_post_data( $post );
			}

			$response->set_data( $results );

			return $response;
		}

		/**
		 * GET @ /wp-json/yd/location
		 * @param  number $id 		The post id
		 * @param  [type] $_headers Request headers
		 * @param  string $type     [description]
		 * @return [type]           [description]
		 */
		public function get_yd_posts_single( $id, $_headers, $type = YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG  ) {
			if ( !isset( $id ) ) {
				return new WP_Error( 'yd_rest_api_invalid_request', __( 'Invalid request parameters (missing id).' ), array( 'status' => 400 ) );
			}

			$response   = new WP_JSON_Response();
			$results = array( 'post' => null );
			$post = get_post( $id );
			if ( is_object( $post ) && get_post_type( $post ) == $type ) {
				$filtered_post = self::filter_post_data( $post );
				$results[ 'post' ] = $filtered_post;
			}
			$response->set_data( $results );

			return $response;
		}



		/**
		 *	Helper to filter which data gets sent back to frontend, also attaches
		 *	the meta data.
		 *	@return array The filtered post data
		 */
		private static function filter_post_data( $post_data ) {
			$post = get_object_vars( $post_data );
			$filtered_post = array();
			$filtered_post[ 'id' ] = $post[ 'ID' ];
			$filtered_post[ 'post_title' ] = $post[ 'post_title' ];
			$filtered_post[ 'post_type' ] = $post[ 'post_type' ];
			$filtered_post[ 'post_content' ] = $post[ 'post_content' ];
			$filtered_post[ 'lat' ] = get_post_meta( $post[ 'ID' ], YD_LOCATION_CUSTOM_POST::LOCATION_LAT, true );
			$filtered_post[ 'lng' ] = get_post_meta( $post[ 'ID' ], YD_LOCATION_CUSTOM_POST::LOCATION_LNG, true );
			if ( has_post_thumbnail( $post[ 'ID' ] ) ) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post[ 'ID' ] ), 'single-post-thumbnail' )[0];
				$filtered_post[ 'img' ] = $image;
			}
			return $filtered_post;
		}

		/**
		 * Helper method to check for valid bounds. Checks that we are given 4 points [ sw_lat, sw_lng, ne_lat, ne_lng ].
		 * Also checks that these values are valid lat,lng:
		 *	 Lat range: [-90, 90].
		 *	 Lng range: [-180, 180].
		 */
		protected static function validate_bounds( $bounds ) {
			if ( count( $bounds ) != 4 )
				return false;
			if ( doubleval($bounds[0]) < -90 || doubleval($bounds[0]) > 90 )
				return false;
			if ( doubleval($bounds[2]) < -90 || doubleval($bounds[2]) > 90 )
				return false;
			if ( doubleval($bounds[1]) < -180 || doubleval($bounds[1]) > 180 )
				return false;
			if ( doubleval($bounds[3]) < -180 || doubleval($bounds[3]) > 180 )
				return false;
			return true;  // TODO: check that sw is sw wrt ne
		}
	}

}

?>