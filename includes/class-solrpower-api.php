<?php

class SolrPower_Api {

	/**
	 * Singleton instance
	 * @var SolrPower_Api|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPower_Api
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
		if ( defined( 'SOLR_PATH' ) ) {
			return SOLR_PATH;
		}
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

		$solarium_config = apply_filters( 's4wp_connection_options', $solarium_config );


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

	/**
	 * Query the required server
	 * passes all parameters to the appropriate function based on the server name
	 * This allows for extensible server/core based query functions.
	 * TODO allow for similar theme/output function
	 */
	function query( $qry, $offset, $count, $fq, $sortby, $order, $server = 'master' ) {
		//NOTICE: does this needs to be cached to stop the db being hit to grab the options everytime search is being done.
		$plugin_s4wp_settings = solr_options();

		$solr = get_solr();

		return $this->master_query( $solr, $qry, $offset, $count, $fq, $sortby, $order, $plugin_s4wp_settings );
	}

	function master_query( $solr, $qry, $offset, $count, $fq, $sortby, $order, &$plugin_s4wp_settings ) {
		$response		 = NULL;
		$facet_fields	 = array();
		$number_of_tags	 = $plugin_s4wp_settings[ 's4wp_max_display_tags' ];

		if ( $plugin_s4wp_settings[ 's4wp_facet_on_categories' ] ) {
			$facet_fields[] = 'categories';
		}

		$facet_on_tags = $plugin_s4wp_settings[ 's4wp_facet_on_tags' ];
		if ( $facet_on_tags ) {
			$facet_fields[] = 'tags';
		}

		if ( $plugin_s4wp_settings[ 's4wp_facet_on_author' ] ) {
			$facet_fields[] = 'author';
		}

		if ( $plugin_s4wp_settings[ 's4wp_facet_on_type' ] ) {
			$facet_fields[] = 'type';
		}


		$facet_on_custom_taxonomy = $plugin_s4wp_settings[ 's4wp_facet_on_taxonomy' ];
		if ( count( $facet_on_custom_taxonomy ) ) {
			$taxonomies = (array) get_taxonomies( array( '_builtin' => FALSE ), 'names' );
			foreach ( $taxonomies as $parent ) {
				$facet_fields[] = $parent . "_taxonomy";
			}
		}

		$facet_on_custom_fields = $plugin_s4wp_settings[ 's4wp_facet_on_custom_fields' ];
		if ( is_array( $facet_on_custom_fields ) and count( $facet_on_custom_fields ) ) {
			foreach ( $facet_on_custom_fields as $field_name ) {
				$facet_fields[] = $field_name . '_str';
			}
		}

		if ( $solr ) {
			$select = array(
				'query'		 => $qry,
				'fields'	 => '*,score',
				'start'		 => $offset,
				'rows'		 => $count,
				'omitheader' => false
			);
			if ( $sortby != "" ) {
				$select[ 'sort' ] = array( $sortby => $order );
			} else {
				$select[ 'sort' ] = array( 'date' => 'desc' );
			}

			$query = $solr->createSelect( $select );

			$facetSet = $query->getFacetSet();
			foreach ( $facet_fields as $facet_field ) {
				$facetSet->createFacetField( $facet_field )->setField( $facet_field );
			}
			$facetSet->setMinCount( 1 );
			if ( $facet_on_tags ) {
				$facetSet->setLimit( $number_of_tags );
			}

			if ( isset( $fq ) ) {
				foreach ( $fq as $filter ) {
					if ( $filter !== "" ) {
						$query->createFilterQuery( $filter )->setQuery( $filter );
					}
				}
			}
			$query->getHighlighting()->setFields( 'content' );
			$query->getHighlighting()->setSimplePrefix( '<b>' );
			$query->getHighlighting()->setSimplePostfix( '</b>' );
			$query->getHighlighting()->setHighlightMultiTerm( true );

			if ( isset( $plugin_s4wp_settings[ 's4wp_default_operator' ] ) ) {
				$query->setQueryDefaultOperator( $plugin_s4wp_settings[ 's4wp_default_operator' ] );
			}
			try {
				$response = $solr->select( $query );
				if ( !$response->getResponse()->getStatusCode() == 200 ) {
					$response = NULL;
				}
			} catch ( Exception $e ) {
				syslog( LOG_ERR, "failed to query solr. " . $e->getMessage() );
				$response = NULL;
			}
		}

		
		return $response;
	}

}

SolrPower_Api::get_instance();

/**
 * Helper function to return Solr object.
 */
function get_solr() {
	return SolrPower_Api::get_instance()->get_solr();
}
