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
	 * Test to see if we can ping the Solr server.
	 */
	function test_solr_active() {
		$this->assertTrue( SolrPower_Api::get_instance()->ping_server() );
	}

	function test_solr_cores() {
		print_r( file_get_contents( 'http://localhost:8983/solr/admin/cores' ) );
	}

}
