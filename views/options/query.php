<div id="solr_query" class="solrtab">
	<form method="post" action="<?php echo esc_url($action); ?>#top#solr_query">
		<?php wp_nonce_field( 'solr_action', 'solr_run_query' ); ?>
		<h3><?php esc_html_e( 'Solr Query', 'solr-for-wordpress-on-pantheon' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Words or phrases to search for.', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><textarea rows="3" cols="50" name="solrQuery"></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Submit Query', 'solr-for-wordpress-on-pantheon' ) ?></th>
				<td><input type="submit" class="button-primary" name="s4wp_query"
				           value="<?php esc_attr_e( 'Execute', 'solr-for-wordpress-on-pantheon' ) ?>"/>
				</td>
			</tr>
		</table>
		<input type="hidden" name="action" value="run_query"/>
	</form>
</div>
