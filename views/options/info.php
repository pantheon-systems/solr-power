<div id="solr_info" class="solrtab active">
	<?php
	$server_info = SolrPower_Api::get_instance()->get_server_info();
	?>
	<div class="solr-display">
		<table class="form-table">
			<thead>
			<tr>
				<th colspan="2"><strong>Solr Configuration</strong></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>Ping Status:</td>
				<td><?php echo ( $server_info['ping_status'] ) ? '<span class="solr-green">Successful</span>' : '<span class="solr-red">Failed</span>'; ?></td>
			</tr>
			<tr>
				<td>Solr Server IP address:</td>
				<td><?php echo esc_html( $server_info['ip_address'] ); ?></td>
			</tr>
			<tr>
				<td>Solr Server Port:</td>
				<td><?php echo esc_html( getenv( $server_info['port'] ) ); ?></td>
			</tr>
			<tr>
				<td>Solr Server Path:</td>
				<td><?php echo esc_html( $server_info['path'] ); ?></td>
			</tr>
			</tbody>

		</table>
	</div>
	<?php if ( $server_info['ping_status'] ) { ?>
		<div class="solr-display">
			<table class="form-table">
				<thead>
				<tr>
					<th colspan="2"><strong>Indexing Stats by Post Type</strong></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( SolrPower_Api::get_instance()->index_stats() as $type => $stat ) {
					?>
					<tr>
						<td><?php echo esc_html( $type ); ?>:</td>
						<td><?php echo absint( $stat ); ?></td>
					</tr>
				<?php } ?>
				</tbody>

			</table>
		</div>
	<?php } ?>
	<br class="clear">
</div>