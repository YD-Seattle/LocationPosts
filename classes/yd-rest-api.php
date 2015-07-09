<?php

if ( ! class_exists( 'YD_REST_API' ) ) {

	class YD_REST_API {

		const RESOURCE_URI = '/yd/locations';

		/**
		 *	Register all of our routes.
		 */
		public function register_routes( $routes ) {
			$routes[ self::RESOURCE_URI ] = array(
				array( array( $this, 'get_yd_location_posts' ), WP_JSON_Server::READABLE ),
			);

			return $routes;
		}

		/**
		 * GET @ /wp-json/yd/locations
		 * This is the function for handling get requets for our custom post type (Location Posts).
		 *
		 * @param  required $q 			The type of query (currently available: 'all', 'ids', 'bounds' )
		 * @param  optional $bounds   	Comma seperated list representing the geographical bounds of the map. [ sw_lat, sw_lng, ne_lat, ne_lng ]
		 * @param  optional $post_ids   Comma seperated list of post_ids
		 * @param  optional $location_taxonomies  Comma seperated list of custom taxonomy slugs
		 * @param  required $_headers 	Request headers
		 */
		public function get_yd_location_posts( 
										$q, 
										$bounds = null, 
										$post_ids = null, 
										$location_taxonomies = null,
										$_headers, $type = YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG  ) {
			if ( !isset( $q ) ) {
				$q = 'all';
			}

			$query = array();
			// Compose the query based on the q param.
			switch ( $q ) {
				case 'post_ids':
					if ( !isset( $post_ids ) ) {
						return new WP_Error( 'yd_rest_api_invalid_request', __( 'Invalid request parameters (missing parameter `post_ids` for type=\'post_ids\').' ), array( 'status' => 400 ) );	
					}
					$post_ids = array_map('trim', explode(',', $post_ids) );
					$query = array(
						'post_type' => array( YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG ),
						'post__in' => $post_ids  // NOTE: if $ids is an empty array, post__in will return all posts
					);
					break;
				case 'bounds':
					if ( !isset( $bounds ) ) {
						return new WP_Error( 'yd_rest_api_invalid_request', __( 'Invalid request parameters (missing parameter `bounds` for type=\'bounds\').' ), array( 'status' => 400 ) );	
					}
					$bounds = array_map('trim', explode(',', $bounds) );
					if ( self::validate_bounds( $bounds ) == false ) {
						return new WP_Error( 'yd_rest_api_invalid_request', __( 'Invalid request parameters (bounds).' ), array( 'status' => 400 ) );
					}
					// query with bounds....
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
					break;
				default:
					// Default to 'all'
					$query = array(
						'post_type' => array( YD_LOCATION_CUSTOM_POST::POST_TYPE_SLUG )
					);
					break;
			}

			if ( isset( $location_taxonomies ) ) {
				// Need to add the taxonmy to the query
				$taxonomies = array_map('trim', explode(',', $location_taxonomies) );
				$query[ 'tax_query' ] = array(
					array(
						'taxonomy' => YD_LOCATION_CUSTOM_POST::TAG_SLUG,
						'field'    => 'slug',
						'terms'    => $taxonomies,
					)
				);
			}
			
			
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
		 *	Helper to filter which data gets sent back to frontend, also attaches
		 *	the meta data.
		 *	@return array The filtered post data
		 */
		private static function filter_post_data( $post_data ) {
			$post = get_object_vars( $post_data );
			$post[ 'lat' ] = get_post_meta( $post[ 'ID' ], YD_LOCATION_CUSTOM_POST::LOCATION_LAT, true );
			$post[ 'lng' ] = get_post_meta( $post[ 'ID' ], YD_LOCATION_CUSTOM_POST::LOCATION_LNG, true );
			return $post;

			$filtered_post = array();
			$filtered_post[ 'id' ] = $post[ 'ID' ];
			$filtered_post[ 'title' ] = $post[ 'post_title' ];
			$filtered_post[ 'type' ] = $post[ 'post_type' ];
			$filtered_post[ 'content' ] = $post[ 'post_content' ];
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
			return true;  // TODO: check that sw is sw wrt ne?
		}
	}

}

?>