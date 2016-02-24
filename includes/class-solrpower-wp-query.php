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
			add_action( 'init', array( self::$instance, 'setup' ) );
		}
		return self::$instance;
	}

	function __construct() {
		
	}

	function setup() {
		// We don't want to do a Solr query if we're doing AJAX or in the admin area.

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( false === apply_filters( 'solr_allow_ajax', false ) ) {
				return;
			}
		}


		if ( is_admin() && false === apply_filters( 'solr_allow_admin', false ) ) {
			return;
		}


		add_filter( 'posts_request', array( $this, 'posts_request' ), 10, 2 );

		// Nukes the FOUND_ROWS() database query
		add_filter( 'found_posts_query', array( $this, 'found_posts_query' ), 5, 2 );

		add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );
	}

	function posts_request( $request, $query ) {
		if ( !$query->is_search() ) {
			return $request;
		}
		$solr_options = SolrPower_Options::get_instance()->get_option();

		$the_page = (!$query->get( 'paged' ) ) ? 1 : $query->get( 'paged' );

		$qry	 = $query->get( 's' );
		$offset	 = $query->get( 'posts_per_page' ) * ($the_page - 1);
		$count	 = $query->get( 'posts_per_page' );
		$fq		 = array();
		$sortby	 = (isset( $solr_options[ 's4wp_default_sort' ] ) && !empty( $solr_options[ 's4wp_default_sort' ] )) ? $solr_options[ 's4wp_default_sort' ] : 'score';

		$order	 = 'desc';
		$search	 = SolrPower_Api::get_instance()->query( $qry, $offset, $count, $fq, $sortby, $order );
		if ( is_null( $search ) ) {
			return false;
		}
		$search					 = $search->getData();
		$search					 = $search[ 'response' ];
		$query->found_posts		 = $search[ 'numFound' ];
		$query->max_num_pages	 = ceil( $search[ 'numFound' ] / $query->get( 'posts_per_page' ) );
		$posts					 = array();

		foreach ( $search[ 'docs' ] as $post_array ) {
			$post = new stdClass();

			foreach ( $post_array as $key => $value ) {
				if ( 'displaydate' == $key ) {
					$post->post_date = $value;
					continue;
				}
				if ( 'displaymodified' == $key ) {
					$post->post_modified = $value;
					continue;
				}
				if ( 'post_date' == $key || 'post_modified' == $key ) {
					continue;
				}

				if ( 'post_id' == $key ) {
					$post->ID = $value;
					continue;
				}

				$post->$key = $value;
			}
			$post->solr	 = true;
			$posts[]	 = $post;
		}

		$this->found_posts[ spl_object_hash( $query ) ] = $posts;

		global $wpdb;

		return "SELECT * FROM $wpdb->posts WHERE 1=0";
	}

	function found_posts_query( $sql, $query ) {

		return '';
	}

	function the_posts( $posts, &$query ) {
		if ( !isset( $this->found_posts[ spl_object_hash( $query ) ] ) ) {
			return $posts;
		}

		$new_posts = $this->found_posts[ spl_object_hash( $query ) ];

		return $new_posts;
	}

}

SolrPower_WP_Query::get_instance();
