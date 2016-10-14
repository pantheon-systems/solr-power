<?php

class SolrTest extends SolrTestBase {

	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test to see if we can ping the Solr server.
	 */
	function test_solr_active() {
		$this->assertTrue( SolrPower_Api::get_instance()->ping_server() );
	}


	function test_wildcard_search() {

		$this->__create_multiple( 5 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$search = $this->__run_test_query( '*:*' );


		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search = $search->getData();
		$search = $search['response'];

		$this->assertEquals( 5, $search['numFound'] );
	}

	/**
	 * Create a post and see if it gets indexed.
	 */
	function test_index_post() {
		$post_id = $this->__create_test_post();
		$search  = $this->__run_test_query();
		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search = $search->getData();
		$search = $search['response'];

		$this->assertEquals( $search['docs'][0]['ID'], $post_id );
	}

	/**
	 * Delete a post and see if it gets removed from index.
	 */
	function test_delete_post() {
		$post_id = $this->__create_test_post();
		wp_delete_post( $post_id );
		$search = $this->__run_test_query();
		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search = $search->getData();
		$search = $search['response'];
		// We should have zero results.
		$this->assertEquals( $search['numFound'], 0 );
	}

	/**
	 * Change post status to draft and see if it gets removed from index.
	 */
	function test_post_status_change() {
		$post_id = $this->__create_test_post();
		// Let's transition the post status from published to draft
		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
		$search = $this->__run_test_query();
		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search = $search->getData();
		$search = $search['response'];
		// We should have zero results.
		$this->assertEquals( $search['numFound'], 0 );
	}

	/**
	 * Tests to see if Solr indexes all posts.
	 * @group 43
	 * @link https://github.com/pantheon-systems/solr-power/issues/43
	 */
	function test_index_all_posts() {
		// Create 20 posts:
		$this->__create_multiple( 20 );
		// Delete index.
		SolrPower_Sync::get_instance()->delete_all();
		// Index all posts:
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );

		$search = $this->__run_test_query( 'post' );
		if ( is_null( $search ) ) {
			$this->assertTrue( false );
		}
		$search = $search->getData();
		$search = $search['response'];

		// We should have 20 results.
		$this->assertEquals( absint( $search['numFound'] ), 20 );
	}

	/**
	 * Tests to see if indexing stats work.
	 * @group 40
	 * @link https://github.com/pantheon-systems/solr-power/issues/40
	 */
	function test_index_stats() {
		$this->__create_test_post( 'page' );
		$this->__create_test_post( 'page' );
		$this->__create_multiple( 5 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 2, $stats['page'] );
		$this->assertEquals( 5, $stats['post'] );
	}

	/**
	 * Tests to see if indexing stats work upon delete all.
	 * @group 40
	 * @link https://github.com/pantheon-systems/solr-power/issues/40
	 */
	function test_index_stats_on_delete_all() {
		$this->__create_test_post( 'page' );
		$this->__create_test_post( 'page' );
		$this->__create_multiple( 5 );

		// Delete all of these newly indexed items:
		SolrPower_Sync::get_instance()->delete_all();

		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 0, $stats['page'] );
		$this->assertEquals( 0, $stats['post'] );
	}

	/**
	 * Tests to see if indexing stats work upon delete.
	 * @group 40
	 * @link https://github.com/pantheon-systems/solr-power/issues/40
	 */
	function test_index_stats_on_delete() {
		$delete_id = $this->__create_test_post( 'page' );
		$this->__create_test_post( 'page' );
		$this->__create_multiple( 5 );

		if ( is_multisite() ) {
			$blogid    = get_current_blog_id();
			$delete_id = $blogid . '_' . $delete_id;
		}
		// Delete all of these newly indexed items:
		SolrPower_Sync::get_instance()->delete( $delete_id );

		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 1, $stats['page'] );
		$this->assertEquals( 5, $stats['post'] );
	}

	/**
	 * Test to see if facets are being returned from search.
	 * @group 37
	 */
	function test_facets() {
		$this->__create_multiple( 5 );
		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );
		$this->__facet_query();
		$facets = SolrPower_WP_Query::get_instance()->facets;
		$this->assertNotEmpty( $facets );
	}

	/**
	 * Test to make custom field a facet.
	 * @group 37
	 */
	function test_custom_field_facet() {
		// Set 'my_field' as a custom field facet.
		$this->__change_option( 's4wp_facet_on_custom_fields', array( 'my_field' ) );
		$this->__change_option( 's4wp_index_custom_fields', array( 'my_field' ) );

		$this->__setup_custom_fields();
		$this->__facet_query();
		$facets = SolrPower_WP_Query::get_instance()->facets;

		$this->assertNotEmpty( $facets );
		$this->assertArrayHasKey( 'my_field_str', $facets );
		$this->assertArrayNotHasKey( 'other_field_str', $facets );

		$data = $facets['my_field_str']->getValues();
		$this->assertArrayHasKey( 'my_value', $data );
		$this->assertArrayHasKey( 'my_value2', $data );
		$this->assertArrayHasKey( 'my_value3', $data );
	}

	/**
	 * Perform a search with a facet set.
	 * @group 37
	 */
	function test_cf_facet_search() {
		// Set 'my_field' as a custom field facet.
		$this->__change_option( 's4wp_facet_on_custom_fields', array( 'my_field' ) );
		$this->__change_option( 's4wp_index_custom_fields', array( 'my_field' ) );
		$this->__setup_custom_fields();


		$query = $this->__facet_query( array(
			'facet' => array(
				'my_field_str' => array( 'my_value' )
			)
		) );

		$this->assertEquals( $query->post_count, 2 );
		$this->assertEquals( $query->found_posts, 2 );

	}

	/**
	 * Test of filter that adds a custom field to index and facet.
	 * @group 37
	 */
	function test_cf_facet_filter() {

		add_filter( 'solr_index_custom_fields', function ( $fields = array() ) {
			$fields[] = 'my_field';

			return $fields;
		} );

		add_filter( 'solr_facet_custom_fields', function ( $fields = array() ) {
			$fields[] = 'my_field';

			return $fields;
		} );
		$this->__setup_custom_fields();


		$query = $this->__facet_query( array(
			'facet' => array(
				'my_field_str' => array( 'my_value' )
			)
		) );

		$this->assertEquals( $query->post_count, 2 );
		$this->assertEquals( $query->found_posts, 2 );
	}

	/**
	 * Test to see if a two facets can be selected.
	 * @group 37
	 */
	function test_multi_facet() {

		$this->__change_option( 's4wp_facet_on_custom_fields', array( 'my_field' ) );
		$this->__change_option( 's4wp_index_custom_fields', array( 'my_field' ) );

		$cat_id_one = wp_create_category( 'new_cat' );
		$cat_id_two = wp_create_category( 'smelly_cat' );

		$p_id = $this->__create_test_post();
		update_post_meta( $p_id, 'my_field', 'my_value' );
		wp_set_object_terms( $p_id, $cat_id_one, 'category', true );


		$p_id_two = $this->__create_test_post();

		wp_set_object_terms( $p_id_two, $cat_id_two, 'category', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );

		$query = $this->__facet_query( array(
			'facet' => array(
				'my_field_str' => array( 'my_value' ),
				'categories'   => array( 'smelly_cat^^' )
			)
		) );

		$this->assertEquals( 2, $query->post_count );
		$this->assertEquals( 2, $query->found_posts );

		wp_delete_category( $cat_id_one );
		wp_delete_category( $cat_id_two );
	}

	/**
	 * Test to see if a two facets can be selected.
	 * @group 37
	 */
	function test_facet_category_ampersand() {
		$cat_name = 'Tests & More Tests';
		$cat_id   = wp_create_category( $cat_name );

		$p_id = $this->__create_test_post();

		wp_set_object_terms( $p_id, $cat_id, 'category', true );


		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );

		$query = $this->__facet_query( array(
			'facet' => array(
				'categories' => array( $cat_name . '^^' )
			)
		) );

		$this->assertEquals( 1, $query->post_count );
		$this->assertEquals( 1, $query->found_posts );

		wp_delete_category( $cat_id );

	}

	/**
	 * Test to see if a two facets of the same type can be selected.
	 * @group 37
	 */
	function test_multi_same_facet() {

		$cat_id_one   = wp_create_category( 'new_cat' );
		$cat_id_two   = wp_create_category( 'smelly_cat' );
		$cat_id_three = wp_create_category( 'bad_cat' );

		$p_id = $this->__create_test_post();
		wp_set_object_terms( $p_id, $cat_id_one, 'category', true );


		$p_id_two = $this->__create_test_post();
		wp_set_object_terms( $p_id_two, $cat_id_two, 'category', true );

		$p_id_three = $this->__create_test_post();
		wp_set_object_terms( $p_id_three, $cat_id_three, 'category', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );

		$query = $this->__facet_query( array(
			'facet' => array(
				'my_field_str' => array( 'my_value' ),
				'categories'   => array(
					'smelly_cat^^',
					'bad_cat^^',
				)
			)
		) );


		$this->assertEquals( 2, $query->post_count );
		$this->assertEquals( 2, $query->found_posts );
		$ids = array();
		foreach ( $query->posts as $post ) {
			$ids[] = $post->ID;
		}
		$this->assertContains( $p_id_two, $ids );
		$this->assertContains( $p_id_three, $ids );
		$this->assertNotContains( $p_id, $ids );

		wp_delete_category( $cat_id_one );
		wp_delete_category( $cat_id_two );
	}

	/**
	 * Test to see if a two facets can be selected with the AND operator.
	 * Should yield zero results.
	 * @group 37
	 */
	function test_multi_facet_and() {

		$this->__change_option( 's4wp_default_operator', 'AND' );
		$this->__change_option( 's4wp_facet_on_custom_fields', array( 'my_field' ) );
		$this->__change_option( 's4wp_index_custom_fields', array( 'my_field' ) );

		$cat_id_one = wp_create_category( 'new_cat' );
		$cat_id_two = wp_create_category( 'smelly_cat' );

		$p_id = $this->__create_test_post();
		update_post_meta( $p_id, 'my_field', 'my_value' );
		wp_set_object_terms( $p_id, $cat_id_one, 'category', true );


		$p_id_two = $this->__create_test_post();

		wp_set_object_terms( $p_id_two, $cat_id_two, 'category', true );

		SolrPower_Sync::get_instance()->load_all_posts( 0, 'post', 100, false );

		$query = $this->__facet_query( array(
			'facet' => array(
				'my_field_str' => array( 'my_value' ),
				'categories'   => array( 'smelly_cat^^' )
			)
		) );


		// This should return zero results because the result cannot contain both the custom field and the category.
		$this->assertEquals( 0, $query->post_count );
		$this->assertEquals( 0, $query->found_posts );

		wp_delete_category( $cat_id_one );
		wp_delete_category( $cat_id_two );
	}

}
