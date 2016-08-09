<?php

/*
  Plugin Name: Solr Power
  Description: Allows WordPress sites to index and search content with ApacheSolr.
  Version: 0.5.0
  Author: Pantheon
  Author URI: http://pantheon.io
 */
/*
  Copyright (c) 2011 Matt Weber

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
 */

/**
 * Echo the admin notice HTML that PHP is less than 5.4
 * and the Solr Power plugin has been deactivated or
 * cannot be activated.
 */
function solr_power_PHP_admin_notice() {
	?>
	<div class="error">
		<p>
			<?php
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );

				echo wp_kses_post( __(
					'The Solr Power plugin requires PHP 5.4 to function properly and <strong>has not</strong> been activated.<br />' .
					'Please upgrade PHP and re-activate the Solr Power plugin. ' .
					'<a href="http://www.wpupdatephp.com/update/" target="_blank">Learn more.</a>',
					'solr-for-wordpress-on-pantheon'
				) );
			} else {
				echo wp_kses_post( __(
					'The Solr Power plugin requires PHP 5.4 to function properly and had been <strong>deactivated</strong>.<br />' .
					'Please upgrade PHP and re-activate the Solr Power plugin. ' .
					'<a href="http://www.wpupdatephp.com/update/" target="_blank">Learn more.</a>',
					'solr-for-wordpress-on-pantheon'
				) );
			}
			?>
		</p>
	</div>
	<?php
}

/**
 * Echo the admin notice HTML that either the
 * PANTHEON_INDEX_HOST or PANTHEON_INDEX_PORT
 * environment variables do not exist and the
 * Solr Power plugin has been deactivated or
 * cannot be activated.
 */
function solr_power_env_variables_admin_notice() {
	?>
	<div class="error">
		<p>
			<?php
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );

				echo wp_kses_post(__(
					'The Solr Power plugin requires environment variables for <pre>PANTHEON_INDEX_HOST</pre> and <pre>PANTHEON_INDEX_PORT</pre> to function properly.<br />' .
					'The Solr Power plugin <strong>has not</strong> been activated.<br />' .
					'Please configure the environment variables and re-activate the Solr Power plugin. ',
					'solr-for-wordpress-on-pantheon'
				));
			} else {
				echo wp_kses_post(__(
					'The Solr Power plugin requires environment variables for <pre>PANTHEON_INDEX_HOST</pre> and <pre>PANTHEON_INDEX_PORT</pre> to function properly.<br />' .
					'The Solr Power plugin <strong>has been deactivated</strong>.<br />' .
					'Please configure the environment variables and re-activate the Solr Power plugin. ',
					'solr-for-wordpress-on-pantheon'
				));
			}
			?>
		</p>
	</div>
	<?php
}


/**
 * Deactivate the Solr Power plugin
 */
function solr_power__deactivate() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
}

if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
	add_action( 'admin_notices', 'solr_power_PHP_admin_notice' );
	add_action( 'admin_init', 'solr_power__deactivate' );
} elseif ( false === getenv( 'PANTHEON_INDEX_HOST' ) || false === getenv( 'PANTHEON_INDEX_PORT' ) ) {
	add_action( 'admin_notices', 'solr_power_env_variables_admin_notice' );
	add_action( 'admin_init', 'solr_power__deactivate' );
} else {

	define( 'SOLR_POWER_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	define( 'SOLR_POWER_URL', plugin_dir_url( __FILE__ ) );

	require_once( SOLR_POWER_PATH . '/vendor/autoload.php' );
	require_once( SOLR_POWER_PATH . '/includes/legacy-functions.php' );

	SolrPower::get_instance();
	SolrPower_Api::get_instance();
	SolrPower_Options::get_instance();
	SolrPower_Sync::get_instance();
	SolrPower_WP_Query::get_instance();

	function solr_options() {
		return SolrPower_Options::get_instance()->get_option();
	}

	/**
	 * Helper function to return Solr object.
	 */
	function get_solr() {
		return SolrPower_Api::get_instance()->get_solr();
	}

	if ( defined( 'WP_CLI' ) && true === WP_CLI ) {
		WP_CLI::add_command( 'solr', 'SolrPower_CLI' );
	}

	register_activation_hook( __FILE__, array( SolrPower::get_instance(), 'activate' ) );
}
