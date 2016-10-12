<?php

class SolrTaxQueryTest extends SolrTestBase  {
	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
	}

	function test_wp_query_by_tax() {
		$this->__create_test_post();

		$p_id = $this->__create_test_post();
		wp_set_object_terms( $p_id, 'Horror', 'genre', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'genre',
					'terms'    => array( 'Horror' ),
					'field'    => 'name',
				),
			),
		);
		$query = new WP_Query( $args );

		$this->assertEquals( $p_id, $query->post->ID );
	}

	function test_wp_query_by_tax_slug() {
		$this->__create_test_post();

		$p_id = $this->__create_test_post();
		wp_set_object_terms( $p_id, 'Horror', 'genre', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'genre',
					'terms'    => array( 'horror' ),
					'field'    => 'slug',
				),
			),
		);
		$query = new WP_Query( $args );
		$this->assertEquals( $p_id, $query->post->ID );
	}

	function test_wp_query_by_tax_id() {
		$this->__create_test_post();

		$p_id = $this->__create_test_post();
		wp_set_object_terms( $p_id, 'Horror', 'genre', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'genre',
					'terms'    => array( $this->term_id ),
					'field'    => 'id',
				),
			),
		);
		$query = new WP_Query( $args );

		$this->assertEquals( $p_id, $query->post->ID );
	}

	function test_wp_query_by_tax_cat() {
		$this->__create_test_post();
		$cat_id_one = wp_create_category( 'Term Slug' );

		$p_id = $this->__create_test_post();
		wp_set_object_terms( $p_id, $cat_id_one, 'category', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'Term Slug' ),
					'field'    => 'name',
				),
			),
		);
		$query = new WP_Query( $args );
		$this->assertEquals( $p_id, $query->post->ID );
	}

	function test_wp_query_by_tax_cat_slug() {
		$this->__create_test_post();
		$cat_id_one = wp_create_category( 'Term Slug' );

		$p_id = $this->__create_test_post();
		wp_set_object_terms( $p_id, $cat_id_one, 'category', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'term-slug' ),
					'field'    => 'slug',
				),
			),
		);
		$query = new WP_Query( $args );

		$this->assertEquals( $p_id, $query->post->ID );
	}

	function test_wp_query_by_tax_cat_id() {
		$this->__create_test_post();
		$cat_id_one = wp_create_category( 'Term Slug' );

		$p_id = $this->__create_test_post();
		wp_set_object_terms( $p_id, $cat_id_one, 'category', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args  = array(
			'solr_integrate' => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( $cat_id_one ),
					'field'    => 'id',
				),
			),
		);
		$query = new WP_Query( $args );
		$this->assertEquals( $p_id, $query->post->ID );
	}

}