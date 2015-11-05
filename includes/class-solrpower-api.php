<?php

class SolrPower_Api {

	/**
	 * Singleton instance
	 * @var SolrPress_Api|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPress_Api
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		
	}

	function call() {
		
	}

	function submit_schema() {
		// Solarium does not currently support submitting schemas to the server.
		// So we'll do it ourselves

		$returnValue = '';
		$upload_dir	 = wp_upload_dir();

		// Let's check for a custom Schema.xml. It MUST be located in
		// wp-content/uploads/solr-for-wordpress-on-pantheon/schema.xml
		if ( is_file( realpath( ABSPATH ) . '/' . $_ENV[ 'FILEMOUNT' ] . '/solr-for-wordpress-on-pantheon/schema.xml' ) ) {
			$schema = realpath( ABSPATH ) . '/' . $_ENV[ 'FILEMOUNT' ] . '/solr-for-wordpress-on-pantheon/schema.xml';
		} else {
			$schema = dirname( __FILE__ ) . '/schema.xml';
		}

		$path		 = $this->compute_path();
		$url		 = 'https://' . getenv( 'PANTHEON_INDEX_HOST' ) . ':' . getenv( 'PANTHEON_INDEX_PORT' ) . '/' . $path;
		$client_cert = realpath( ABSPATH . '../certs/binding.pem' );

		/*
		 * A couple of quick checks to make sure eveyrthing seems sane
		 */
		if ( $errorMessage = SolrPower::get_instance()->sanity_check() ) {
			return $errorMessage;
		}

		if ( !file_exists( $schema ) ) {
			return $schema . ' does not exist.';
		}

		if ( !file_exists( $client_cert ) ) {
			return $client_cert . ' does not exist.';
		}


		$file	 = fopen( $schema, 'r' );
		// set URL and other appropriate options
		$opts	 = array(
			CURLOPT_URL				 => $url,
			CURLOPT_PORT			 => 449,
			CURLOPT_RETURNTRANSFER	 => 1,
			CURLOPT_SSLCERT			 => $client_cert,
			CURLOPT_SSL_VERIFYPEER	 => false,
			CURLOPT_HTTPHEADER		 => array( 'Content-type:text/xml; charset=utf-8' ),
			CURLOPT_PUT				 => TRUE,
			CURLOPT_BINARYTRANSFER	 => 1,
			CURLOPT_INFILE			 => $file,
			CURLOPT_INFILESIZE		 => filesize( $schema ),
		);

		$ch = curl_init();
		curl_setopt_array( $ch, $opts );

		$response	 = curl_exec( $ch );
		$curl_opts	 = curl_getinfo( $ch );
		fclose( $file );
		$returnValue = (int) $curl_opts[ 'http_code' ];
		if ( (int) $curl_opts[ 'http_code' ] == 200 ) {
			$returnValue = 'Schema Upload Success: ' . $curl_opts[ 'http_code' ];
		} else {
			$returnValue = 'Schema Upload Error: ' . $curl_opts[ 'http_code' ];
		}
		return $returnValue;
	}

	/**
	 * build the path that the Solr server uses
	 * @return string
	 */
	function compute_path() {
		return '/sites/self/environments/' . getenv( 'PANTHEON_ENVIRONMENT' ) . '/index';
	}

	/**
	 * check if the server by pinging it
	 * @return boolean
	 */
	function ping_server() {
		$solr = get_solr();
		try {
			$solr->ping( $solr->createPing() );
			return true;
		} catch ( Solarium\Exception $e ) {
			return false;
		}
	}

	/**
	 * Connect to the solr service
	 * @return solr service object
	 */
	function get_solr() {
		# get the connection options
		$plugin_s4wp_settings = solr_options();

		$solarium_config = array(
			'endpoint' => array(
				'localhost' => array(
					'host'	 => getenv( 'PANTHEON_INDEX_HOST' ),
					'port'	 => getenv( 'PANTHEON_INDEX_PORT' ),
					'scheme' => 'https',
					'path'	 => $this->compute_path(),
					'ssl'	 => array( 'local_cert' => realpath( ABSPATH . '../certs/binding.pem' ) )
				)
			)
		);

		apply_filters( 's4wp_connection_options', $solarium_config );


		# double check everything has been set
		if ( !($solarium_config[ 'endpoint' ][ 'localhost' ][ 'host' ] and
		$solarium_config[ 'endpoint' ][ 'localhost' ][ 'port' ] and
		$solarium_config[ 'endpoint' ][ 'localhost' ][ 'path' ]) ) {
			syslog( LOG_ERR, "host, port or path are empty, host:$host, port:$port, path:$path" );
			return NULL;
		}


		$solr = new Solarium\Client( $solarium_config );

		apply_filters( 's4wp_solr', $solr ); // better name?
		return $solr;
	}

	function optimize() {
		try {
			$solr = get_solr();
			if ( !$solr == NULL ) {
				$update = $solr->createUpdate();
				$update->addOptimize();
				$solr->update( $update );
			}
		} catch ( Exception $e ) {
			syslog( LOG_ERR, $e->getMessage() );
		}
	}

}

/**
 * Helper function to return Solr object.
 */
function get_solr() {
	return SolrPower_Api::get_instance()->get_solr();
}
