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
	public $msg = null;

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
		add_action( 'wp_ajax_solr_options', array( $this, 'options_load' ) );
		add_action( 'admin_init', array( $this, 'check_for_actions' ) );
		add_action( 'admin_init', array( $this, 'settings_api' ) );
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

	/**
	 * AJAX Callback
	 *
	 */
	function options_load() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die();
		}
		check_ajax_referer( 'solr_security', 'security' );
		$method = filter_input( INPUT_POST, 'method', FILTER_SANITIZE_STRING );
		if ( 'load' === $method ) {
			$type = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
			$prev = filter_input( INPUT_POST, 'prev', FILTER_SANITIZE_STRING );
			if ( $type ) {
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

	function update_option( $options ) {
		$optval   = $this->sanitise_options( $options );
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
	 * @return array $options sanitised values
	 */
	function sanitise_options( $options ) {
		$clean = array();

		$clean['s4wp_index_pages']         = 1;
		$clean['s4wp_index_posts']         = 1;
		$clean['s4wp_index_comments']      = absint( $options['s4wp_index_comments'] );
		$clean['s4wp_delete_page']         = absint( $options['s4wp_delete_page'] );
		$clean['s4wp_delete_post']         = absint( $options['s4wp_delete_post'] );
		$clean['s4wp_private_page']        = absint( $options['s4wp_private_page'] );
		$clean['s4wp_private_post']        = absint( $options['s4wp_private_post'] );
		$clean['s4wp_output_info']         = 1;
		$clean['s4wp_output_pager']        = 1;
		$clean['s4wp_output_facets']       = 1;
		$clean['s4wp_exclude_pages']       = $this->filter_str2list_numeric( $options['s4wp_exclude_pages'] );
		$clean['s4wp_cat_as_taxo']         = absint( $options['s4wp_cat_as_taxo'] );
		$clean['s4wp_max_display_tags']    = 10;
		$clean['s4wp_facet_on_categories'] = absint( $options['s4wp_facet_on_categories'] );
		$clean['s4wp_facet_on_tags']       = absint( $options['s4wp_facet_on_tags'] );
		$clean['s4wp_facet_on_author']     = absint( $options['s4wp_facet_on_author'] );
		$clean['s4wp_facet_on_type']       = absint( $options['s4wp_facet_on_type'] );
		$clean['s4wp_facet_on_taxonomy']   = absint( $options['s4wp_facet_on_taxonomy'] );
		if ( is_multisite() ) {
			$clean['s4wp_index_all_sites'] = absint( $options['s4wp_index_all_sites'] );
		}
		$clean['s4wp_index_custom_fields']    = $this->filter_str2list( $options['s4wp_index_custom_fields'] );
		$clean['s4wp_facet_on_custom_fields'] = $this->filter_str2list( $options['s4wp_facet_on_custom_fields'] );
		$clean['s4wp_default_operator']       = sanitize_text_field( $options['s4wp_default_operator'] );
		$clean['s4wp_default_sort']           = sanitize_text_field( $options['s4wp_default_sort'] );
		$clean['s4wp_solr_initialized']       = 1;
		return $clean;
	}

	/**
	 * Converts comma separated string of integers to array.
	 *
	 * @param string $input
	 *
	 * @return array
	 */
	function filter_str2list_numeric( $input ) {
		$final = array();
		if ( '' !== $input ) {
			foreach ( explode( ',', $input ) as $val ) {
				$val = sanitize_text_field( trim( $val ) );
				if ( is_numeric( $val ) ) {
					$final[] = $val;
				}
			}
		}

		return $final;
	}

	/**
	 * Converts comma separated string to array.
	 *
	 * @param string $input
	 *
	 * @return array
	 */
	function filter_str2list( $input ) {
		$final = array();
		if ( '' !== $input ) {
			foreach ( explode( ',', $input ) as $val ) {
				$final[] = sanitize_text_field( trim( $val ) );
			}
		}

		return $final;
	}

	/**
	 * Converts array to a comma separated string.
	 *
	 * @param array $input
	 *
	 * @return string
	 */
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

	/**
	 * Sets the default options.
	 * @return array
	 */
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
		$options['s4wp_cat_as_taxo']            = 1;
		$options['s4wp_solr_initialized']       = 1;
		$options['s4wp_max_display_tags']       = 10;
		$options['s4wp_facet_on_categories']    = 1;
		$options['s4wp_facet_on_taxonomy']      = 1;
		$options['s4wp_facet_on_tags']          = 1;
		$options['s4wp_facet_on_author']        = 1;
		$options['s4wp_facet_on_type']          = 1;
		$options['s4wp_index_comments']         = 1;
		$options['s4wp_index_custom_fields']    = array();
		$options['s4wp_facet_on_custom_fields'] = array();
		$options['s4wp_index_custom_fields']    = '';
		$options['s4wp_facet_on_custom_fields'] = '';
		$options['s4wp_default_operator']       = 'OR';
		$options['s4wp_default_sort']           = 'score';
		$options['s4wp_solr_initialized']       = 1;
		$this->update_option( $options );
	}


	/**
	 * Checks to see if any actions were taken on the settings page.
	 */
	function check_for_actions() {

		if ( ! isset( $_POST['action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
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

	/**
	 * Verifies if nonce is valid for action taken.
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
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

		if ( ! $results ) {
			$this->msg = __( '<p><strong>Solr Results for string "' . esc_html( $qry ) . '
				":</strong>
			<br/>Search Failed.
		</p>', 'solr-for-wordpress-on-pantheon' );

			return;
		}
		$data     = $results->getData();
		$response = $data['response'];
		$header   = $data['responseHeader'];

		$out['hits']  = $response['numFound'];
		$out['qtime'] = sprintf( __( "%.3f" ), $header['QTime'] / 1000 );


		$this->msg = __( '<p><strong>Solr Results for string "' . esc_html( $qry ) . '
				":</strong>
			<br/>Hits: ' . esc_html( $out['hits'] ) . '
			<br/>Query Time: ' . esc_html( $out['qtime'] ) . '
		</p>', 'solr-for-wordpress-on-pantheon' );
	}

	/**
	 * Register setting and sections for Settings API.
	 */
	function settings_api() {
		register_setting( 'solr-power', 'plugin_s4wp_settings', array( $this, 'sanitise_options' ) );
		$this->indexing_section();
		$this->results_section();
	}

	/**
	 * Generates HTML form fields based upon array of arguments sent (field, filter, type, choices).
	 *
	 * @param array $args
	 */
	function render_field( $args ) {

		$field_name    = $args['field'];
		$s4wp_settings = solr_options();
		$value         = $this->render_value( $s4wp_settings[ $field_name ], $args['filter'] );
		switch ( $args['type'] ) {
			case 'input':
				echo '<input type="text" name="plugin_s4wp_settings[' . esc_attr( $field_name ) . ']" value="' . esc_attr( $value ) . '"/>';
				echo PHP_EOL; //XSS ok
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="plugin_s4wp_settings[' . esc_attr( $field_name ) . ']" value="1" ' . checked( $value, 1, false ) . '/>';
				echo PHP_EOL; //XSS ok
				break;
			case 'radio':
				if ( ! isset( $args['choices'] ) || ! is_array( $args['choices'] ) ) {
					return;
				}
				if ( empty( $value ) ) {
					$value = $args['choices'][0];
				}
				foreach ( $args['choices'] as $choice ) {
					echo esc_html( $choice ) . ' ';
					echo '<input type="radio" name="plugin_s4wp_settings[' . esc_attr( $field_name ) . ']" value="' . esc_attr( $choice ) . '" ' . checked( $value, $choice, false ) . '/>';
					echo PHP_EOL; //XSS ok
				}

				break;
			case 'select':
				if ( ! isset( $args['choices'] ) || ! is_array( $args['choices'] ) ) {
					return;
				}
				if ( empty( $value ) ) {
					$value = $args['choices'][0];
				}
				echo '<select name="plugin_s4wp_settings[' . esc_attr( $field_name ) . ']">';
				echo PHP_EOL; //XSS ok
				foreach ( $args['choices'] as $choice ) {
					echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $choice, false ) . '>' . esc_attr( $choice ) . '</option>';
					echo PHP_EOL; //XSS ok
				}
				echo '</select>';
				echo PHP_EOL; //XSS ok
				break;
		}
	}

	/**
	 * Type adjustments for value.
	 *
	 * @param string $value Value saved in option.
	 * @param string $filter Type change needed.
	 *
	 * @return string Filtered (or unfiltered) value.
	 */
	private function render_value( $value, $filter ) {
		if ( is_null( $filter ) ) {
			return $value;
		}
		switch ( $filter ) {
			case 'list2str':
				return SolrPower_Options::get_instance()->filter_list2str( $value );
				break;
			case 'bool':
				return absint( $value );
				break;
		}

		return $value;
	}

	/**
	 * Indexing Options Section
	 * Creates the Indexing Options section and the fields in that section.
	 */
	private function indexing_section() {
		$section = 'solr_indexing';
		add_settings_section(
			$section,
			'Indexing Options',
			'__return_empty_string',
			'solr-power'
		);

		$this->add_field( 's4wp_delete_page', 'Remove Page on Delete', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_delete_post', 'Remove Post on Delete', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_private_page', 'Remove Page on Status Change', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_private_post', 'Remove Post on Status Change', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_index_comments', 'Index Comments', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_index_custom_fields', 'Index custom fields (comma separated names list)', $section, 'input', 'list2str' );
		$this->add_field( 's4wp_exclude_pages', 'Excludes Posts or Pages (comma separated ids list)', $section, 'input', 'list2str' );

	}

	/**
	 * Results Section
	 * Adds fields in section.
	 */
	private function results_section() {
		$section = 'solr_results';

		add_settings_section(
			$section,
			'Result Options',
			'__return_empty_string',
			'solr-power'
		);

		$this->add_field( 's4wp_cat_as_taxo', 'Category Facet as Taxonomy', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_facet_on_categories', 'Categories as Facet', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_facet_on_tags', 'Tags as Facet', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_facet_on_author', 'Author as Facet', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_facet_on_type', 'Post Type as Facet', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_facet_on_taxonomy', 'Taxonomy as Facet', $section, 'checkbox', 'bool' );
		$this->add_field( 's4wp_facet_on_custom_fields', 'Custom fields as Facet (comma separated ordered names list)', $section, 'input', 'list2str' );

		$this->add_field( 's4wp_default_operator', 'Default Search Operator', $section, 'radio', null, array(
			'OR',
			'AND'
		) );

		$this->add_field( 's4wp_default_sort', 'Default Sort', $section, 'select', null, array(
			'score',
			'displaydate'
		) );
	}

	/**
	 * Adds the settings field with custom arguments.
	 *
	 * @param string $name Option Key
	 * @param string $title Name of field /  label
	 * @param string $section The section the field is going to be registered to
	 * @param string $type Type of form element (input, checkbox, radio, select)
	 * @param null|string $filter Any value typesetting that needs to be done.
	 * @param null|string $choices Any default choices for select or radio options.
	 */
	private function add_field( $name, $title, $section, $type, $filter = null, $choices = null ) {
		add_settings_field( $name, $title, array(
			$this,
			'render_field'
		), 'solr-power', $section, array(
			'field'   => $name,
			'type'    => $type,
			'filter'  => $filter,
			'choices' => $choices
		) );
	}

}
