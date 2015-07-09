<?php

if ( ! class_exists( 'YD_LOCATION_CUSTOM_POST' ) ) {

	/**
	 * Creates a custom post type and associated taxonomies
	 */
	class YD_LOCATION_CUSTOM_POST extends YD_Module implements YD_Custom_Post_Type {
		protected static $readable_properties  = array();
		protected static $writeable_properties = array();

		const VERSION    = '0.1';
		const PREFIX     = 'yd_';

		const POST_TYPE_NAME = 'Location Post';
		const POST_TYPE_SLUG = 'yd-location-post';
		const TAG_NAME       = 'Location Taxonomy';
		const TAG_SLUG       = 'yd-location-custom-tax';

		const LOCATION_META_ID = 'yd-location-meta';
		const LOCATION_LAT = 'yd-location-lat';
		const LOCATION_LNG = 'yd-location-lng';

		/*
		 * Magic methods
		 */

		/**
		 * Constructor
		 *
		 * @mvc Controller
		 */
		protected function __construct() {
			$this->register_hook_callbacks();
		}


		/*
		 * Static methods
		 */

		/**
		 * Registers the custom post type
		 *
		 * @mvc Controller
		 */
		public static function create_post_type() {
			if ( ! post_type_exists( self::POST_TYPE_SLUG ) ) {
				$post_type_params = self::get_post_type_params();
				$post_type        = register_post_type( self::POST_TYPE_SLUG, $post_type_params );

				if ( is_wp_error( $post_type ) ) {
					add_notice( __METHOD__ . ' error: ' . $post_type->get_error_message(), 'error' );
				}
			}
		}

		/**
		 * Defines the parameters for the custom post type
		 *
		 * @mvc Model
		 *
		 * @return array
		 */
		protected static function get_post_type_params() {
			$labels = array(
				'name'               => self::POST_TYPE_NAME . 's',
				'singular_name'      => self::POST_TYPE_NAME,
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New ' . self::POST_TYPE_NAME,
				'edit'               => 'Edit',
				'edit_item'          => 'Edit ' .    self::POST_TYPE_NAME,
				'new_item'           => 'New ' .     self::POST_TYPE_NAME,
				'view'               => 'View ' .    self::POST_TYPE_NAME . 's',
				'view_item'          => 'View ' .    self::POST_TYPE_NAME,
				'search_items'       => 'Search ' .  self::POST_TYPE_NAME . 's',
				'not_found'          => 'No ' .      self::POST_TYPE_NAME . 's found',
				'not_found_in_trash' => 'No ' .      self::POST_TYPE_NAME . 's found in Trash',
				'parent'             => 'Parent ' .  self::POST_TYPE_NAME
			);

			$post_type_params = array(
				'labels'               => $labels,
				'singular_label'       => self::POST_TYPE_NAME,
				'public'               => true,  // TODO: remove once done!
				'exclude_from_search'  => true,
				'publicly_queryable'   => true,
				'show_ui'              => true,
				'show_in_menu'         => true,
				'register_meta_box_cb' => __CLASS__ . '::add_meta_boxes',
				'taxonomies'           => array( self::TAG_SLUG ),
				'menu_position'        => 20,
				'hierarchical'         => true,
				'capability_type'      => 'post',
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'supports'             => array( 'title', 'editor', 'thumbnail', 'revisions' )
			);

			return apply_filters( 'yd_post-type-params', $post_type_params );
		}

		/**
		 * Registers the category taxonomy
		 *
		 * @mvc Controller
		 */
		public static function create_taxonomies() {
			if ( ! taxonomy_exists( self::TAG_SLUG ) ) {
				$tag_taxonomy_params = self::get_tag_taxonomy_params();
				register_taxonomy( self::TAG_SLUG, self::POST_TYPE_SLUG, $tag_taxonomy_params );
			}
		}

		/**
		 * Defines the parameters for the custom taxonomy
		 *
		 * @mvc Model
		 *
		 * @return array
		 */
		protected static function get_tag_taxonomy_params() {
			$tag_taxonomy_params = array(
				'label'                 => self::TAG_NAME,
				'labels'                => array( 'name' => self::TAG_NAME, 'singular_name' => self::TAG_NAME ),
				'hierarchical'          => true,
				'rewrite'               => array( 'slug' => self::TAG_SLUG ),
				'update_count_callback' => '_update_post_term_count'
			);

			return apply_filters( 'yd_tag-taxonomy-params', $tag_taxonomy_params );
		}

		/**
		 * Adds meta boxes for the custom post type
		 *
		 * @mvc Controller
		 */
		public static function add_meta_boxes() {
			add_meta_box(
				self::LOCATION_META_ID,
				'Post Location',
				__CLASS__ . '::markup_meta_boxes',
				self::POST_TYPE_SLUG,
				'normal',
				'core'
			);
		}

		/**
		 * Builds the markup for all meta boxes
		 *
		 * @mvc Controller
		 *
		 * @param object $post
		 * @param array  $box
		 */
		public static function markup_meta_boxes( $post, $box ) {
			$variables = array();
			switch ( $box['id'] ) {
				case self::LOCATION_META_ID:
					$variables[ self::LOCATION_LAT ] = get_post_meta( $post->ID, self::LOCATION_LAT, true );
					$variables[ self::LOCATION_LNG ] = get_post_meta( $post->ID, self::LOCATION_LNG, true );
					$view                         = 'yd-location-post/location-meta-box.php';
					break;

				default:
					$view = false;
					break;
			}

			echo self::render_template( $view, $variables );
		}

		/**
		 * Determines whether a meta key should be considered private or not
		 *
		 * @mvc Model
		 *
		 * @param bool $protected
		 * @param string $meta_key
		 * @param mixed $meta_type
		 * @return bool
		 */
		public static function is_protected_meta( $protected, $meta_key, $meta_type ) {
			switch( $meta_key ) {
				case 'yd_example-box':
				case 'yd_example-box2':
					$protected = true;
					break;

				case 'yd_some-other-box':
				case 'yd_some-other-box2':
					$protected = false;
					break;
			}

			return $protected;
		}

		/**
		 * Saves values of the the custom post type's extra fields
		 *
		 * @mvc Controller
		 *
		 * @param int    $post_id
		 * @param object $post
		 */
		public static function save_post( $post_id, $revision ) {
			global $post;
			$ignored_actions = array( 'trash', 'untrash', 'restore' );

			if ( isset( $_GET['action'] ) && in_array( $_GET['action'], $ignored_actions ) ) {
				return;
			}

			if ( ! $post || $post->post_type != self::POST_TYPE_SLUG || ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft' ) {
				return;
			}

			self::save_custom_fields( $post_id, $_POST );
		}

		/**
		 * Validates and saves values of the the custom post type's extra fields
		 *
		 * @mvc Model
		 *
		 * @param int   $post_id
		 * @param array $new_values
		 */
		protected static function save_custom_fields( $post_id, $new_values ) {

			if ( isset( $new_values[ self::LOCATION_LAT ] ) ) {
				update_post_meta( $post_id, self::LOCATION_LAT, $new_values[ self::LOCATION_LAT ] );
			}
			if ( isset( $new_values[ self::LOCATION_LNG ] ) ) {
				update_post_meta( $post_id, self::LOCATION_LNG, $new_values[ self::LOCATION_LNG ] );
			}
		}

		/**
		 * Defines the [POST_TYPE_SLUG] shortcode
		 *
		 * @mvc Controller
		 *
		 * @param array $attributes
		 * @return string
		 */
		public static function shortcode( $attributes ) {
			$attributes = apply_filters( 'yd-shortcode-attributes', $attributes );
			$attributes = self::validate_shortcode_attributes( $attributes );

			// Include the javascript and the styling for the shortcode
			wp_register_script(
				self::PREFIX . 'shortcode',
				plugins_url( 'javascript/yd-shortcode.js', dirname( __FILE__ ) . '../' ),
				array( 'jquery' ),
				self::VERSION,
				true
			);
			wp_enqueue_script( self::PREFIX . 'shortcode' );

			wp_register_style(
				self::PREFIX . 'shortcode',
				plugins_url( 'css/shortcode.css', dirname( __FILE__ ) . '../' ),
				array(),
				self::VERSION,
				'all'
			);
			wp_enqueue_style( self::PREFIX . 'shortcode' );
			

			return self::render_template( 'yd-location-post/shortcode.php', array( 'attributes' => $attributes ) );
		}

		/**
		 * Validates the attributes for the [POST_TYPE_SLUG] shortcode
		 *
		 * @mvc Model
		 *
		 * @param array $attributes
		 * @return array
		 */
		protected static function validate_shortcode_attributes( $attributes ) {
			$defaults   = self::get_default_shortcode_attributes();
			$attributes = shortcode_atts( $defaults, $attributes );
			// TODO: update the validation to check for location posts (or that the attributes contain valid post ids)
			// if ( $attributes['foo'] != 'valid data' ) {
			// 	$attributes['foo'] = $defaults['foo'];
			// }

			return apply_filters( 'yd-validate-shortcode-attributes', $attributes );
		}

		/**
		 * Defines the default arguments for the [POST_TYPE_SLUG] shortcode
		 * By Default, we will pull the latest location post to display on the map.
		 *
		 * @mvc Model
		 *
		 * @return array
		 */
		protected static function get_default_shortcode_attributes() {
			$attributes = array(
				'mapsApiKey' => get_option( 'yd_settings' )[ 'required' ][ 'yd-google-maps-api-key' ]
			);

			return apply_filters( 'yd-default-shortcode-attributes', $attributes );
		}


		/*
		 * Instance methods
		 */

		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'init',                     __CLASS__ . '::create_post_type' );
			add_action( 'init',                     __CLASS__ . '::create_taxonomies' );
			add_action( 'save_post',                __CLASS__ . '::save_post', 10, 2 );
			add_filter( 'is_protected_meta',        __CLASS__ . '::is_protected_meta', 10, 3 );

			add_action( 'init',                     array( $this, 'init' ) );
			add_action( 'wp_json_server_before_serve', __CLASS__ . '::yd_api_init' );

			add_shortcode( self::POST_TYPE_SLUG, __CLASS__ . '::shortcode' );
		}

		/**
		 *	Creates an instance of our Rest API.
		 */
		public static function yd_api_init() {
			global $rest_api;
			$rest_api = new YD_REST_API();
			add_filter( 'json_endpoints', array( $rest_api, 'register_routes' ) );
		}
		

		

		/**
		 * Prepares site to use the plugin during activation
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
			self::create_post_type();
			self::create_taxonomies();
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		public function deactivate() {
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {
		}

		/**
		 * Executes the logic of upgrading from specific older versions of the plugin to the current version
		 *
		 * @mvc Model
		 *
		 * @param string $db_version
		 */
		public function upgrade( $db_version = 0 ) {
			/*
			if( version_compare( $db_version, 'x.y.z', '<' ) )
			{
				// Do stuff
			}
			*/
		}

		/**
		 * Checks that the object is in a correct state
		 *
		 * @mvc Model
		 *
		 * @param string $property An individual property to check, or 'all' to check all of them
		 *
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			return true;
		}
	} // end YD_LOCATION_CUSTOM_POST
}
