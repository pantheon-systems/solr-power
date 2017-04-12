<?php
/*
    Copyright (c) 2009 Matt Weber

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
?>
<div id="solr_header">
    <h2>Solr Power <span>Version <?php echo esc_html( SOLR_POWER_VERSION ); ?></span></h2>
</div>
<?php
// Load up options.
$s4wp_settings = solr_options();

// Display a message if one is set.
if ( ! is_null( SolrPower_Options::get_instance()->msg ) ) {
	?>
    <div id="message">
        <p>
            <strong><?php echo wp_kses_post( SolrPower_Options::get_instance()->msg ); ?></strong>
        </p>
    </div>
	<?php
}
?>

<div class="wrap">
    <div class="solr-power-subpage">

        <h2 class="nav-tab-wrapper" id="solr-tabs">

            <a class="nav-tab <?php echo ( ! isset( $_GET['settings-updated'] ) ) ? 'nav-tab-active' : ''; ?>"
               id="solr_info-tab"
               href="#top#solr_info"><span class="dashicons dashicons-info"></span>
				<?php esc_html_e( 'Info', 'solr-for-wordpress-on-pantheon' ); ?>
            </a>
            <a class="nav-tab" id="solr_action-tab"
               href="#top#solr_action"><span class="dashicons dashicons-performance"></span>
				<?php esc_html_e( 'Actions', 'solr-for-wordpress-on-pantheon' ); ?>
            </a>
            <a class="nav-tab" id="solr_indexing-tab"
               href="#top#solr_indexing"><span class="dashicons dashicons-admin-page"></span>
				<?php esc_html_e( 'Indexing Options', 'solr-for-wordpress-on-pantheon' ); ?>
            </a>
            <a class="nav-tab" id="solr_facet-tab"
               href="#top#solr_facet"><span class="dashicons dashicons-forms"></span>
				<?php esc_html_e( 'Facet Options', 'solr-for-wordpress-on-pantheon' ); ?>
            </a>
        </h2>


		<?php
		if ( is_multisite() ) {
			$action = 'settings.php?page=solr-power';
		} else {
			$action = 'admin.php?page=solr-power';
		}
		include 'views/options/info.php';
		include 'views/options/action.php';

		$options_action = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'options.php' );
		?>
		<form method="post" action="<?php echo esc_url( $options_action ); ?>">
		<?php
		include 'views/options/indexing.php';
		include 'views/options/facet.php';
		?>
		</form>
	</div>
</div>
