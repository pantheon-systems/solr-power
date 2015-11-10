<?php

class SolrPower_WP_Query {

	/**
	 * Singleton instance
	 * @var SolrPower_WP_Query|Bool
	 */
	private static $instance = false;

	/**
	 * Array of found Solr returned posts based on query hash.
	 * @var array
	 */
	private $found_posts = array();

	/**
	 * Grab instance of object.
	 * @return SolrPower_WP_Query
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		// We don't want to do a Solr query if we're doing AJAX or in the admin area.
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || (is_admin()) ) {
			return;
		}


		add_filter( 'posts_request', array( $this, 'posts_request' ), 10, 2 );

		// Nukes the FOUND_ROWS() database query
		add_filter( 'found_posts_query', array( $this, 'found_posts_query' ), 5, 2 );

		add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );


		add_action( 'wp', array( $this, 'search' ) );
	}

	function posts_request( $request, $query ) {

		$qry					 = $query->get( 's' );
		$offset					 = $query->get( 'offset' );
		$count					 = $query->get( 'posts_per_page' );
		$fq						 = array();
		$sortby					 = '';
		$order					 = '';
		$search					 = SolrPower_Api::get_instance()->query( $qry, $offset, $count, $fq, $sortby, $order );
		$search					 = $search->getData();
		$search					 = $search[ 'response' ];
		$query->found_posts		 = $search[ 'numFound' ];
		$query->max_num_pages	 = ceil( $search[ 'numFound' ] / $query->get( 'posts_per_page' ) );
		$posts					 = array();

		foreach ( $search[ 'docs' ] as $post_array ) {
			$post		 = new stdClass();
			$post_args	 = array(
				'id'				 => 'ID',
				'type'				 => 'post_type',
				'title'				 => 'post_title',
				'content'			 => 'post_content',
				'displaydate'		 => 'post_date',
				'displaymodified'	 => 'post_modified',
				'permalink'			 => 'permalink'
			);
			foreach ( $post_args as $solr => $arg ) {
				$post->$arg = $post_array[ $solr ];
			}
			$posts[] = $post;
		}
		$this->found_posts[ spl_object_hash( $query ) ] = $posts;

		global $wpdb;

		return "SELECT * FROM $wpdb->posts WHERE 1=0";
	}

	function found_posts_query( $sql, $query ) {

		return '';
	}

	function search() {
		
	}

	function the_posts( $posts, &$query ) {


		$new_posts = $this->found_posts[ spl_object_hash( $query ) ];

		return $new_posts;
	}


}

SolrPower_WP_Query::get_instance();
