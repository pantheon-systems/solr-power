<?php

class SolrPower_Options {

	/**
	 * Singleton instance
	 * @var SolrPower_Options|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPower_Options
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}
		return self::$instance;
	}

	function __construct() {
		
	}

	function setup() {
		add_action( 'admin_menu', array( $this, 'add_pages' ) );
		add_action( 'network_admin_menu', array( $this, 'add_network_pages' ) );
		add_action( 'admin_init', array( $this, 'options_init' ) );
		add_action( 'wp_ajax_solr_options', array( $this, 'options_load' ) );
	}

	function add_pages() {
		if ( !is_multisite() ) {
			add_options_page( 'Solr Options', 'Solr Options', 'manage_options', 'solr-power', array( $this, 'options_page' ) );
		}
	}

	function add_network_pages() {
		add_submenu_page( 'settings.php', 'Solr Options', 'Solr Options', 'manage_options', 'solr-power', array( $this, 'options_page' ) );
	}

	function options_page() {
		if ( file_exists( SOLR_POWER_PATH . '/solr-options-page.php' ) ) {
			include( SOLR_POWER_PATH . '/solr-options-page.php' );
		} else {
			_e( "<p>Couldn't locate the options page.</p>", 'solr4wp' );
		}
	}

	function options_init() {
		error_reporting( E_ERROR );
		ini_set( 'display_errors', false );

		register_setting( 's4w-options-group', 'plugin_s4wp_settings', array( $this, 'sanitise_options' ) );
	}

	/**
	 * AJAX Callback
	 *
	 */
	function options_load() {
		check_ajax_referer( 'solr_security', 'security' );
		$method = filter_input( INPUT_POST, 'method', FILTER_SANITIZE_STRING );
		if ( $method === "load" ) {
			$type	 = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
			$prev	 = filter_input( INPUT_POST, 'prev', FILTER_SANITIZE_STRING );
			if ( isset( $type ) ) {
				SolrPower_Sync::get_instance()->load_all_posts( $prev, $type );
				die();
			}
		}
		die();
	}

	function get_option() {
		$indexall	 = FALSE;
		$option		 = 'plugin_s4wp_settings';

		if ( is_multisite() ) {
			$plugin_s4wp_settings	 = get_site_option( $option );
			$indexall				 = $plugin_s4wp_settings[ 's4wp_index_all_sites' ];
		}

		if ( $indexall ) {
			return get_site_option( $option );
		} else {
			return get_option( $option );
		}
	}

	function update_option( $optval ) {
		$indexall	 = FALSE;
		$option		 = 'plugin_s4wp_settings';
		if ( is_multisite() ) {
			$plugin_s4wp_settings	 = get_site_option( $option );
			$indexall				 = $plugin_s4wp_settings[ 's4wp_index_all_sites' ];
		}

		if ( $indexall ) {
			update_site_option( $option, $optval );
		} else {
			update_option( $option, $optval );
		}
	}

	/**
	 * Sanitises the options values
	 * @param $options array of s4w settings options
	 * @return $options sanitised values
	 */
	function sanitise_options( $options ) {
		$options[ 's4wp_index_pages' ]				 = absint( $options[ 's4wp_index_pages' ] );
		$options[ 's4wp_index_posts' ]				 = absint( $options[ 's4wp_index_posts' ] );
		$options[ 's4wp_index_comments' ]			 = absint( $options[ 's4wp_index_comments' ] );
		$options[ 's4wp_delete_page' ]				 = absint( $options[ 's4wp_delete_page' ] );
		$options[ 's4wp_delete_post' ]				 = absint( $options[ 's4wp_delete_post' ] );
		$options[ 's4wp_private_page' ]				 = absint( $options[ 's4wp_private_page' ] );
		$options[ 's4wp_private_post' ]				 = absint( $options[ 's4wp_private_post' ] );
		$options[ 's4wp_output_info' ]				 = absint( $options[ 's4wp_output_info' ] );
		$options[ 's4wp_output_pager' ]				 = absint( $options[ 's4wp_output_pager' ] );
		$options[ 's4wp_output_facets' ]			 = absint( $options[ 's4wp_output_facets' ] );
		$options[ 's4wp_exclude_pages' ]			 = $this->filter_str2list( $options[ 's4wp_exclude_pages' ] );
		$options[ 's4wp_num_results' ]				 = absint( $options[ 's4wp_num_results' ] );
		$options[ 's4wp_cat_as_taxo' ]				 = absint( $options[ 's4wp_cat_as_taxo' ] );
		$options[ 's4wp_max_display_tags' ]			 = absint( $options[ 's4wp_max_display_tags' ] );
		$options[ 's4wp_facet_on_categories' ]		 = absint( $options[ 's4wp_facet_on_categories' ] );
		$options[ 's4wp_facet_on_tags' ]			 = absint( $options[ 's4wp_facet_on_tags' ] );
		$options[ 's4wp_facet_on_author' ]			 = absint( $options[ 's4wp_facet_on_author' ] );
		$options[ 's4wp_facet_on_type' ]			 = absint( $options[ 's4wp_facet_on_type' ] );
		$options[ 's4wp_index_all_sites' ]			 = absint( $options[ 's4wp_index_all_sites' ] );
		$options[ 's4wp_connect_type' ]				 = wp_filter_nohtml_kses( $options[ 's4wp_connect_type' ] );
		$options[ 's4wp_index_custom_fields' ]		 = $this->filter_str2list( $options[ 's4wp_index_custom_fields' ] );
		$options[ 's4wp_facet_on_custom_fields' ]	 = $this->filter_str2list( $options[ 's4wp_facet_on_custom_fields' ] );
		return $options;
	}

	function filter_str2list_numeric( $input ) {
		$final = array();
		if ( $input != "" ) {
			foreach ( explode( ',', $input ) as $val ) {
				$val = trim( $val );
				if ( is_numeric( $val ) ) {
					$final[] = $val;
				}
			}
		}

		return $final;
	}

	function filter_str2list( $input ) {
		$final = array();
		if ( $input != "" ) {
			foreach ( explode( ',', $input ) as $val ) {
				$final[] = trim( $val );
			}
		}

		return $final;
	}

	function filter_list2str( $input ) {
		if ( !is_array( $input ) ) {
			return "";
		}

		$outval = implode( ',', $input );
		if ( !$outval ) {
			$outval = "";
		}

		return $outval;
	}

	function initalize_options() {
		$options = array();

		$options[ 's4wp_index_pages' ]				 = 1;
		$options[ 's4wp_index_posts' ]				 = 1;
		$options[ 's4wp_delete_page' ]				 = 1;
		$options[ 's4wp_delete_post' ]				 = 1;
		$options[ 's4wp_private_page' ]				 = 1;
		$options[ 's4wp_private_post' ]				 = 1;
		$options[ 's4wp_output_info' ]				 = 1;
		$options[ 's4wp_output_pager' ]				 = 1;
		$options[ 's4wp_output_facets' ]			 = 1;
		$options[ 's4wp_exclude_pages' ]			 = array();
		$options[ 's4wp_exclude_pages' ]			 = '';
		$options[ 's4wp_num_results' ]				 = 5;
		$options[ 's4wp_cat_as_taxo' ]				 = 1;
		$options[ 's4wp_solr_initialized' ]			 = 1;
		$options[ 's4wp_max_display_tags' ]			 = 10;
		$options[ 's4wp_facet_on_categories' ]		 = 1;
		$options[ 's4wp_facet_on_taxonomy' ]		 = 1;
		$options[ 's4wp_facet_on_tags' ]			 = 1;
		$options[ 's4wp_facet_on_author' ]			 = 1;
		$options[ 's4wp_facet_on_type' ]			 = 1;
		$options[ 's4wp_index_comments' ]			 = 1;
		$options[ 's4wp_connect_type' ]				 = 'solr';
		$options[ 's4wp_index_custom_fields' ]		 = array();
		$options[ 's4wp_facet_on_custom_fields' ]	 = array();
		$options[ 's4wp_index_custom_fields' ]		 = '';
		$options[ 's4wp_facet_on_custom_fields' ]	 = '';
		$options[ 's4wp_default_operator' ]			 = 'OR';

		return $options;
	}

}

SolrPower_Options::get_instance();

function solr_options() {
	return SolrPower_Options::get_instance()->get_option();
}
