<?php

class SolrTaxQueryTest extends SolrTestBase {
	function setUp() {

		parent::setUp();

	}

	function tearDown() {
		parent::tearDown();
	}

	function show_query() {
		print_r( SolrPower_WP_Query::get_instance()->backup );
		print_r( SolrPower_Api::get_instance()->log );
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
					'field'    => 'term_id',
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
					'field'    => 'term_id',
				),
			),
		);
		$query = new WP_Query( $args );
		$this->assertEquals( $p_id, $query->post->ID );
	}

	public function test_tax_query_single_query_single_term_field_slug() {
		$t  = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
				),
			),
		) );

		$this->assertEquals( array( $p1 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_single_term_field_name() {
		$t  = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'Foo' ),
					'field'    => 'name',
				),
			),
		) );

		$this->assertEquals( array( $p1 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_field_name_should_work_for_names_with_spaces() {
		register_taxonomy( 'wptests_tax', 'post' );

		$t  = self::factory()->term->create( array(
			'taxonomy' => 'wptests_tax',
			'slug'     => 'foo',
			'name'     => 'Foo Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		wp_set_object_terms( $p1, $t, 'wptests_tax' );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wptests_tax',
					'terms'    => array( 'Foo Bar' ),
					'field'    => 'name',
				),
			),
		) );

		$this->assertEquals( array( $p1 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_single_term_field_term_taxonomy_id() {
		$t  = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		$tt_ids = wp_set_post_terms( $p1, $t, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => $tt_ids,
					'field'    => 'term_taxonomy_id',
				),
			),
		) );

		$this->assertEquals( array( $p1 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_single_term_field_term_id() {
		$t  = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( $t ),
					'field'    => 'term_id',
				),
			),
		) );

		$this->assertEquals( array( $p1 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_single_term_operator_in() {
		$t  = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
					'operator' => 'IN',
				),
			),
		) );

		$this->assertEquals( array( $p1 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_single_term_operator_not_in() {
		$t  = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );

		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
					'operator' => 'NOT IN',
				),
			),
		) );
		$this->assertEquals( array( $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_single_term_operator_and() {
		$t  = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
					'operator' => 'AND',
				),
			),
		) );
		$this->assertEquals( array( $p1 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_multiple_terms_operator_in() {
		$t1 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$t2 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'bar',
			'name'     => 'Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t1, 'category' );
		wp_set_post_terms( $p2, $t2, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo', 'bar' ),
					'field'    => 'slug',
					'operator' => 'IN',
				),
			),
		) );

		$this->assertEqualSets( array( $p1, $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_multiple_terms_operator_not_in() {
		$t1 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$t2 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'bar',
			'name'     => 'Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t1, 'category' );
		wp_set_post_terms( $p2, $t2, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo', 'bar' ),
					'field'    => 'slug',
					'operator' => 'NOT IN',
				),
			),
		) );

		$this->assertEquals( array( $p3 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_multiple_queries_operator_not_in() {
		$t1 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$t2 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'bar',
			'name'     => 'Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_post_terms( $p1, $t1, 'category' );
		wp_set_post_terms( $p2, $t2, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
					'operator' => 'NOT IN',
				),
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'bar' ),
					'field'    => 'slug',
					'operator' => 'NOT IN',
				),
			),
		) );

		$this->assertEquals( array( $p3 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_single_query_multiple_terms_operator_and() {
		$t1 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$t2 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'bar',
			'name'     => 'Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, $t1, 'category' );
		wp_set_object_terms( $p2, array( $t1, $t2 ), 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo', 'bar' ),
					'field'    => 'slug',
					'operator' => 'AND',
				),
			),
		) );

		$this->assertEquals( array( $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_operator_not_exists() {
		register_taxonomy( 'wptests_tax1', 'post' );
		register_taxonomy( 'wptests_tax2', 'post' );

		$t1 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax1' ) );
		$t2 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax2' ) );

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, array( $t1 ), 'wptests_tax1' );
		wp_set_object_terms( $p2, array( $t2 ), 'wptests_tax2' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wptests_tax2',
					'operator' => 'NOT EXISTS',
				),
			),
		) );

		$this->assertEqualSets( array( $p1, $p3 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_operator_exists() {
		register_taxonomy( 'wptests_tax1', 'post' );
		register_taxonomy( 'wptests_tax2', 'post' );

		$t1 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax1' ) );
		$t2 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax2' ) );

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, array( $t1 ), 'wptests_tax1' );
		wp_set_object_terms( $p2, array( $t2 ), 'wptests_tax2' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wptests_tax2',
					'operator' => 'EXISTS',
				),
			),
		) );

		$this->assertEqualSets( array( $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_operator_exists_should_ignore_terms() {
		register_taxonomy( 'wptests_tax1', 'post' );
		register_taxonomy( 'wptests_tax2', 'post' );

		$t1 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax1' ) );
		$t2 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax2' ) );

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, array( $t1 ), 'wptests_tax1' );
		wp_set_object_terms( $p2, array( $t2 ), 'wptests_tax2' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wptests_tax2',
					'operator' => 'EXISTS',
					'terms'    => array( 'foo', 'bar' ),
				),
			),
		) );

		$this->assertEqualSets( array( $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_operator_exists_with_no_taxonomy() {
		register_taxonomy( 'wptests_tax1', 'post' );
		register_taxonomy( 'wptests_tax2', 'post' );

		$t1 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax1' ) );
		$t2 = self::factory()->term->create( array( 'taxonomy' => 'wptests_tax2' ) );

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, array( $t1 ), 'wptests_tax1' );
		wp_set_object_terms( $p2, array( $t2 ), 'wptests_tax2' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'tax_query'      => array(
				array(
					'operator' => 'EXISTS',
				),
			),
		) );

		$this->assertEmpty( $q->posts );
	}

	public function test_tax_query_multiple_queries_relation_and() {
		$t1 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$t2 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'bar',
			'name'     => 'Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, $t1, 'category' );
		wp_set_object_terms( $p2, array( $t1, $t2 ), 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
				),
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'bar' ),
					'field'    => 'slug',
				),
			),
		) );

		$this->assertEquals( array( $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_multiple_queries_relation_or() {
		$t1 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$t2 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'bar',
			'name'     => 'Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, $t1, 'category' );
		wp_set_object_terms( $p2, array( $t1, $t2 ), 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
				),
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'bar' ),
					'field'    => 'slug',
				),
			),
		) );

		$this->assertEqualSets( array( $p1, $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_multiple_queries_different_taxonomies() {
		$t1 = self::factory()->term->create( array(
			'taxonomy' => 'post_tag',
			'slug'     => 'foo',
			'name'     => 'Foo',
		) );
		$t2 = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'slug'     => 'bar',
			'name'     => 'Bar',
		) );
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, $t1, 'post_tag' );
		wp_set_object_terms( $p2, $t2, 'category' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'post_tag',
					'terms'    => array( 'foo' ),
					'field'    => 'slug',
				),
				array(
					'taxonomy' => 'category',
					'terms'    => array( 'bar' ),
					'field'    => 'slug',
				),
			),
		) );

		$this->assertEqualSets( array( $p1, $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_two_nested_queries() {
		register_taxonomy( 'foo', 'post' );
		register_taxonomy( 'bar', 'post' );

		$foo_term_1 = self::factory()->term->create( array(
			'taxonomy' => 'foo',
		) );
		$foo_term_2 = self::factory()->term->create( array(
			'taxonomy' => 'foo',
		) );
		$bar_term_1 = self::factory()->term->create( array(
			'taxonomy' => 'bar',
		) );
		$bar_term_2 = self::factory()->term->create( array(
			'taxonomy' => 'bar',
		) );

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, array( $foo_term_1 ), 'foo' );
		wp_set_object_terms( $p1, array( $bar_term_1 ), 'bar' );
		wp_set_object_terms( $p2, array( $foo_term_2 ), 'foo' );
		wp_set_object_terms( $p2, array( $bar_term_2 ), 'bar' );
		wp_set_object_terms( $p3, array( $foo_term_1 ), 'foo' );
		wp_set_object_terms( $p3, array( $bar_term_2 ), 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'foo',
						'terms'    => array( $foo_term_1 ),
						'field'    => 'term_id',
					),
					array(
						'taxonomy' => 'bar',
						'terms'    => array( $bar_term_1 ),
						'field'    => 'term_id',
					),
				),
				array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'foo',
						'terms'    => array( $foo_term_2 ),
						'field'    => 'term_id',
					),
					array(
						'taxonomy' => 'bar',
						'terms'    => array( $bar_term_2 ),
						'field'    => 'term_id',
					),
				),
			),
		) );

		_unregister_taxonomy( 'foo' );
		_unregister_taxonomy( 'bar' );

		$this->assertEqualSets( array( $p1, $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_one_nested_query_one_first_order_query() {
		register_taxonomy( 'foo', 'post' );
		register_taxonomy( 'bar', 'post' );

		$foo_term_1 = self::factory()->term->create( array(
			'taxonomy' => 'foo',
		) );
		$foo_term_2 = self::factory()->term->create( array(
			'taxonomy' => 'foo',
		) );
		$bar_term_1 = self::factory()->term->create( array(
			'taxonomy' => 'bar',
		) );
		$bar_term_2 = self::factory()->term->create( array(
			'taxonomy' => 'bar',
		) );

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		wp_set_object_terms( $p1, array( $foo_term_1 ), 'foo' );
		wp_set_object_terms( $p1, array( $bar_term_1 ), 'bar' );
		wp_set_object_terms( $p2, array( $foo_term_2 ), 'foo' );
		wp_set_object_terms( $p2, array( $bar_term_2 ), 'bar' );
		wp_set_object_terms( $p3, array( $foo_term_1 ), 'foo' );
		wp_set_object_terms( $p3, array( $bar_term_2 ), 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'foo',
					'terms'    => array( $foo_term_2 ),
					'field'    => 'term_id',
				),
				array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'foo',
						'terms'    => array( $foo_term_1 ),
						'field'    => 'term_id',
					),
					array(
						'taxonomy' => 'bar',
						'terms'    => array( $bar_term_1 ),
						'field'    => 'term_id',
					),
				),
			),
		) );

		_unregister_taxonomy( 'foo' );
		_unregister_taxonomy( 'bar' );

		$this->assertEqualSets( array( $p1, $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}


	public function test_tax_query_one_double_nested_query_one_first_order_query() {
		register_taxonomy( 'foo', 'post' );
		register_taxonomy( 'bar', 'post' );

		$foo_term_1 = self::factory()->term->create( array(
			'taxonomy' => 'foo',
		) );
		$foo_term_2 = self::factory()->term->create( array(
			'taxonomy' => 'foo',
		) );
		$bar_term_1 = self::factory()->term->create( array(
			'taxonomy' => 'bar',
		) );
		$bar_term_2 = self::factory()->term->create( array(
			'taxonomy' => 'bar',
		) );

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();
		$p4 = self::factory()->post->create();

		wp_set_object_terms( $p1, array( $foo_term_1 ), 'foo' );
		wp_set_object_terms( $p1, array( $bar_term_1 ), 'bar' );
		wp_set_object_terms( $p2, array( $foo_term_2 ), 'foo' );
		wp_set_object_terms( $p2, array( $bar_term_2 ), 'bar' );
		wp_set_object_terms( $p3, array( $foo_term_1 ), 'foo' );
		wp_set_object_terms( $p3, array( $bar_term_2 ), 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'foo',
					'terms'    => array( $foo_term_2 ),
					'field'    => 'term_id',
				),
				array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'foo',
						'terms'    => array( $foo_term_1 ),
						'field'    => 'term_id',
					),
					array(
						'relation' => 'OR',
						array(
							'taxonomy' => 'bar',
							'terms'    => array( $bar_term_1 ),
							'field'    => 'term_id',
						),
						array(
							'taxonomy' => 'bar',
							'terms'    => array( $bar_term_2 ),
							'field'    => 'term_id',
						),
					),
				),
			),
		) );

		_unregister_taxonomy( 'foo' );
		_unregister_taxonomy( 'bar' );
		$this->assertEqualSets( array( $p1, $p2, $p3 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_tax_query_relation_or_both_clauses_empty_terms() {
		// An empty tax query should return an empty array, not all posts.

		self::factory()->post->create_many( 2 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'id',
					'terms'    => false,
					'operator' => 'IN'
				),
				array(
					'taxonomy' => 'category',
					'field'    => 'id',
					'terms'    => false,
					'operator' => 'IN'
				),
			)
		) );

		$posts = $query->get_posts();
		$this->assertEquals( 0, count( $posts ) );
	}

	public function test_tax_query_relation_or_one_clause_empty_terms() {
		// An empty tax query should return an empty array, not all posts.

		self::factory()->post->create_many( 2 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'id',
					'terms'    => array( 'foo' ),
					'operator' => 'IN'
				),
				array(
					'taxonomy' => 'category',
					'field'    => 'id',
					'terms'    => false,
					'operator' => 'IN'
				),
			)
		) );

		$posts = $query->get_posts();
		$this->assertEquals( 0, count( $posts ) );
	}

	public function test_tax_query_include_children() {
		$cat_a = self::factory()->term->create( array( 'taxonomy' => 'category', 'name' => 'Australia' ) );
		$cat_b = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'name'     => 'Sydney',
			'parent'   => $cat_a
		) );
		$cat_c = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'name'     => 'East Syndney',
			'parent'   => $cat_b
		) );
		$cat_d = self::factory()->term->create( array(
			'taxonomy' => 'category',
			'name'     => 'West Syndney',
			'parent'   => $cat_b
		) );

		$post_a = self::factory()->post->create( array( 'post_category' => array( $cat_a ) ) );
		$post_b = self::factory()->post->create( array( 'post_category' => array( $cat_b ) ) );
		$post_c = self::factory()->post->create( array( 'post_category' => array( $cat_c ) ) );
		$post_d = self::factory()->post->create( array( 'post_category' => array( $cat_d ) ) );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );

		$posts = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'id',
					'terms'    => array( $cat_a ),
				)
			)
		) );


		$this->assertEquals( 4, count( $posts->posts ) );

		$posts = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy'         => 'category',
					'field'            => 'id',
					'terms'            => array( $cat_a ),
					'include_children' => false
				)
			)
		) );

		$this->assertEquals( 1, count( $posts->posts ) );

		$posts = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'id',
					'terms'    => array( $cat_b ),
				)
			)
		) );

		$this->assertEquals( 3, count( $posts->posts ) );

		$posts = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy'         => 'category',
					'field'            => 'id',
					'terms'            => array( $cat_b ),
					'include_children' => false
				)
			)
		) );

		$this->assertEquals( 1, count( $posts->posts ) );

		$posts = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'id',
					'terms'    => array( $cat_c ),
				)
			)
		) );

		$this->assertEquals( 1, count( $posts->posts ) );

		$posts = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy'         => 'category',
					'field'            => 'id',
					'terms'            => array( $cat_c ),
					'include_children' => false
				)
			)
		) );

		$this->assertEquals( 1, count( $posts->posts ) );
	}

	public function test_tax_query_no_taxonomy() {
		$cat_id = self::factory()->category->create( array( 'name' => 'alpha' ) );
		self::factory()->post->create( array( 'post_title' => 'alpha', 'post_category' => array( $cat_id ) ) );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$response1 = new WP_Query( array(
			'solr_integrate' => true,
			'tax_query'      => array(
				array( 'terms' => array( $cat_id ) )
			)
		) );
		$this->assertEmpty( $response1->posts );

		$response2 = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'terms'    => array( $cat_id )
				)
			)
		) );
		$this->assertNotEmpty( $response2->posts );

		$term      = get_category( $cat_id );
		$response3 = new WP_Query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'field' => 'term_taxonomy_id',
					'terms' => array( $term->term_taxonomy_id )
				)
			)
		) );

		$this->assertNotEmpty( $response3->posts );
	}

	public function test_term_taxonomy_id_field_no_taxonomy() {
		$q = new WP_Query();

		$posts = self::factory()->post->create_many( 5 );

		$cats = $tags = array();

		// need term_taxonomy_ids in addition to term_ids, so no factory
		for ( $i = 0; $i < 5; $i ++ ) {
			$cats[ $i ] = wp_insert_term( 'category-' . $i, 'category' );
			$tags[ $i ] = wp_insert_term( 'tag-' . $i, 'post_tag' );

			// post 0 gets all terms
			wp_set_object_terms( $posts[0], array( $cats[ $i ]['term_id'] ), 'category', true );
			wp_set_object_terms( $posts[0], array( $tags[ $i ]['term_id'] ), 'post_tag', true );
		}

		wp_set_object_terms( $posts[1], array(
			$cats[0]['term_id'],
			$cats[2]['term_id'],
			$cats[4]['term_id']
		), 'category' );
		wp_set_object_terms( $posts[1], array(
			$tags[0]['term_id'],
			$tags[2]['term_id'],
			$cats[4]['term_id']
		), 'post_tag' );

		wp_set_object_terms( $posts[2], array( $cats[1]['term_id'], $cats[3]['term_id'] ), 'category' );
		wp_set_object_terms( $posts[2], array( $tags[1]['term_id'], $tags[3]['term_id'] ), 'post_tag' );

		wp_set_object_terms( $posts[3], array(
			$cats[0]['term_id'],
			$cats[2]['term_id'],
			$cats[4]['term_id']
		), 'category' );
		wp_set_object_terms( $posts[3], array( $tags[1]['term_id'], $tags[3]['term_id'] ), 'post_tag' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$results1 = $q->query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'tax_query'              => array(
				'relation' => 'OR',
				array(
					'field'            => 'term_taxonomy_id',
					'terms'            => array(
						$cats[0]['term_taxonomy_id'],
						$cats[2]['term_taxonomy_id'],
						$cats[4]['term_taxonomy_id'],
						$tags[0]['term_taxonomy_id'],
						$tags[2]['term_taxonomy_id'],
						$cats[4]['term_taxonomy_id']
					),
					'operator'         => 'AND',
					'include_children' => false,
				),
				array(
					'field'            => 'term_taxonomy_id',
					'terms'            => array(
						$cats[1]['term_taxonomy_id'],
						$cats[3]['term_taxonomy_id'],
						$tags[1]['term_taxonomy_id'],
						$tags[3]['term_taxonomy_id']
					),
					'operator'         => 'AND',
					'include_children' => false,
				)
			)
		) );

		$this->assertEquals( array(
			$posts[0],
			$posts[1],
			$posts[2]
		), wp_list_pluck( $results1, 'ID' ), 'Relation: OR; Operator: AND' );

		$results2 = $q->query( array(
			'solr_integrate'         => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'tax_query'              => array(
				'relation' => 'AND',
				array(
					'field'            => 'term_taxonomy_id',
					'terms'            => array( $cats[0]['term_taxonomy_id'], $tags[0]['term_taxonomy_id'] ),
					'operator'         => 'IN',
					'include_children' => false,
				),
				array(
					'field'            => 'term_taxonomy_id',
					'terms'            => array( $cats[3]['term_taxonomy_id'], $tags[3]['term_taxonomy_id'] ),
					'operator'         => 'IN',
					'include_children' => false,
				)
			)
		) );

		$this->assertEquals( array(
			$posts[0],
			$posts[3]
		), wp_list_pluck( $results2, 'ID' ), 'Relation: AND; Operator: IN' );
	}

}