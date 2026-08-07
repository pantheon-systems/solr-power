<?php
/**
 * Renders the actions tab
 *
 * @package Solr_Power
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="solr_action" class="solrtab">

	<h3><?php esc_html_e( 'Actions', 'solr-power' ) ?></h3>
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
		<?php wp_nonce_field( 'solr_action', 'solr_ping' ); ?>
		<input type="hidden" name="action" value="ping" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Check Server Settings', 'solr-power' ) ?></th>
				<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_ping" value="<?php esc_attr_e( 'Execute', 'solr-power' ) ?>" /></td>
			</tr>
		</table>
	</form>
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
		<?php wp_nonce_field( 'solr_action', 'solr_optimize' ); ?>
		<input type="hidden" name="action" value="optimize" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Optimize Index', 'solr-power' ) ?></th>
				<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_optimize" value="<?php esc_attr_e( 'Execute', 'solr-power' ) ?>" /></td>
			</tr>
		</table>
	</form>
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
		<?php wp_nonce_field( 'solr_action', 'solr_delete_all' ); ?>
		<input type="hidden" name="action" value="delete_all" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Delete All', 'solr-power' ) ?></th>
				<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_deleteall" value="<?php esc_attr_e( 'Execute', 'solr-power' ) ?>" /></td>
			</tr>
		</table>
	</form>
	<?php
	if ( false !== getenv( 'PANTHEON_ENVIRONMENT' ) ) {
		?>
		<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
			<?php wp_nonce_field( 'solr_action', 'solr_repost_schema' ); ?>
			<input type="hidden" name="action" value="repost_schema" />
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Repost schema.xml', 'solr-power' ) ?></th>
					<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_repost_schema" value="<?php esc_attr_e( 'Execute', 'solr-power' ) ?>" /></td>
				</tr>
				<tr valign="top">
					<?php
					$dir = dirname( SolrPower_Api::custom_schema_file_path() );
					?>
					<td scope="row" colspan="2">To use a custom schema.xml, upload it to the
						<b><?php echo esc_html( $dir ); ?></b>
						directory.
					</td>
				</tr>
			</table>
		</form>
	<?php } ?>
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
		<input type="hidden" name="action" value="index_all" />
		<table class="form-table">

			<tr valign="top">
				<?php if ( is_multisite() ) : ?>
					<th scope="row"><?php esc_html_e( 'Index Searchable Post Types with WP-CLI', 'solr-power' ) ?></th>
					<td>
						<p>To index a single site, use the <code>--url=&lt;url&gt;</code> argument:</p>
						<pre>wp --url=example.com/site1 solr index</pre>
						<p>To index all sites, use <code>xargs</code> to pass the list of sites:</p>
						<pre>wp site list --field=url | xargs -n1 -I % wp --url=% solr index</pre>
					</td>
				<?php else : ?>
					<th scope="row"><?php esc_html_e( 'Index Searchable Post Types', 'solr-power' ) ?></th>
					<td id="solr-batch-index"><?php /** Rendered with JS **/ ?></td>
				<?php endif; ?>
			</tr>
		</table>
	</form>
</div>

<?php if ( ! is_multisite() ) : ?>
	<?php
	$batch_index     = new SolrPower_Batch_Index;
	$current_batch   = $batch_index->get_current_batch();
	$total_batches   = $batch_index->get_total_batches();
	$remaining_posts = $batch_index->get_remaining_posts();
	$total_posts     = $batch_index->get_total_posts();
	?>
<script type="text/html" id="tmpl-solr-batch-index" data-current-batch="<?php echo (int) $current_batch; ?>" data-total-batches="<?php echo (int) $total_batches; ?>" data-remaining-posts="<?php echo (int) $remaining_posts; ?>" data-total-posts="<?php echo (int) $total_posts; ?>">
	<# if ( data.elapsedTime ) { #>
		<div class="solr-indexing-message">
		<# if ( data.remainingPosts > 0 ) { #>
		<?php
		// translators: Displays batch index progress message.
		echo esc_attr( sprintf( __( 'Running batch %1$s of %2$s at %3$s elapsed time (%4$s indexed, %5$s failed, %6$s remaining)', 'solr-power' ), '{{ data.currentBatch }}', '{{ data.totalBatches }}', '{{ data.elapsedTime }}', '{{ data.successPosts }}', '{{ data.failedPosts }}', '{{ data.remainingPosts }}' ) );
		?>
		<# } else { #>
		<?php
		// translators: Displays batch index completion message.
		echo esc_html( sprintf( __( 'Completed indexing in %1$s elapsed time (%2$s indexed, %3$s failed)', 'solr-power' ), '{{ data.elapsedTime }}', '{{ data.successPosts }}', '{{ data.failedPosts }}' ) );
		?>
		<# } #>
		</div>
	<# } else { #>
		<# if ( data.currentBatch > 1 ) { #>
		<input type="button" class="button-primary solr-admin-action" name="s4wp_resume_index" value="
		<?php
		// translators: Displays batch index start message.
		echo esc_attr( sprintf( __( 'Resume at batch %1$s of %2$s', 'solr-power' ), '{{ data.currentBatch }}', '{{ data.totalBatches }}' ) );
		?>
		" /> <input type="button" class="button solr-admin-action" name="s4wp_start_index" value="<?php esc_attr_e( 'Restart', 'solr-power' ); ?>" />
		<# } else { #>
		<input type="button" class="button-primary solr-admin-action" name="s4wp_start_index" value="<?php esc_attr_e( 'Start Index', 'solr-power' ); ?>" />
		<# } #>
	<# } #>
</script>
<?php endif; ?>
