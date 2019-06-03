<?php

class SolrPowerAPITest extends SolrTestBase {

	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
	}


	function test_server_info_uses_actual_config() {
		$env_host      = getenv('PANTHEON_INDEX_HOST');
		$env_port      = getenv('PANTHEON_INDEX_PORT');
		$override_host = $env_host . 'OVERRIDE';
		$override_port = $env_host . 'OVERRIDE';

		$override_connection = function() {
			return array(
				'endpoint' => array(
					'localhost' => array(
						'host' => $override_host,
						'port' => $override_port,
						'path' => '',
					)
				),
			);
		};
		add_filter( 's4wp_connection_options', $override_connection );

		$server_info = SolrPower_Api::get_instance()->get_server_info();

		$this->assertEquals( $override_host, $server_info['ip_address'] );
		$this->assertEquals( $override_port, $server_info['port'] );

		remove_filter( 's4wp_connection_options', $override_connection );
	}
}