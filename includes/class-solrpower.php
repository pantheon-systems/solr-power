<?php

class SolrPower {

	/**
	 * Singleton instance
	 * @var SolrPress|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPress
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 1 );
		//	add_action( 'widgets_init', array( $this, 'widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'autosuggest_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
	}

	static function activate() {

		// Check to see if we have  environment variables. If not, bail. If so, create the initial options.

		if ( $errMessage = SolrPower::get_instance()->sanity_check() ) {
			wp_die( $errMessage );
		}

		$schemaSubmit = SolrPower_Api::get_instance()->submit_schema();

		if ( strpos( $schemaSubmit, 'Error' ) ) {
			wp_die( 'Submitting the schema failed with the message ' . $errorMessage );
		}
		$options = SolrPower_Options::get_instance()->initalize_options();
		SolrPower_Options::get_instance()->update_option( $options );
		return;
	}

	function sanity_check() {
		$returnValue = '';
		$wp_version	 = get_bloginfo( 'version' );

		if ( getenv( 'PANTHEON_INDEX_HOST' ) === false ) {
			$returnValue = __( 'Before you can activate this plugin, you must first activate Solr in your Pantheon Dashboard.', 'solr-for-wordpress-on-pantheon' );
		} else if ( version_compare( $wp_version, '3.0', '<' ) ) {
			$returnValue = __( 'This plugin requires WordPress 3.0 or greater.', 'solr-for-wordpress-on-pantheon' );
		}

		return $returnValue;
	}

	function widget() {
		register_widget( 's4wp_MLTWidget' );
	}

	function admin_head() {
		// include our default css
		if ( file_exists( SOLR_POWER_PATH . '/template/search.css' ) ) {
			wp_enqueue_style( 'solr-search', SOLR_POWER_URL . 'template/search.css' );
		}
		wp_enqueue_script( 'solr-js', SOLR_POWER_URL . 'template/script.js', false );
		$solr_js = array(
			'ajax_url'	 => admin_url( 'admin-ajax.php' ),
			'post_types' => apply_filters( 'solr_post_types', get_post_types( array( 'exclude_from_search' => false ) ) ),
			'security'	 => wp_create_nonce( "solr_security" )
		);
		wp_localize_script( 'solr-js', 'solr', $solr_js );
	}

	function plugin_settings_link( $links, $file ) {
		if ( $file != plugin_basename( SOLR_POWER_PATH . 'solr-power.php' ) ) {
			return $links;
		}

		array_unshift( $links, '<a href="' . admin_url( 'admin.php' ) . '?page=solr-power">' . __( 'Settings', 's4wp' ) . '</a>' );

		return $links;
	}

	/*
	 * @TODO change to echo statemnts and get rid of direct output.
	 */

	function autosuggest_head() {
		if ( file_exists( SOLR_POWER_PATH . '/template/autocomplete.css' ) ) {
			wp_enqueue_style( 'solr-autocomplete', SOLR_POWER_URL . 'template/autocomplete.css' );
		}
		wp_enqueue_script( 'solr-suggest', SOLR_POWER_URL . 'template/autocomplete.js', false );
	}

	function template_redirect() {
		wp_enqueue_script( 'suggest' );

		// not a search page; don't do anything and return
		// thanks to the Better Search plugin for the idea:  http://wordpress.org/extend/plugins/better-search/
		$search			 = stripos( $_SERVER[ 'REQUEST_URI' ], '?ssearch=' );
		$autocomplete	 = stripos( $_SERVER[ 'REQUEST_URI' ], '?method=autocomplete' );

		if ( ($search || $autocomplete) == FALSE ) {
			return;
		}

		if ( $autocomplete ) {
			$q		 = filter_input( INPUT_GET, 'q', FILTER_SANITIZE_STRING );
			$limit	 = filter_input( INPUT_GET, 'limit', FILTER_SANITIZE_STRING );

			$this->autocomplete( $q, $limit );
			exit;
		}

		// If there is a template file then we use it
		if ( file_exists( TEMPLATEPATH . '/s4wp_search.php' ) ) {
			// use theme file
			include_once(TEMPLATEPATH . '/s4wp_search.php');
		} else if ( file_exists( dirname( __FILE__ ) . '/template/s4wp_search.php' ) ) {
			// use plugin supplied file
			add_action( 'wp_head', array( $this, 'default_head' ) );
			include_once(dirname( __FILE__ ) . '/template/s4wp_search.php');
		} else {
			// no template files found, just continue on like normal
			// this should get to the normal WordPress search results
			return;
		}

		exit;
	}

	function autocomplete( $q, $limit ) {
		$solr		 = get_solr();
		$response	 = NULL;

		if ( !$solr ) {
			return;
		}

		$query = $solr->createTerms();
		$query->setFields( 'spell' );
		$query->setPrefix( $q );
		$query->setLowerbound( $q );
		$query->setLowerboundInclude( false );
		$query->setLimit( $limit );

		$response = $solr->terms( $query );
		if ( !$response->getResponse()->getStatusCode() == 200 ) {
			return;
		}
		$terms = $response->getResults();
		foreach ( $terms[ 'spell' ] as $term => $count ) {
			printf( "%s\n", $term );
		}
	}

	function default_head() {
		// include our default css
		if ( file_exists( dirname( __FILE__ ) . '/template/search.css' ) ) {
			wp_enqueue_style( 'solr-search', plugins_url( '/template/search.css', __FILE__ ) );
		}
	}

}

SolrPower::get_instance();
