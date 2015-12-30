<?php

class SolrTest extends WP_UnitTestCase {

	function __construct() {
		parent::__construct();
		// For tests, we're not using https.
		add_filter( 'solr_scheme', function() {
			return 'http';
		} );
	}

	/**
	 * Setup for every test.
	 */
	function setUp() {
		parent::setUp();
		
	}
	
	function tearDown(){
		parent::tearDown();
		// Delete all pages and posts after each test:
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'posts', array( 'post_type' => 'page' ) );
		$wpdb->delete( $wpdb->prefix . 'posts', array( 'post_type' => 'post' ) );
		// Delete the entire index.
		SolrPower_Sync::get_instance()->delete_all();
	}

	/**
	 * Creates a new post.
	 * @return int|WP_Error
	 */
	function __create_test_post() {
		$args = array(
			'post_type'		 => 'post',
			'post_status'	 => 'publish',
			'post_title'	 => 'Test Post ' . time(),
			'post_content'	 => 'This is a solr test.',
		);

		return wp_insert_post( $args );
	}

	function __run_test_query() {
		$qry	 = 'solr';
		$offset	 = 0;
		$count	 = 10;
		$fq		 = array();
		$sortby	 = 'score';
		$order	 = 'desc';
		return SolrPower_Api::get_instance()->query( $qry, $offset, $count, $fq, $sortby, $order );
	}

	/**
	 * Test to see if we can ping the Solr server.
	 */
	function test_solr_active() {
		$this->assertTrue( SolrPower_Api::get_instance()->ping_server() );
	}

	/**
	 * Create a post and see if it gets indexed.
	 */
	function test_index_post() {
		$post_id = $this->__create_test_post();
		$search	 = $this->__run_test_query();
		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search	 = $search->getData();
		$search	 = $search[ 'response' ];

		$this->assertEquals( $search[ 'docs' ][ 0 ][ 'ID' ], $post_id );
	}

	/**
	 * Delete a post and see if it gets removed from index.
	 */
	function test_delete_post() {
		$post_id = $this->__create_test_post();
		wp_delete_post( $post_id );
		$search	 = $this->__run_test_query();
		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search	 = $search->getData();
		$search	 = $search[ 'response' ];
		// We should have zero results.
		$this->assertEquals( $search[ 'numFound' ], 0 );
	}

	/**
	 * Change post status to draft and see if it gets removed from index.
	 */
	function test_post_status_change() {
		$post_id = $this->__create_test_post();
		// Let's transition the post status from published to draft
		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
		$search	 = $this->__run_test_query();
		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search	 = $search->getData();
		$search	 = $search[ 'response' ];
		// We should have zero results.
		$this->assertEquals( $search[ 'numFound' ], 0 );
	}

	/**
	 * Performs simple search query with WP_Query.
	 * @global WP_Post $post
	 */
	function test_simple_wp_query() {
		$post_id = $this->__create_test_post();
		$args	 = array(
			's' => 'solr'
		);
		$query	 = new WP_Query( $args );
		$this->assertEquals( $query->post_count, 1 );
		$this->assertEquals( $query->found_posts, 1 );
		while ( $query->have_posts() ) {
			$query->the_post();

			global $post;

			$wp_post = get_post( get_the_ID() );
			$this->assertEquals( $post->solr, true );
			$this->assertEquals( $post->post_title, get_the_title() );
			$this->assertEquals( $post->post_content, get_the_content() );
			$this->assertEquals( $post->post_date, $wp_post->post_date );
			$this->assertEquals( $post->post_modified, $wp_post->post_modified );
			$this->assertEquals( $post->post_name, $wp_post->post_name );
			$this->assertEquals( $post->post_parent, $wp_post->post_parent );
			$this->assertEquals( $post->post_excerpt, $wp_post->post_excerpt );
		}

		wp_reset_postdata();
	}

}
