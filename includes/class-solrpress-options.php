<?php

class SolrPress_Options {

	/**
	 * Singleton instance
	 * @var SolrPress_Options|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPress_Options
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		add_action( 'admin_menu', array( $this, 'add_pages' ) );
		add_action( 'admin_init', array( $this, 'options_init' ) );
	}

	function add_pages() {
		$addpage = FALSE;

		if ( is_multisite() && is_site_admin() ) {
			$plugin_s4wp_settings	 = s4wp_get_option();
			$indexall				 = $plugin_s4wp_settings[ 's4wp_index_all_sites' ];
			if ( ($indexall && is_main_blog()) || !$indexall ) {
				$addpage = TRUE;
			}
		} else if ( !is_multisite() && is_admin() ) {
			$addpage = TRUE;
		}

		if ( $addpage ) {
			add_options_page( 'Solr Options', 'Solr Options', 'manage_options', 'solrpress', array( $this, 'options_page' ) );
		}
	}

	function options_page() {
		if ( file_exists( dirname( __FILE__ ) . '/solr-options-page.php' ) ) {
			include( dirname( __FILE__ ) . '/solr-options-page.php' );
		} else {
			_e( "<p>Couldn't locate the options page.</p>", 'solr4wp' );
		}
	}

	function options_init() {
		error_reporting( E_ERROR );
		ini_set( 'display_errors', false );

		register_setting( 's4w-options-group', 'plugin_s4wp_settings', 's4wp_sanitise_options' );
	}

}
