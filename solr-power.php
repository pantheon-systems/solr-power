<?php

/*
  Plugin Name: Solr Power
  Description: Allows WordPress sites to index and search content with ApacheSolr.
  Version: 0.3.0
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

/*
 * NOTE: We have had to hack the Solarium Curl class to get it to support
 * https:. There is probably a better way to do this and a future version
 * may include a new "Pantheon" provider for Solarium. Until then, if you
 * do a composer update, and it updates, Solarium, things WILL STOP
 * WORKING.
 *
 * Make a backup!
 *  - Cal
 *
 * @TODO refactor as an object
 *
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

				_e(
					'The Solr Power plugin requires PHP 5.4 to function properly and <strong>has not</strong> been activated.<br />' .
					'Please upgrade PHP and re-activate the Solr Power plugin. ' .
					'<a href="http://www.wpupdatephp.com/update/" target="_blank">Learn more.</a>',
					'solr-for-wordpress-on-pantheon'
				);
			} else {
				_e(
					'The Solr Power plugin requires PHP 5.4 to function properly and had been <strong>deactivated</strong>.<br />' .
					'Please upgrade PHP and re-activate the Solr Power plugin. ' .
					'<a href="http://www.wpupdatephp.com/update/" target="_blank">Learn more.</a>',
					'solr-for-wordpress-on-pantheon'
				);
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
} else {
	define( 'SOLR_POWER_PATH', plugin_dir_path( __FILE__ ) . '/' );
	define( 'SOLR_POWER_URL', plugin_dir_url( __FILE__ ) );

	require_once( SOLR_POWER_PATH . '/vendor/autoload.php' );
	require_once( SOLR_POWER_PATH . '/includes/class-solrpower.php' );
	require_once( SOLR_POWER_PATH . '/includes/class-solrpower-options.php' );
	require_once( SOLR_POWER_PATH . '/includes/class-solrpower-sync.php' );
	require_once( SOLR_POWER_PATH . '/includes/class-solrpower-api.php' );
	require_once( SOLR_POWER_PATH . '/includes/class-solrpower-wp-query.php' );
	require_once( SOLR_POWER_PATH . '/includes/legacy-functions.php' );
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once( SOLR_POWER_PATH . '/includes/class-solrpower-cli.php' );
	}
	register_activation_hook( __FILE__, array( SolrPower::get_instance(), 'activate' ) );
}
