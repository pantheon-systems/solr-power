<?php

class Tests_Solr_MetaQuery extends SolrTestBase {
	/**
	 * @var array Custom fields that need to be indexed in unit tests.
	 */
	var $postmeta = array(
		'foo',
		'oof',
		'bar',
		'froo',
		'baz',
		'tango',
		'vegetable',
		'color',
		'number_of_colors',
		'decimal_value',
		'bar1',
		'bar2',
		'foo2',
		'foo3',
		'foo4',
		'time',
		'city',
		'address'
	);

	function setUp() {
		parent::setUp();

		$this->__change_option( 's4wp_index_custom_fields', $this->postmeta );

	}

	public function test_meta_empty() {

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'oof', 'bar' );
		add_post_meta( $p3, 'oof', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(),
		) );
		$expected = array( $p1, $p2, $p3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}


	public function test_meta_paged() {

		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();
		$this->__create_multiple( 7 );
		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'oof', 'bar' );
		add_post_meta( $p3, 'oof', 'baz' );
		add_post_meta( $p1, 'oof', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_per_page'         => 5,
			'paged'                  => 1,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'foo',
					'value'   => 'bar',
					'compare' => '='

				),
				array(
					'key'     => 'oof',
					'value'   => 'baz',
					'compare' => '='
				)
			),
		) );

		$expected = array( $p1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_post_type() {

		$p1 = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$p2 = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$p3 = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$this->__create_multiple( 7 );
		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'oof', 'bar' );
		add_post_meta( $p3, 'oof', 'baz' );
		add_post_meta( $p1, 'oof', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_type'             => 'page',
			'post_status'            => 'publish',
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'foo',
					'value'   => 'bar',
					'compare' => '='

				),
				array(
					'key'     => 'oof',
					'value'   => 'baz',
					'compare' => '='
				)
			),
		) );

		$expected = array( $p1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_no_key() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'oof', 'bar' );
		add_post_meta( $p3, 'oof', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'value' => 'bar',
				),
			),
		) );
		$expected = array( $p1, $p2 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_no_value() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'oof', 'bar' );
		add_post_meta( $p3, 'oof', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key' => 'oof',
				),
			),
		) );

		$expected = array( $p2, $p3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );

	}

	public function test_meta_query_single_query_compare_default() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'   => 'foo',
					'value' => 'bar',
				),
			),
		) );

		$expected = array( $p1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_single_query_compare_equals() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 'bar',
					'compare' => '=',
				),
			),
		) );

		$expected = array( $p1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_single_query_compare_not_equals() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'foo', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 'bar',
					'compare' => '!=',
				),
			),
		) );
		$expected = array( $p2 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_single_query_compare_arithmetic_comparisons() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', '1' );
		add_post_meta( $p2, 'foo', '2' );
		add_post_meta( $p3, 'foo', '3' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		// <
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 2,
					'compare' => '<',
				),
			),
		) );

		$expected = array( $p1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );

		// <=
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 2,
					'compare' => '<=',
				),
			),
		) );

		$expected = array( $p1, $p2 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );

		// >=
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 2,
					'compare' => '>=',
				),
			),
		) );

		$expected = array( $p2, $p3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );

		// >
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 2,
					'compare' => '>',
				),
			),
		) );

		$expected = array( $p3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_single_query_compare_like() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 'ba',
					'compare' => 'LIKE',
				),
			),
		) );
		$expected = array( $p1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_single_query_compare_not_like() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'foo', 'rab' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => 'ba',
					'compare' => 'NOT LIKE',
				),
			),
		) );

		$expected = array( $p2 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_single_query_compare_between_not_between() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', '1' );
		add_post_meta( $p2, 'foo', '10' );
		add_post_meta( $p3, 'foo', '100' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => array( 9, 12 ),
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
				),
			),
		) );

		$expected = array( $p2 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}
		$this->assertEqualSets( $expected, $returned );

		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'value'   => array( 9, 12 ),
					'compare' => 'NOT BETWEEN',
					'type'    => 'NUMERIC',
				),
			),
		) );

		$expected = array( $p1, $p3 );


		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_default() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'foo value 1' );
		add_post_meta( $p1, 'bar', 'bar value 1' );
		add_post_meta( $p2, 'foo', 'foo value 1' );
		add_post_meta( $p2, 'bar', 'bar value 2' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key'   => 'foo',
					'value' => 'foo value 1',
				),
				array(
					'key'   => 'bar',
					'value' => 'bar value 1',
				),
			),
		) );

		$expected = array( $p1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_or() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'foo', rand_str() );
		add_post_meta( $post_id, 'foo', rand_str() );
		$post_id2 = self::factory()->post->create();
		add_post_meta( $post_id2, 'bar', 'val2' );
		$post_id3 = self::factory()->post->create();
		add_post_meta( $post_id3, 'baz', rand_str() );
		$post_id4 = self::factory()->post->create();
		add_post_meta( $post_id4, 'froo', rand_str() );
		$post_id5 = self::factory()->post->create();
		add_post_meta( $post_id5, 'tango', 'val2' );
		$post_id6 = self::factory()->post->create();
		add_post_meta( $post_id6, 'bar', 'val1' );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				array(
					'key' => 'foo'
				),
				array(
					'key'   => 'bar',
					'value' => 'val2'
				),
				array(
					'key' => 'baz'
				),
				array(
					'key' => 'froo'
				),
				'relation' => 'OR',
			),
		) );

		$expected = array( $post_id, $post_id2, $post_id3, $post_id4 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}


	public function test_meta_query_relation_and() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'foo', rand_str() );
		add_post_meta( $post_id, 'foo', rand_str() );
		$post_id2 = self::factory()->post->create();
		add_post_meta( $post_id2, 'bar', 'val2' );
		add_post_meta( $post_id2, 'foo', rand_str() );
		$post_id3 = self::factory()->post->create();
		add_post_meta( $post_id3, 'baz', rand_str() );
		$post_id4 = self::factory()->post->create();
		add_post_meta( $post_id4, 'froo', rand_str() );
		$post_id5 = self::factory()->post->create();
		add_post_meta( $post_id5, 'tango', 'val2' );
		$post_id6 = self::factory()->post->create();
		add_post_meta( $post_id6, 'bar', 'val1' );
		add_post_meta( $post_id6, 'foo', rand_str() );
		$post_id7 = self::factory()->post->create();
		add_post_meta( $post_id7, 'foo', rand_str() );
		add_post_meta( $post_id7, 'froo', rand_str() );
		add_post_meta( $post_id7, 'baz', rand_str() );
		add_post_meta( $post_id7, 'bar', 'val2' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		sleep( 1 );
		$query = new WP_Query( array(
			'meta_query'             => array(
				array(
					'key' => 'foo'
				),
				array(
					'key'   => 'bar',
					'value' => 'val2'
				),
				array(
					'key' => 'baz'
				),
				array(
					'key' => 'froo'
				),
				'relation' => 'AND',
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'solr_integrate'         => true,
		) );

		$expected = array( $post_id7 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query = new WP_Query( array(
			'meta_query'             => array(
				array(
					'key' => 'foo'
				),
				array(
					'key' => 'bar',
				),
				'relation' => 'AND',
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'solr_integrate'         => true,
		) );

		$expected = array( $post_id2, $post_id6, $post_id7 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_compare_exists() {
		$posts = self::factory()->post->create_many( 3 );
		add_post_meta( $posts[0], 'foo', 'bar' );
		add_post_meta( $posts[2], 'foo', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'compare' => 'EXISTS',
					'key'     => 'foo',
				),
			),
		) );
		$expected = array( $posts[0], $posts[2] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_compare_exists_with_value_should_convert_to_equals() {
		$posts = self::factory()->post->create_many( 3 );
		add_post_meta( $posts[0], 'foo', 'bar' );
		add_post_meta( $posts[2], 'foo', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'compare' => 'EXISTS',
					'value'   => 'baz',
					'key'     => 'foo',
				),
			),
		) );
		$expected = array( $posts[2] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_compare_not_exists_should_ignore_value() {
		$posts = self::factory()->post->create_many( 3 );
		add_post_meta( $posts[0], 'foo', 'bar' );
		add_post_meta( $posts[2], 'foo', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'compare' => 'NOT EXISTS',
					'value'   => 'bar',
					'key'     => 'foo',
				),
			),
		) );

		$expected = array( $posts[1] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}


	public function test_meta_query_compare_not_exists() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'foo', rand_str() );
		$post_id2 = self::factory()->post->create();
		add_post_meta( $post_id2, 'bar', rand_str() );
		$post_id3 = self::factory()->post->create();
		add_post_meta( $post_id3, 'bar', rand_str() );
		$post_id4 = self::factory()->post->create();
		add_post_meta( $post_id4, 'baz', rand_str() );
		$post_id5 = self::factory()->post->create();
		add_post_meta( $post_id5, 'foo', rand_str() );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'compare' => 'NOT EXISTS',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $post_id2, $post_id3, $post_id4 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'bar',
					'compare' => 'NOT EXISTS',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $post_id4 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'foo',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'bar',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'baz',
					'compare' => 'NOT EXISTS',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$this->assertEquals( 0, count( $query->posts ) );
	}

	public function test_meta_query_relation_or_compare_equals() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => 'vegetable',
					'value'   => 'onion',
					'compare' => '=',
				),
				array(
					'key'     => 'vegetable',
					'value'   => 'shallot',
					'compare' => '=',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[1], $posts[2] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

	}


	public function test_meta_query_relation_or_compare_equals_and_in() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => 'vegetable',
					'value'   => 'onion',
					'compare' => '=',
				),
				array(
					'key'     => 'color',
					'value'   => array( 'orange', 'green' ),
					'compare' => 'IN',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[0], $posts[1] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_or_compare_equals_and_like() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => 'vegetable',
					'value'   => 'onion',
					'compare' => '=',
				),
				array(
					'key'     => 'vegetable',
					'value'   => 'hall',
					'compare' => 'LIKE',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[1], $posts[2] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_or_compare_equals_and_between() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'number_of_colors', '2' );
		add_post_meta( $posts[1], 'number_of_colors', '5' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => 'vegetable',
					'value'   => 'shallot',
					'compare' => '=',
				),
				array(
					'key'     => 'number_of_colors',
					'value'   => array( 1, 3 ),
					'compare' => 'BETWEEN',
					'type'    => 'SIGNED',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[0], $posts[2] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_and_compare_in_same_keys() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		add_post_meta( $posts[3], 'vegetable', 'banana' );
		add_post_meta( $posts[3], 'vegetable', 'onion' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'vegetable',
					'value'   => array( 'onion', 'shallot' ),
					'compare' => 'IN',
				),
				array(
					'key'     => 'vegetable',
					'value'   => array( 'banana' ),
					'compare' => 'IN',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[3] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_and_compare_in_different_keys() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[1], 'vegetable', 'shallot' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		add_post_meta( $posts[3], 'vegetable', 'banana' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'vegetable',
					'value'   => array( 'onion', 'shallot' ),
					'compare' => 'IN',
				),
				array(
					'key'     => 'color',
					'value'   => array( 'blue' ),
					'compare' => 'IN',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[1] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_and_compare_not_equals() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		add_post_meta( $posts[3], 'vegetable', 'banana' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'vegetable',
					'value'   => 'onion',
					'compare' => '!=',
				),
				array(
					'key'     => 'vegetable',
					'value'   => 'shallot',
					'compare' => '!=',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[3] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_and_compare_not_equals_different_keys() {
		$posts = self::factory()->post->create_many( 4 );

		// !shallot, but orange.
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[0], 'vegetable', 'onion' );

		// !orange, but shallot.
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'shallot' );

		// Neither.
		add_post_meta( $posts[2], 'color', 'blue' );
		add_post_meta( $posts[2], 'vegetable', 'onion' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'vegetable',
					'value'   => 'shallot',
					'compare' => '!=',
				),
				array(
					'key'     => 'color',
					'value'   => 'orange',
					'compare' => '!=',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[2] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_and_compare_not_equals_not_in() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		add_post_meta( $posts[3], 'vegetable', 'banana' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'vegetable',
					'value'   => 'onion',
					'compare' => '!=',
				),
				array(
					'key'     => 'vegetable',
					'value'   => array( 'shallot' ),
					'compare' => 'NOT IN',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[3] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_relation_and_compare_not_equals_and_not_like() {
		$posts = self::factory()->post->create_many( 4 );
		add_post_meta( $posts[0], 'color', 'orange' );
		add_post_meta( $posts[1], 'color', 'blue' );
		add_post_meta( $posts[1], 'vegetable', 'onion' );
		add_post_meta( $posts[2], 'vegetable', 'shallot' );
		add_post_meta( $posts[3], 'vegetable', 'banana' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'vegetable',
					'value'   => 'onion',
					'compare' => '!=',
				),
				array(
					'key'     => 'vegetable',
					'value'   => 'hall',
					'compare' => 'NOT LIKE',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[3] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}


	public function test_meta_query_decimal_results() {
		$post_1 = self::factory()->post->create();
		$post_2 = self::factory()->post->create();
		$post_3 = self::factory()->post->create();
		$post_4 = self::factory()->post->create();

		update_post_meta( $post_1, 'decimal_value', '-0.3' );
		update_post_meta( $post_2, 'decimal_value', '0.23409844' );
		update_post_meta( $post_3, 'decimal_value', '0.3' );
		update_post_meta( $post_4, 'decimal_value', '0.4' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => '.300',
					'compare' => '=',
					'type'    => 'DECIMAL(10,2)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $post_3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => '0.35',
					'compare' => '>',
					'type'    => 'DECIMAL(10,2)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $post_4 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => '0.3',
					'compare' => '>=',
					'type'    => 'DECIMAL(10,2)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );


		$expected = array( $post_3, $post_4 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => '0',
					'compare' => '<',
					'type'    => 'DECIMAL(10,2)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $post_1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => '0.3',
					'compare' => '<=',
					'type'    => 'DECIMAL(10,2)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $post_1, $post_2, $post_3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => array( 0.23409845, .31 ),
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(10, 10)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $post_3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => array( 0.23409845, .31 ),
					'compare' => 'NOT BETWEEN',
					'type'    => 'DECIMAL(10,10)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $post_1, $post_2, $post_4 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => '.3',
					'compare' => 'LIKE',
					'type'    => 'DECIMAL(10,2)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $post_1, $post_3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				array(
					'key'     => 'decimal_value',
					'value'   => '.3',
					'compare' => 'NOT LIKE',
					'type'    => 'DECIMAL(10,2)'
				)
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $post_2, $post_4 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'orderby'                => 'meta_value',
			'order'                  => 'DESC',
			'meta_key'               => 'decimal_value',
			'meta_type'              => 'DECIMAL(10, 2)',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $post_4, $post_3, $post_2, $post_1 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}


	public function test_meta_vars_should_be_converted_to_meta_query() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', '5' );
		add_post_meta( $p2, 'foo', '7' );
		add_post_meta( $p3, 'foo', '10' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate' => true,
			'meta_key'       => 'foo',
			'meta_value'     => '5',
			'meta_compare'   => '>',
			'meta_type'      => 'SIGNED',
		) );

		$expected = array( $p2, $p3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_with_orderby_meta_value_relation_or() {
		$posts = self::factory()->post->create_many( 4 );
		update_post_meta( $posts[0], 'foo', 5 );
		update_post_meta( $posts[1], 'foo', 6 );
		update_post_meta( $posts[2], 'foo', 4 );
		update_post_meta( $posts[3], 'foo', 7 );

		update_post_meta( $posts[0], 'bar1', 'baz' );
		update_post_meta( $posts[1], 'bar1', 'baz' );
		update_post_meta( $posts[2], 'bar2', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'orderby'                => 'meta_value',
			'order'                  => 'ASC',
			'meta_key'               => 'foo',
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => 'bar1',
					'value'   => 'baz',
					'compare' => '=',
				),
				array(
					'key'     => 'bar2',
					'value'   => 'baz',
					'compare' => '=',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[2], $posts[0], $posts[1] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_with_orderby_meta_value_relation_and() {
		$posts = self::factory()->post->create_many( 4 );
		update_post_meta( $posts[0], 'foo', 5 );
		update_post_meta( $posts[1], 'foo', 6 );
		update_post_meta( $posts[2], 'foo', 4 );
		update_post_meta( $posts[3], 'foo', 7 );

		update_post_meta( $posts[0], 'bar1', 'baz' );
		update_post_meta( $posts[1], 'bar1', 'baz' );
		update_post_meta( $posts[2], 'bar1', 'baz' );
		update_post_meta( $posts[3], 'bar1', 'baz' );
		update_post_meta( $posts[0], 'bar2', 'baz' );
		update_post_meta( $posts[1], 'bar2', 'baz' );
		update_post_meta( $posts[2], 'bar2', 'baz' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query    = new WP_Query( array(
			'solr_integrate'         => true,
			'orderby'                => 'meta_value',
			'order'                  => 'ASC',
			'meta_key'               => 'foo',
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => 'bar1',
					'value'   => 'baz',
					'compare' => '=',
				),
				array(
					'key'     => 'bar2',
					'value'   => 'baz',
					'compare' => '=',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );
		$expected = array( $posts[2], $posts[0], $posts[1] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_nested() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p2, 'foo2', 'bar' );
		add_post_meta( $p3, 'foo2', 'bar' );
		add_post_meta( $p3, 'foo3', 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_term_meta_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'   => 'foo',
					'value' => 'bar',
				),
				array(
					'relation' => 'AND',
					array(
						'key'   => 'foo2',
						'value' => 'bar',
					),
					array(
						'key'   => 'foo3',
						'value' => 'bar',
					),
				),
			),
		) );

		$expected = array( $p1, $p3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_query_nested_two_levels_deep() {
		$p1 = self::factory()->post->create();
		$p2 = self::factory()->post->create();
		$p3 = self::factory()->post->create();

		add_post_meta( $p1, 'foo', 'bar' );
		add_post_meta( $p3, 'foo2', 'bar' );
		add_post_meta( $p3, 'foo3', 'bar' );
		add_post_meta( $p3, 'foo4', 'bar' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'update_post_meta_cache' => false,
			'update_term_meta_cache' => false,
			'fields'                 => 'ids',
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'   => 'foo',
					'value' => 'bar',
				),
				array(
					'relation' => 'OR',
					array(
						'key'   => 'foo2',
						'value' => 'bar',
					),
					array(
						'relation' => 'AND',
						array(
							'key'   => 'foo3',
							'value' => 'bar',
						),
						array(
							'key'   => 'foo4',
							'value' => 'bar',
						),
					),
				),
			),
		) );

		$expected = array( $p1, $p3 );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_between_not_between() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'time', 500 );
		$post_id2 = self::factory()->post->create();
		add_post_meta( $post_id2, 'time', 1001 );
		$post_id3 = self::factory()->post->create();
		add_post_meta( $post_id3, 'time', 0 );
		$post_id4 = self::factory()->post->create();
		add_post_meta( $post_id4, 'time', 1 );
		$post_id5 = self::factory()->post->create();
		add_post_meta( $post_id5, 'time', 1000 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args = array(
			'solr_integrate' => true,
			'meta_key'       => 'time',
			'meta_value'     => array( 1, 1000 ),
			'meta_type'      => 'numeric',
			'meta_compare'   => 'NOT BETWEEN'
		);

		$query = new WP_Query( $args );

		$this->assertEquals( 2, count( $query->posts ) );
		foreach ( $query->posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$posts = wp_list_pluck( $query->posts, 'ID' );

		$this->assertEqualSets( array( $post_id2, $post_id3 ), $posts );

		$args = array(
			'solr_integrate' => true,
			'meta_key'       => 'time',
			'meta_value'     => array( 1, 1000 ),
			'meta_type'      => 'numeric',
			'meta_compare'   => 'BETWEEN'
		);

		$query = new WP_Query( $args );
		$this->assertEquals( 3, count( $query->posts ) );
		foreach ( $query->posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$returned = wp_list_pluck( $query->posts, 'ID' );

		$expected = array( $post_id, $post_id4, $post_id5 );


		$this->assertEqualSets( $expected, $returned );
	}

	public function test_meta_default_compare() {
		// compare should default to IN when meta_value is an array
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'foo', 'bar' );
		$post_id2 = self::factory()->post->create();
		add_post_meta( $post_id2, 'bar', 'baz' );
		$post_id3 = self::factory()->post->create();
		add_post_meta( $post_id3, 'foo', 'baz' );
		$post_id4 = self::factory()->post->create();
		add_post_meta( $post_id4, 'baz', 'bar' );
		$post_id5 = self::factory()->post->create();
		add_post_meta( $post_id5, 'foo', rand_str() );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$posts = get_posts( array(
			'solr_integrate' => true,
			'meta_key'       => 'foo',
			'meta_value'     => array( 'bar', 'baz' )
		) );

		$this->assertEquals( 2, count( $posts ) );
		$posts = wp_list_pluck( $posts, 'ID' );
		$this->assertEqualSets( array( $post_id, $post_id3 ), $posts );

		$posts = get_posts( array(
			'solr_integrate' => true,
			'meta_key'       => 'foo',
			'meta_value'     => array( 'bar', 'baz' ),
			'meta_compare'   => 'IN'
		) );

		$this->assertEquals( 2, count( $posts ) );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$posts = wp_list_pluck( $posts, 'ID' );
		$this->assertEqualSets( array( $post_id, $post_id3 ), $posts );
	}

	public function test_duplicate_posts_when_no_key() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'city', 'Lorem' );
		add_post_meta( $post_id, 'address', '123 Lorem St.' );
		$post_id2 = self::factory()->post->create();
		add_post_meta( $post_id2, 'city', 'Lorem' );
		$post_id3 = self::factory()->post->create();
		add_post_meta( $post_id3, 'city', 'Loren' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$args = array(
			'solr_integrate' => true,
			'meta_query'     => array(
				array(
					'value'   => 'lorem',
					'compare' => 'LIKE'
				)
			)
		);

		$posts = get_posts( $args );
		$this->assertEquals( 2, count( $posts ) );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$posts = wp_list_pluck( $posts, 'ID' );
		$this->assertEqualSets( array( $post_id, $post_id2 ), $posts );
	}

	public function test_empty_meta_value() {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, 'foo', '0' );
		add_post_meta( $post_id, 'bar', 0 );
		$post_id2 = self::factory()->post->create();
		add_post_meta( $post_id2, 'foo', 1 );
		$post_id3 = self::factory()->post->create();
		add_post_meta( $post_id3, 'baz', 0 );
		$post_id4 = self::factory()->post->create();
		add_post_meta( $post_id4, 'baz', 0 );
		$post_id5 = self::factory()->post->create();
		add_post_meta( $post_id5, 'baz', 0 );
		add_post_meta( $post_id5, 'bar', '0' );
		$post_id6 = self::factory()->post->create();
		add_post_meta( $post_id6, 'baz', 0 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array( 'solr_integrate' => true, 'meta_key' => 'foo', 'meta_value' => '0' ) );
		$this->assertEquals( 1, count( $q->posts ) );
		foreach ( $q->posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$this->assertEquals( $post_id, $q->posts[0]->ID );

		$posts = get_posts( array( 'solr_integrate' => true, 'meta_key' => 'bar', 'meta_value' => '0' ) );
		$this->assertEquals( 2, count( $posts ) );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$posts = wp_list_pluck( $posts, 'ID' );
		$this->assertEqualSets( array( $post_id, $post_id5 ), $posts );

		$posts = get_posts( array( 'solr_integrate' => true, 'meta_key' => 'bar', 'meta_value' => 0 ) );
		$this->assertEquals( 2, count( $posts ) );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$posts = wp_list_pluck( $posts, 'ID' );
		$this->assertEqualSets( array( $post_id, $post_id5 ), $posts );

		$posts = get_posts( array( 'solr_integrate' => true, 'meta_value' => 0 ) );
		$this->assertEquals( 5, count( $posts ) );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$posts = wp_list_pluck( $posts, 'ID' );
		$this->assertEqualSets( array( $post_id, $post_id3, $post_id4, $post_id5, $post_id6 ), $posts );

		$posts = get_posts( array( 'solr_integrate' => true, 'meta_value' => '0' ) );
		$this->assertEquals( 5, count( $posts ) );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( 'WP_Post', $post );
			$this->assertEquals( 'raw', $post->filter );
		}
		$posts = wp_list_pluck( $posts, 'ID' );
		$this->assertEqualSets( array( $post_id, $post_id3, $post_id4, $post_id5, $post_id6 ), $posts );
	}

	public function test_orderby_clause_key() {
		$posts = self::factory()->post->create_many( 3 );
		add_post_meta( $posts[0], 'foo', 'aaa' );
		add_post_meta( $posts[1], 'foo', 'zzz' );
		add_post_meta( $posts[2], 'foo', 'jjj' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				'foo_key' => array(
					'key'     => 'foo',
					'compare' => 'EXISTS',
				),
			),
			'orderby'        => 'foo_key',
			'order'          => 'DESC',
		) );

		$this->assertEquals( array( $posts[1], $posts[2], $posts[0] ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_orderby_clause_key_as_secondary_sort() {
		$p1 = self::factory()->post->create( array(
			'post_date' => '2015-01-28 03:00:00',
		) );
		$p2 = self::factory()->post->create( array(
			'post_date' => '2015-01-28 05:00:00',
		) );
		$p3 = self::factory()->post->create( array(
			'post_date' => '2015-01-28 03:00:00',
		) );

		add_post_meta( $p1, 'foo', 'jjj' );
		add_post_meta( $p2, 'foo', 'zzz' );
		add_post_meta( $p3, 'foo', 'aaa' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				'foo_key' => array(
					'key'     => 'foo',
					'compare' => 'EXISTS',
				),
			),
			'orderby'        => array(
				'post_date' => 'asc',
				'foo_key'   => 'asc',
			),
		) );

		$this->assertEquals( array( $p3, $p1, $p2 ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_orderby_more_than_one_clause_key() {
		$posts = self::factory()->post->create_many( 3 );

		add_post_meta( $posts[0], 'foo', 'jjj' );
		add_post_meta( $posts[1], 'foo', 'zzz' );
		add_post_meta( $posts[2], 'foo', 'jjj' );
		add_post_meta( $posts[0], 'bar', 'aaa' );
		add_post_meta( $posts[1], 'bar', 'ccc' );
		add_post_meta( $posts[2], 'bar', 'bbb' );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$q = new WP_Query( array(
			'solr_integrate' => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				'foo_key' => array(
					'key'     => 'foo',
					'compare' => 'EXISTS',
				),
				'bar_key' => array(
					'key'     => 'bar',
					'compare' => 'EXISTS',
				),
			),
			'orderby'        => array(
				'foo_key' => 'asc',
				'bar_key' => 'desc',
			),
		) );

		$this->assertEquals( array( $posts[2], $posts[0], $posts[1] ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_meta_query_compare_not_exists_with_another_condition_relation_or() {
		$posts = self::factory()->post->create_many( 4 );
		update_post_meta( $posts[0], 'color', 'orange' );
		update_post_meta( $posts[1], 'color', 'blue' );
		update_post_meta( $posts[1], 'vegetable', 'onion' );
		update_post_meta( $posts[2], 'vegetable', 'shallot' );

		$post_3_meta = get_post_meta( $posts[3] );
		foreach ( $post_3_meta as $meta_key => $meta_value ) {
			delete_post_meta( $posts[3], $meta_key );
		}
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$query = new WP_Query( array(
			'solr_integrate'         => true,
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'   => 'vegetable',
					'value' => 'onion',
				),
				array(
					'key'     => 'color',
					'compare' => 'NOT EXISTS',
				),
			),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		) );

		$expected = array( $posts[2], $posts[3] );
		$returned = array();
		foreach ( $query->posts as $post ) {
			$returned[] = $post->ID;
		}

		$this->assertEqualSets( $expected, $returned );

	}

}
