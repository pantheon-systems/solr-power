<?php

/**
 * Class SolrPower_REST
 * Adds REST API endpoints for Solr Power
 */
class SolrPower_REST {
	/**
	 * Singleton instance
	 *
	 * @var SolrPower_WP_Query|Bool
	 */
	private static $instance = false;

	/**
	 * Define REST API route namespace
	 *
	 * @var $namespace
	 */
	var $namespace = 'solr-power/v1';

	/**
	 * Return instance of object.
	 *
	 * @return SolrPower_WP_Query
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			add_action( 'init', array( self::$instance, 'setup' ) );
		}

		return self::$instance;
	}

	/**
	 * SolrPower_REST constructor.
	 */
	function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks' ) );
	}

	/**
	 * Register recipe REST endpoints
	 */
	public function register_api_hooks() {

		register_rest_route( $this->namespace, '/search', array(
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => array( $this, 'solr_search' ),
			'args'     => array(
				'solr_integrate' => array(
					'sanitize_callback' => 'boolval',
					'default'           => true,
				),
				's'              => array(
					'sanitize_callback' => 'sanitize_text_field',
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return ! empty( $param );
					},
				),
				'facet'          => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return ! empty( $param ) && is_array( $param );
					},
				),
			),
		) );

	}

	/**
	 * Query for search w/ facets and create response
	 *
	 * @access protected
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @param array            $args    WP_Query args.
	 * @param bool             $respond . Optional. Whether to create a response, the default, or just return the data.
	 *
	 * @return \WP_HTTP_Response | array
	 */
	public function solr_search( $request, $args, $respond = true ) {
		$params     = $request->get_params();
		$query_args = array(
			'solr_integrate' => $params['solr_integrate'],
			'post_type'      => $params['post_type'],
			's'              => $params['s'],
		);
		$query_args = array_merge( $params, $query_args );
		$posts_query  = new \WP_Query();
		$query_result = $posts_query->query( $query_args );


		$data = array();
		if ( ! empty( $query_result ) ) {
			foreach ( $query_result as $post ) {
				$data[ $post->ID ] = array(
					'ID'   => $post->ID,
					'name' => $post->post_title,
					'url'  => get_the_permalink( $post->ID ),
					'slug' => $post->post_name,
				);

			}
		}

		$data = apply_filters( 'solr_power_search_endpoint_return_data', $data, $posts_query );

		if ( $respond ) {
			$response = new \WP_REST_Response( $data );
			$response->header( 'Access-Control-Allow-Origin', '*' );
			$response->header( 'Cache-Control', 'no_cache' );

			return $response;
		} else {
			return $data;
		}

	}
}
