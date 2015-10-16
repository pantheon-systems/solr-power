<?php

class SolrPress {

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
		add_action( 'template_redirect', 's4wp_template_redirect', 1 );
		add_action( 'widgets_init', array( $this, 'widget' ) );
		add_action( 'wp_enqueue_scripts', 's4wp_autosuggest_head' );
		add_action( 'admin_enqueue_scripts', 's4wp_admin_head' );
		add_filter( 'plugin_action_links', 's4wp_plugin_settings_link', 10, 2 );
		add_action( 'wp_ajax_solr_options', 's4wp_options_load' );

	}

	function widget() {
		register_widget( 's4wp_MLTWidget' );
	}

}
