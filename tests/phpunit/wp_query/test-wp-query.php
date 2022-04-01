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
			$this->assertTrue( $post->solr );
			$this->assertEquals( $post->post_title, get_the_title() );
			$this->assertEquals( $post->post_author, 1 );
			$this->assertEquals( $post->post_content, get_the_content() );
			$this->assertEquals( $post->post_date, $wp_post->post_date );
			$this->assertEquals( $post->post_modified, $wp_post->post_modified );
			$this->assertEquals( $post->post_name, $wp_post->post_name );
			$this->assertEquals( $post->post_parent, $wp_post->post_parent );
			$this->assertEquals( $post->post_excerpt, $wp_post->post_excerpt );
		}

		wp_reset_postdata();
	}

	/**
	 * Performs simple search query with WP_Query using Solr.
	 *
	 * @global WP_Post $post
	 */
	function test_simple_wp_query_solr_integrate() {
		$this->__create_test_post();
		$args  = array(
			's'              => 'solr',
			'solr_integrate' => true,
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

	/**
	 * Solr Power should correctly apply filters even when search string is empty.
	 */
	function test_wp_query_solr_search_empty_string_applies_filters() {
		$this->__create_test_post();
		$this->__create_test_post( 'page' );
		$query = new WP_Query( array(
			'post_type' => 'post',
			's'         => '',
		) );
		$this->assertTrue( $query->posts[0]->solr );
		$this->assertCount( 1, $query->posts );
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

	function test_wp_query_failed_ping() {
		$this->__create_test_post( 'post', 'Query Failed Ping' );
		$noop_connection = function() {
			return array(
				'endpoint' => array(
					'localhost' => array(
						'host' => '',
						'port' => '',
						'path' => '',
					)
				),
			);
		};
		add_filter( 's4wp_connection_options', $noop_connection );
		SolrPower_Api::get_instance()->solr = null;
		SolrPower_Api::get_instance()->ping = false;
		$query = new WP_Query( array(
			's' => 'Query Failed Ping',
		) );
		$this->assertEquals( 1, $query->post_count );
		$this->assertEquals( 1, $query->found_posts );
		$this->assertFalse( isset( $query->posts[0]->solr ) );
		remove_filter( 's4wp_connection_options', $noop_connection );
	}

	public function test_wp_query_search_filter_post_type() {
		$post_id = $this->__create_test_post(); // Title defaults to 'Test Post'
		$page_id = $this->__create_test_post( 'page' ); // Title defaults to 'Test Post'
		$args = array(
			's'         => 'Test',
			'post_type' => 'page',
		);
		$query = new WP_Query( $args );
		$this->assertEquals( 1, $query->post_count );
		$this->assertEquals( 1, $query->found_posts );
		$this->assertEquals( 'page', $query->get('post_type') );
		$this->assertEquals( array( $page_id ), wp_list_pluck( $query->posts, 'ID' ) );
	}

	public function test_wp_query_by_post__in() {
		$post_id = $this->__create_test_post();
		$post_id2 = $this->__create_test_post();
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'post__in'       => array( $post_id2 ),
		);
		$query = new WP_Query( $args );
		$this->assertEquals( 1, $query->post_count );
		$this->assertEquals( $post_id2, $query->posts[0]->ID );
	}

	public function test_wp_query_by_post__not_in() {
		$post_id = $this->__create_test_post();
		$post_id2 = $this->__create_test_post();
		$post_id3 = $this->__create_test_post();
		$post_id4 = $this->__create_test_post();
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'post__not_in'   => array( $post_id, $post_id3 ),
		);
		$query = new WP_Query( $args );
		$this->assertEquals( 2, $query->post_count );
		$ids = wp_list_pluck( $query->posts, 'ID' );
		sort( $ids );
		$this->assertEquals( array( $post_id2, $post_id4 ), $ids );
	}

	public function test_wp_query_fields_id_parent() {

		$p1 = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$p2 = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$p3 = self::factory()->post->create( array( 'post_type' => 'page' ) );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'page', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'id=>parent',
			'post_type'      => 'page',
		) );
		$this->assertEqualSets(
			array( $p1 => 0, $p2 => 0, $p3 => 0 ),
			$query->get_posts()
		);
	}
}
