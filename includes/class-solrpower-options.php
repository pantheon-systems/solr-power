<?php

class SolrPower_Options {

	/**
	 * Singleton instance
	 * @var SolrPower_Options|Bool
	 */
	private static $instance = false;

	/**
	 * @var null|string Admin message.
	 */
	var $msg = null;

	/**
	 * Grab instance of object.
	 * @return SolrPower_Options
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'admin_menu', array( $this, 'add_pages' ) );
		add_action( 'network_admin_menu', array( $this, 'add_network_pages' ) );
		add_action( 'admin_init', array( $this, 'options_init' ) );
		add_action( 'wp_ajax_solr_options', array( $this, 'options_load' ) );
		//add_action( 'init', array( $this, 'check_for_defaults' ) );
		add_action( 'admin_init', array( $this, 'check_for_actions' ) );
	}

	function add_pages() {
		if ( ! is_multisite() ) {
			add_options_page( 'Solr Options', 'Solr Options', 'manage_options', 'solr-power', array(
				$this,
				'options_page'
			) );
		}
	}

	function add_network_pages() {
		add_submenu_page( 'settings.php', 'Solr Options', 'Solr Options', 'manage_options', 'solr-power', array(
			$this,
			'options_page'
		) );
	}

	function options_page() {
		if ( file_exists( SOLR_POWER_PATH . '/solr-options-page.php' ) ) {
			include( SOLR_POWER_PATH . '/solr-options-page.php' );
		} else {
			esc_html_e( "Couldn't locate the options page.", 'solr4wp' );
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
			$type = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
			$prev = filter_input( INPUT_POST, 'prev', FILTER_SANITIZE_STRING );
			if ( isset( $type ) ) {
				SolrPower_Sync::get_instance()->load_all_posts( $prev, $type );
				die();
			}
		}
		die();
	}

	function get_option() {
		$indexall = false;
		$option   = 'plugin_s4wp_settings';

		if ( is_multisite() ) {
			$plugin_s4wp_settings = get_site_option( $option );
			$indexall             = $plugin_s4wp_settings['s4wp_index_all_sites'];
		}

		if ( $indexall ) {
			return get_site_option( $option );
		} else {
			return get_option( $option );
		}
	}

	function update_option( $optval ) {
		$indexall = false;
		$option   = 'plugin_s4wp_settings';
		if ( is_multisite() ) {
			$plugin_s4wp_settings = get_site_option( $option );
			$indexall             = $plugin_s4wp_settings['s4wp_index_all_sites'];
		}

		if ( $indexall ) {
			update_site_option( $option, $optval );
		} else {
			update_option( $option, $optval );
		}
	}

	/**
	 * Sanitises the options values
	 *
	 * @param $options array of s4w settings options
	 *
	 * @return $options sanitised values
	 */
	function sanitise_options( $options ) {
		$options['s4wp_index_pages']         = absint( $options['s4wp_index_pages'] );
		$options['s4wp_index_posts']         = absint( $options['s4wp_index_posts'] );
		$options['s4wp_index_comments']      = absint( $options['s4wp_index_comments'] );
		$options['s4wp_delete_page']         = absint( $options['s4wp_delete_page'] );
		$options['s4wp_delete_post']         = absint( $options['s4wp_delete_post'] );
		$options['s4wp_private_page']        = absint( $options['s4wp_private_page'] );
		$options['s4wp_private_post']        = absint( $options['s4wp_private_post'] );
		$options['s4wp_output_info']         = absint( $options['s4wp_output_info'] );
		$options['s4wp_output_pager']        = absint( $options['s4wp_output_pager'] );
		$options['s4wp_output_facets']       = absint( $options['s4wp_output_facets'] );
		$options['s4wp_exclude_pages']       = $this->filter_str2list_numeric( $options['s4wp_exclude_pages'] );
		$options['s4wp_num_results']         = absint( $options['s4wp_num_results'] );
		$options['s4wp_cat_as_taxo']         = absint( $options['s4wp_cat_as_taxo'] );
		$options['s4wp_max_display_tags']    = absint( $options['s4wp_max_display_tags'] );
		$options['s4wp_facet_on_categories'] = absint( $options['s4wp_facet_on_categories'] );
		$options['s4wp_facet_on_tags']       = absint( $options['s4wp_facet_on_tags'] );
		$options['s4wp_facet_on_author']     = absint( $options['s4wp_facet_on_author'] );
		$options['s4wp_facet_on_type']       = absint( $options['s4wp_facet_on_type'] );
		if ( is_multisite() ) {
			$options['s4wp_index_all_sites'] = absint( $options['s4wp_index_all_sites'] );
		}
		$options['s4wp_connect_type']           = wp_filter_nohtml_kses( $options['s4wp_connect_type'] );
		$options['s4wp_index_custom_fields']    = $this->filter_str2list( $options['s4wp_index_custom_fields'] );
		$options['s4wp_facet_on_custom_fields'] = $this->filter_str2list( $options['s4wp_facet_on_custom_fields'] );
		$options['s4wp_default_sort']           = esc_attr( $options['s4wp_default_sort'] );

		return $options;
	}

	function filter_str2list_numeric( $input ) {
		$final = array();
		if ( $input != "" ) {
			foreach ( explode( ',', $input ) as $val ) {
				$val = sanitize_text_field( trim( $val ) );
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
				$final[] = sanitize_text_field( trim( $val ) );
			}
		}

		return $final;
	}

	function filter_list2str( $input ) {
		if ( ! is_array( $input ) ) {
			return "";
		}

		$outval = implode( ',', $input );
		if ( ! $outval ) {
			$outval = "";
		}

		return $outval;
	}

	function initalize_options() {
		$options = array();

		$options['s4wp_index_pages']            = 1;
		$options['s4wp_index_posts']            = 1;
		$options['s4wp_delete_page']            = 1;
		$options['s4wp_delete_post']            = 1;
		$options['s4wp_private_page']           = 1;
		$options['s4wp_private_post']           = 1;
		$options['s4wp_output_info']            = 1;
		$options['s4wp_output_pager']           = 1;
		$options['s4wp_output_facets']          = 1;
		$options['s4wp_exclude_pages']          = array();
		$options['s4wp_exclude_pages']          = '';
		$options['s4wp_num_results']            = 5;
		$options['s4wp_cat_as_taxo']            = 1;
		$options['s4wp_solr_initialized']       = 1;
		$options['s4wp_max_display_tags']       = 10;
		$options['s4wp_facet_on_categories']    = 1;
		$options['s4wp_facet_on_taxonomy']      = 1;
		$options['s4wp_facet_on_tags']          = 1;
		$options['s4wp_facet_on_author']        = 1;
		$options['s4wp_facet_on_type']          = 1;
		$options['s4wp_index_comments']         = 1;
		$options['s4wp_connect_type']           = 'solr';
		$options['s4wp_index_custom_fields']    = array( 'default!' );
		$options['s4wp_facet_on_custom_fields'] = array();
		$options['s4wp_index_custom_fields']    = '';
		$options['s4wp_facet_on_custom_fields'] = '';
		$options['s4wp_default_operator']       = 'OR';
		$options['s4wp_default_sort']           = 'score';

		return $options;
	}


	/**
	 * If option values are not set, let's set some defaults.
	 */
	function check_for_defaults() {
		//get the plugin settings
		$s4wp_settings = solr_options();

		//set defaults if not initialized

		if ( $s4wp_settings['s4wp_solr_initialized'] != 1 ) {
			$options = SolrPower_Options::get_instance()->initalize_options();

			$options['s4wp_index_all_sites'] = 0;

			//update existing settings from multiple option record to a single array
			//if old options exist, update to new system
			// Pretty sure we don't need any of this. Seems left over from an older version. - Cal
			$delete_option_function = 'delete_option';

			if ( is_multisite() ) {
				$indexall               = get_site_option( 's4wp_index_all_sites' );
				$delete_option_function = 'delete_site_option';
			}

			//find each of the old options function
			//update our new array and delete the record.
			foreach ( $options as $key => $value ) {
				if ( $existing = get_option( $key ) ) {
					$options[ $key ] = $existing;
					$indexall        = false;
					//run the appropriate delete options function
					$delete_option_function( $key );
				}
			}


			//save our options array
			$this->update_option( $options );
		}

	}

	function save_options() {
		$s4wp_settings = solr_options();
		//lets loop through our setting fields $_POST['settings']

		foreach ( $s4wp_settings as $option => $old_value ) {
			if ( ! isset( $_POST['settings'][ $option ] ) ) {
				$s4wp_settings[ $option ] = 0;
				continue;
			}
			$value = $_POST['settings'][ $option ];
			switch ( $option ) {
				case 's4wp_solr_initialized':
					$value = absint( trim( $old_value ) );
					break;
				case 's4wp_server':
					//remove empty server entries
					$s_value = &$value['info'];

					foreach ( $s_value as $key => $v ) {
						//lets rename the array_keys
						if ( ! $v['host'] ) {
							unset( $s_value[ $key ] );
						}
					}
					break;

			}
			if ( ! is_array( $value ) ) {
				$value = sanitize_text_field( trim( $value ) );
			} else {
				$value = array_map( 'sanitize_text_field', $value );
			}
			$value                    = stripslashes_deep( $value );
			$s4wp_settings[ $option ] = $value;
		}
		$s4wp_settings['s4wp_solr_initialized'] = 1;
		//lets save our options array
		SolrPower_Options::get_instance()->update_option( $s4wp_settings );

		$this->msg = __( 'Success!', 'solr-for-wordpress-on-pantheon' );

	}

	function check_for_actions() {
		if ( isset( $_POST['action'] ) ) {
			$action = sanitize_text_field( $_POST['action'] );
			switch ( $action ) {
				case 'update':
					if ( ! $this->check_nonce( 'solr_update' ) ) {
						return;
					}
					$this->save_options();
					break;
				case 'ping':
					if ( ! $this->check_nonce( 'solr_ping' ) ) {
						return;
					}
					if ( SolrPower_Api::get_instance()->ping_server() ) {
						$this->msg = __( 'Ping Success!', 'solr-for-wordpress-on-pantheon' );
					} else {
						$this->msg = __( 'Ping Failed!', 'solr-for-wordpress-on-pantheon' );
					}
					break;
				case 'init_blogs':
					if ( ! $this->check_nonce( 'solr_init_blogs' ) ) {
						return;
					}
					SolrPower_Sync::get_instance()->copy_config_to_all_blogs();
					$this->msg = __( 'Configuration has been copied to all blogs!', 'solr-for-wordpress-on-pantheon' );
					break;
				case 'optimize':
					if ( ! $this->check_nonce( 'solr_optimize' ) ) {
						return;
					}
					SolrPower_Api::get_instance()->optimize();
					$this->msg = __( 'Index Optimized!', 'solr-for-wordpress-on-pantheon' );
					break;
				case 'delete_all':
					if ( ! $this->check_nonce( 'solr_delete_all' ) ) {
						return;
					}
					SolrPower_Sync::get_instance()->delete_all();
					$this->msg = __( 'All Indexed Pages Deleted!', 'solr-for-wordpress-on-pantheon' );
					break;
				case 'repost_schema':
					if ( ! $this->check_nonce( 'solr_repost_schema' ) ) {
						return;
					}
					SolrPower_Sync::get_instance()->delete_all();
					$output    = SolrPower_Api::get_instance()->submit_schema();
					$this->msg = __( 'All Indexed Pages Deleted!<br />' . esc_html( $output ), 'solr-for-wordpress-on-pantheon' );
					break;

				case 'run_query':
					if ( ! $this->check_nonce( 'solr_run_query' ) ) {
						return;
					}
					$this->run_query();
					break;


			}
		}
	}

	private function check_nonce( $field ) {
		if ( ! isset( $_POST[ $field ] )
		     || ! wp_verify_nonce( $_POST[ $field ], 'solr_action' )
		) {
			$this->msg = __( 'Action failed. Please try again.' . $field, 'solr-for-wordpress-on-pantheon' );

			return false;
		}

		return true;
	}

	/**
	 * Run the Solr query on the options page.
	 */
	private function run_query() {
		$qry     = filter_input( INPUT_POST, 'solrQuery', FILTER_SANITIZE_STRING );
		$offset  = null;
		$count   = null;
		$fq      = null;
		$sortby  = null;
		$order   = null;
		$results = SolrPower_Api::get_instance()->query( $qry, $offset, $count, $fq, $sortby, $order );

		if ( isset( $results ) ) {
			$plugin_s4wp_settings = solr_options();
			$output_info          = $plugin_s4wp_settings['s4wp_output_info'];
			$data                 = $results->getData();
			$response             = $data['response'];
			$header               = $data['responseHeader'];
			if ( $output_info ) {
				$out['hits']  = $response['numFound'];
				$out['qtime'] = sprintf( __( "%.3f" ), $header['QTime'] / 1000 );
			} else {
				$out['hits']  = 0;
				$out['qtime'] = 0;
			}
		} else {
			$data = $results;
		}
		$this->msg = __( '<p><strong>Solr Results for string "' . esc_html( $qry ) . '
				":</strong>
			<br/>Hits: ' . esc_html( $out['hits'] ) . '
			<br/>Query Time: ' . esc_html( $out['qtime'] ) . '
		</p>', 'solr-for-wordpress-on-pantheon' );
	}
}

SolrPower_Options::get_instance();

function solr_options() {
	return SolrPower_Options::get_instance()->get_option();
}
