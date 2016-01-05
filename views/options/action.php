<div id="solr_action" class="solrtab">
	<form method="post" action="<?php echo $action; ?>#top#solr_action">
		<h3><?php _e( 'Actions', 'solr4wp' ) ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Check Server Settings', 'solr4wp' ) ?></th>
				<td><input type="submit" class="button-primary" name="s4wp_ping" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
			</tr>

			<?php if ( is_multisite() ) { ?>
				<tr valign="top">
					<th scope="row"><?php _e( 'Push Solr Configuration to All Blogs', 'solr4wp' ) ?></th>
					<td><input type="submit" class="button-primary" name="s4wp_init_blogs" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
				</tr>
			<?php } ?>

			<tr valign="top">
				<th scope="row"><?php _e( 'Optimize Index', 'solr4wp' ) ?></th>
				<td><input type="submit" class="button-primary" name="s4wp_optimize" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Delete All', 'solr4wp' ) ?></th>
				<td><input type="submit" class="button-primary" name="s4wp_deleteall" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
			</tr>
			<?php if ( getenv( 'PANTHEON_ENVIRONMENT' ) !== false ) { ?>
				<tr valign="top">
					<th scope="row"><?php _e( 'Repost schema.xml', 'solr4wp' ) ?></th>
					<td><input type="submit" class="button-primary" name="s4wp_repost_schema" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
				</tr>
				<tr valign="top">
					<td scope="row" colspan="2">To use a custom schema.xml, upload it to the <b>/wp-content/uploads/solr-for-wordpress-on-pantheon/</b> directory.</td>
				</tr>
			<?php } ?>
	</form -->

    <!-- tr valign="top">
        <th scope="row"><?php _e( 'Load All Pages', 'solr4wp' ) ?></th>
        <td><input type="submit" class="button-primary" name="s4wp_pageload" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php _e( 'Load All Posts', 'solr4wp' ) ?></th>
        <td><input type="submit" class="button-primary" name="s4wp_postload['posts']" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
    </tr -->

	<!--
	  Let's loop through each Post type, and give an option to add them into the
	  Solr index.
	-->
	<?php
	$postTypes = get_post_types( array( 'exclude_from_search' => false ) );

	foreach ( $postTypes as $postType ) {
		?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Load All ' . esc_html( $postType ) . '(s)', 'solr4wp' ) ?></th>
			<td><input type="button" class="button-primary s4wp_postload_<?php print(esc_attr( $postType ) ); ?>" name="s4wp_postload_<?php print(esc_attr( $postType ) ); ?>" value="<?php _e( 'Execute', 'solr4wp' ) ?>" /></td>
		</tr>
	<?php } ?>
</table>
</div>