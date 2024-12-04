<?php
class SolrSubmitSchemaTest extends SolrTestBase {

	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test to see if we can ping the Solr server.
	 */
	function test_solr_submit_schema() {
		$submit_response = SolrPower_Api::get_instance()->submit_schema();		
		$this->assertContains("200",$submit_response);
	}
}