<?php

class SolrWPQueryTest extends SolrTestBase {
	function setUp() {
		parent::setUp();
	}

	/**
	 * Performs simple search query with WP_Query.
	 * @global WP_Post $post
	 */
	function test_simple_wp_query() {
		$this->__create_test_post();
		$args  = array(
			's' => 'solr'
		);
		$query = new WP_Query( $args );
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

	function test_wp_query_paged() {
		$this->__create_multiple( 15 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			's'     => 'solr',
			'paged' => 2
		);
		$query = new WP_Query( $args );
		$this->assertEquals( 5, $query->post_count );
		$this->assertEquals( 2, $query->max_num_pages );
	}

	function test_wp_query_many_pages() {
		$this->__create_multiple( 55 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			's'              => 'solr',
			'posts_per_page' => 10
		);
		$query = new WP_Query( $args );
		$this->assertEquals( 6, $query->max_num_pages );
	}


	function test_wp_query_published() {
		$this->__create_multiple( 15 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			's'           => 'solr',
			'post_status' => 'publish'
		);
		$query = new WP_Query( $args );
		$this->assertEquals( 15, $query->found_posts );
	}

	function test_wp_query_by_id() {
		$post_id = $this->__create_test_post();
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'p'              => $post_id
		);
		$query = new WP_Query( $args );
		$this->assertEquals( $post_id, $query->post->ID );
	}

	function test_wp_query_by_post_type() {
		$post_id = $this->__create_test_post();
		$page_id = $this->__create_test_post( 'page' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'post_type'      => 'page'
		);
		$query = new WP_Query( $args );
		$this->assertEquals( $page_id, $query->post->ID );
	}

	function test_wp_query_by_post_type_arr() {
		$post_id = $this->__create_test_post();
		$page_id = $this->__create_test_post( 'page' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'post_type'      => array( 'page', 'post' ),
		);
		$query = new WP_Query( $args );
		$this->assertEquals( 2, $query->post_count );
		$this->assertEquals( 2, $query->found_posts );
	}

}