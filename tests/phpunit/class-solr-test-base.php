<?php
class SolrTestBase extends WP_UnitTestCase{

	/**
	 * @var integer Term ID of custom taxonomy term.
	 */
	var $term_id;

	function __construct() {
		parent::__construct();
		// For tests, we're not using https.
		add_filter( 'solr_scheme', function () {
			return 'http';
		} );
		SolrPower_Options::get_instance()->initalize_options();
		$this->__setup_taxonomy();
	}

	/**
	 * Setup for every test.
	 */
	function setUp() {
		parent::setUp();
		// Delete the entire index.
		SolrPower_Sync::get_instance()->delete_all();

		SolrPower_WP_Query::get_instance()->reset_vars();
		// Setup options (if not already set)
		$solr_options = solr_options();
		if ( 1 !== $solr_options['s4wp_solr_initialized'] ) {
			$options = SolrPower_Options::get_instance()->initalize_options();
			update_option( 'plugin_s4wp_settings', $options );
		}

	}

	function __setup_taxonomy() {

		$labels = array(
			'name'              => _x( 'Genres', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Genre', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Genres', 'textdomain' ),
			'all_items'         => __( 'All Genres', 'textdomain' ),
			'parent_item'       => __( 'Parent Genre', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Genre:', 'textdomain' ),
			'edit_item'         => __( 'Edit Genre', 'textdomain' ),
			'update_item'       => __( 'Update Genre', 'textdomain' ),
			'add_new_item'      => __( 'Add New Genre', 'textdomain' ),
			'new_item_name'     => __( 'New Genre Name', 'textdomain' ),
			'menu_name'         => __( 'Genre', 'textdomain' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'genre' ),
		);

		register_taxonomy( 'genre', array( 'post' ), $args );


		// Create 'Horror' genre
		wp_insert_term( 'Horror', 'genre' );
		$term          = get_term_by( 'slug', 'horror', 'genre' );
		$this->term_id = $term->term_id;
	}


	/**
	 * Creates a new post.
	 * @return int|WP_Error
	 */
	function __create_test_post( $post_type = 'post' ) {
		$args = array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			'post_title'   => 'Test Post ' . time(),
			'post_content' => 'This is a solr test.',
		);

		return wp_insert_post( $args );
	}

	function __create_multiple( $number = 1 ) {
		for ( $i = 0; $i < $number; $i ++ ) {
			$this->__create_test_post();
		}
	}

	function __change_option( $key, $value ) {
		$solr_options         = solr_options();
		$solr_options[ $key ] = $value;
		update_option( 'plugin_s4wp_settings', $solr_options );
	}

	/**
	 * @param string $qry Search query.
	 *
	 * @return object
	 */
	function __run_test_query( $qry = 'solr' ) {
		$offset = 0;
		$count  = 10;
		$fq     = array();
		$sortby = 'score';
		$order  = 'desc';

		return SolrPower_Api::get_instance()->query( $qry, $offset, $count, $fq, $sortby, $order );
	}

	function __facet_query( $args = array() ) {
		$defaults = array(
			's' => 'solr'
		);

		$args = array_merge( $defaults, $args );

		return new WP_Query( $args );
	}


	function __setup_custom_fields() {
		$p_id = $this->__create_test_post();
		update_post_meta( $p_id, 'my_field', 'my_value' );
		update_post_meta( $p_id, 'other_field', 'other_value' );
		$p_id = $this->__create_test_post();
		update_post_meta( $p_id, 'my_field', 'my_value2' );
		update_post_meta( $p_id, 'other_field', 'other_value2' );
		$p_id = $this->__create_test_post();
		update_post_meta( $p_id, 'my_field', 'my_value3' );
		update_post_meta( $p_id, 'other_field', 'other_value' );
		$p_id = $this->__create_test_post();
		// This post will have the same custom field value (so two will have my_value).
		update_post_meta( $p_id, 'my_field', 'my_value' );
		update_post_meta( $p_id, 'other_field', 'other_value' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
	}
}