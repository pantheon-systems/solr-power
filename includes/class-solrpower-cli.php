<?php

class SolrPower_CLI extends WP_CLI_Command {

	/**
	 * Index all posts for a site.
	 *
	 * @synopsis [--posts_per_page] [--post_type]
	 *
	 * @param array $args
	 *
	 *
	 * @param array $assoc_args
	 */
	public function index( $args, $assoc_args ) {
		$defaults = array(
			'posts_per_page' => 300,
			'post_status'	 => 'publish',
			'fields'		 => 'ids',
			'paged'			 => 1,
			'post_type'		 => get_post_types( array( 'exclude_from_search' => false ) ),
		);
		// Check if specified post_type is valid.
		if ( isset( $assoc_args[ 'post_type' ] ) && (false === post_type_exists( $assoc_args[ 'post_type' ] )) ) {
			WP_CLI::error( '"' . $assoc_args[ 'post_type' ] . '" is an invalid post type.' );
		}
		$query_args		 = array_merge( $defaults, $assoc_args );
		$query			 = new WP_Query( $query_args );
		$current_page	 = $query->get( 'paged' );
		$total			 = $query->max_num_pages;
		// There's a bug with found_posts that shows two more than what it should.
		$total_posts	 = (1 == $query->max_num_pages) ? $query->post_count : $query->found_posts - 2;

		$notify	 = \WP_CLI\Utils\make_progress_bar( 'Indexing Items:', $total_posts );
		$done	 = 0;
		$failed	 = 0;
		$solr	 = get_solr();
		$update	 = $solr->createUpdate();
		while ( $current_page <= $total ) {
			$query->set( 'paged', $current_page );
			$query->get_posts();
			foreach ( $query->posts as $id ) {
				$documents	 = array();
				$documents[] = SolrPower_Sync::get_instance()->build_document( $update->createDocument(), get_post( $id ) );
				$post_it	 = SolrPower_Sync::get_instance()->post( $documents, true, FALSE );

				if ( false === $post_it ) {
					$failed++;
				} else {
					$done++;
				}
				$notify->tick();
			}
			$current_page++;
		}
		$notify->finish();
		WP_CLI::success( sprintf( '%d of %d items indexed.', $done, $total_posts ) );
		if ( 0 < $failed ) {
			WP_CLI::error( 'Failed to index ' . $failed . ' item(s).' );
			WP_CLI::error( SolrPower_Sync::get_instance()->error_msg );
		}
	}

	/**
	 * Remove one or all posts from a Solr index.
	 * 
	 * ## EXAMPLES
	 *
	 * wp solr delete
	 * wp solr delete 123
	 *
	 *
	 * @param array $args
	 * 
	 */
	function delete( $args ) {
		$post_id = false;
		if ( isset( $args[ 0 ] ) ) {
			$post_id = $args[ 0 ];
		}
		if ( $post_id ) {
			$status = SolrPower_Sync::get_instance()->delete( absint( $post_id ) );

			$msg = ($status) ? 'Post #' . absint( $post_id ) . ' successfully removed from index.' : SolrPower_Sync::get_instance()->error_msg;
		} else {
			$status	 = SolrPower_Sync::get_instance()->delete_all();
			$msg	 = ($status) ? 'All posts successfully removed from index.' : SolrPower_Sync::get_instance()->error_msg;
		}
		if ( $status ) {
			WP_CLI::success( $msg );
			return;
		}
		WP_CLI::error( $msg );
	}

}

WP_CLI::add_command( 'solr', 'SolrPower_CLI' );
