<div id="solr_action" class="solrtab">

	<h3><?php esc_html_e( 'Actions', 'solr-for-wordpress-on-pantheon' ) ?></h3>
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
		<?php wp_nonce_field( 'solr_action', 'solr_ping' ); ?>
		<input type="hidden" name="action" value="ping" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Check Server Settings', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_ping"
						   value="<?php esc_attr_e( 'Execute', 'solr-for-wordpress-on-pantheon' ) ?>" /></td>
			</tr>
		</table>
	</form>
	<?php if ( is_multisite() ) { ?>
		<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
			<?php wp_nonce_field( 'solr_action', 'solr_init_blogs' ); ?>
			<input type="hidden" name="action" value="init_blogs" />
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Push Solr Configuration to All Blogs', 'solr-for-wordpress-on-pantheon' ) ?></th>
					<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_init_blogs"
							   value="<?php esc_attr_e( 'Execute', 'solr-for-wordpress-on-pantheon' ) ?>" /></td>
				</tr>
			</table>
		</form>
	<?php } ?>
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
		<?php wp_nonce_field( 'solr_action', 'solr_optimize' ); ?>
		<input type="hidden" name="action" value="optimize" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Optimize Index', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_optimize"
						   value="<?php esc_attr_e( 'Execute', 'solr-for-wordpress-on-pantheon' ) ?>" /></td>
			</tr>
		</table>
	</form>
	<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
		<?php wp_nonce_field( 'solr_action', 'solr_delete_all' ); ?>
		<input type="hidden" name="action" value="delete_all" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Delete All', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_deleteall"
						   value="<?php esc_attr_e( 'Execute', 'solr-for-wordpress-on-pantheon' ) ?>" /></td>
			</tr>
		</table>
	</form>
	<?php
	if ( false !== getenv( 'PANTHEON_ENVIRONMENT' ) ) { ?>
		<form method="post" action="<?php echo esc_url( $action ); ?>#top#solr_action">
			<?php wp_nonce_field( 'solr_action', 'solr_repost_schema' ); ?>
			<input type="hidden" name="action" value="repost_schema" />
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Repost schema.xml', 'solr-for-wordpress-on-pantheon' ) ?></th>
					<td><input type="submit" class="button-primary solr-admin-action" name="s4wp_repost_schema"
							   value="<?php esc_attr_e( 'Execute', 'solr-for-wordpress-on-pantheon' ) ?>" /></td>
				</tr>
				<tr valign="top">
					<td scope="row" colspan="2">To use a custom schema.xml, upload it to the
						<b>/wp-content/uploads/solr-for-wordpress-on-pantheon/</b>
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
				<th scope="row"><?php esc_html_e( 'Index Searchable Post Types', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td>
				<?php
					$batch_index = new SolrPower_Batch_Index;
					$current_batch = $batch_index->get_current_batch();
					$total_batches = $batch_index->get_total_batches();
					if ( $current_batch > 1 ) : ?>
					<input type="button" class="button-primary solr-admin-action" name="s4wp_resume_index" value="<?php echo esc_attr( sprintf( __( 'Resume at batch %d of %d', 'solr-for-wordpress-on-pantheon' ), $current_batch, $total_batches ) ); ?>" /> <input type="button" class="button solr-admin-action" name="s4wp_start_index" value="<?php esc_attr_e( 'Restart', 'solr-for-wordpress-on-pantheon' ); ?>" />
					<?php else : ?>
					<input type="button" class="button-primary solr-admin-action" name="s4wp_start_index" value="<?php esc_attr_e( 'Start Index', 'solr-for-wordpress-on-pantheon' ); ?>" />
					<?php endif; ?>
				</td>
			</tr>
		</table>
	</form>
</div>
