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
		add_action( 'widgets_init', array( $this, 'widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'autosuggest_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
	}

	function widget() {
		register_widget( 's4wp_MLTWidget' );
	}

	function admin_head() {
		// include our default css
		if ( file_exists( dirname( __FILE__ ) . '/template/search.css' ) ) {
			wp_enqueue_style( 'solr-search', plugins_url( '/template/search.css', __FILE__ ) );
		}
		wp_enqueue_script( 'solr-js', plugins_url( '/template/script.js', __FILE__ ), false );
		$solr_js = array(
			'ajax_url'	 => admin_url( 'admin-ajax.php' ),
			'post_types' => apply_filters( 'solr_post_types', get_post_types( array( 'exclude_from_search' => false ) ) ),
			'security'	 => wp_create_nonce( "solr_security" )
		);
		wp_localize_script( 'solr-js', 'solr', $solr_js );
	}

	function plugin_settings_link( $links, $file ) {
		if ( $file != plugin_basename( __FILE__ ) ) {
			return $links;
		}

		array_unshift( $links, '<a href="' . admin_url( 'admin.php' ) . '?page=solr-power">' . __( 'Settings', 's4wp' ) . '</a>' );

		return $links;
	}

	/*
	 * @TODO change to echo statemnts and get rid of direct output.
	 */

	function autosuggest_head() {
		if ( file_exists( dirname( __FILE__ ) . '/template/autocomplete.css' ) ) {
			wp_enqueue_style( 'solr-autocomplete', plugins_url( '/template/autocomplete.css', __FILE__ ) );
		}
		wp_enqueue_script( 'solr-suggest', plugins_url( '/template/autocomplete.js', __FILE__ ), false );
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

			s4wp_autocomplete( $q, $limit );
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

}
