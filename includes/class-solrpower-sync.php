<?php

class SolrPower_Sync {

	/**
	 * Singleton instance
	 * @var SolrPress_Sync|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPress_Sync
	 */
	public static function get_instance() {
		if ( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		add_action( 'publish_post', array( $this, 'handle_modified' ) );
		add_action( 'publish_page', array( $this, 'handle_modified' ) );
		add_action( 'save_post', array( $this, 'handle_modified' ) );
		add_action( 'delete_post', array( $this, 'handle_delete' ) );
		if ( is_multisite() ) {
			add_action( 'deactivate_blog', array( $this, 'delete_blog' ) );
			add_action( 'activate_blog', array( $this, 'handle_activate_blog' ) );
			add_action( 'archive_blog', array( $this, 'delete_blog' ) );
			add_action( 'unarchive_blog', array( $this, 'handle_activate_blog' ) );
			add_action( 'make_spam_blog', array( $this, 'delete_blog' ) );
			add_action( 'unspam_blog', array( $this, 'handle_activate_blog' ) );
			add_action( 'delete_blog', array( $this, 'delete_blog' ) );
			add_action( 'wpmu_new_blog', array( $this, 'handle_activate_blog' ) );
		}
	}

	function handle_modified( $post_id ) {
		global $current_blog;

		$post_info = get_post( $post_id );

		$plugin_s4wp_settings	 = s4wp_get_option();
		$index_pages			 = $plugin_s4wp_settings[ 's4wp_index_pages' ];
		$index_posts			 = $plugin_s4wp_settings[ 's4wp_index_posts' ];
		s4wp_handle_status_change( $post_id, $post_info );
		if ( $post_info->post_type == 'revision' ) {
			return;
		}
		$index_posts = $plugin_s4wp_settings[ 's4wp_index_posts' ];
		s4wp_handle_status_change( $post_id, $post_info );
		if ( $post_info->post_type == 'revision' ) {
			return;
		}
		# make sure this blog is not private or a spam if indexing on a multisite install
		if ( is_multisite() && ($current_blog->public != 1 || $current_blog->spam == 1 || $current_blog->archived == 1) ) {
			return;
		}
		$docs	 = array();
		$solr	 = s4wp_get_solr();
		$update	 = $solr->createUpdate();
		$doc	 = s4wp_build_document( $update->createDocument(), $post_info );

		if ( $doc ) {
			$docs[] = $doc;
			s4wp_post( $docs );
		}

		return;
	}

	function handle_delete( $post_id ) {
		global $current_blog;
		$post_info				 = get_post( $post_id );
		$plugin_s4wp_settings	 = s4wp_get_option();
		$delete_page			 = $plugin_s4wp_settings[ 's4wp_delete_page' ];
		$delete_post			 = $plugin_s4wp_settings[ 's4wp_delete_post' ];


		if ( is_multisite() ) {
			s4wp_delete( $current_blog->domain . $current_blog->path . $post_info->ID );
		} else {
			s4wp_delete( $post_info->ID );
		}
	}

	function handle_activate_blog( $blogid ) {
		$this->apply_config_to_blog( $blogid );
		$this->load_blog_all( $blogid );
	}

	function delete_blog( $blogid ) {
		try {
			$solr = s4wp_get_solr();
			if ( !$solr == NULL ) {
				$update = $solr->createUpdate();
				$update->addDeleteQuery( "blogid:{$blogid}" );
				$update->addCommit();
				$solr->update( $update );
			}
		} catch ( Exception $e ) {
			echo esc_html( $e->getMessage() );
		}
	}

	function apply_config_to_blog( $blogid ) {
		syslog( LOG_ERR, "applying config to blog with id $blogid" );
		if ( !is_multisite() )
			return;

		wp_cache_flush();
		$plugin_s4wp_settings = s4wp_get_option();
		switch_to_blog( $blogid );
		wp_cache_flush();
		s4wp_update_option( $plugin_s4wp_settings );
		restore_current_blog();
		wp_cache_flush();
	}

	function load_blog_all( $blogid ) {
		global $wpdb;
		$documents	 = array();
		$cnt		 = 0;
		$batchsize	 = 10;

		$bloginfo = get_blog_details( $blogid, FALSE );

		if ( $bloginfo->public && !$bloginfo->archived && !$bloginfo->spam && !$bloginfo->deleted ) {
			$query	 = $wpdb->prepare( "SELECT ID FROM %s WHERE post_type = 'post' and post_status = 'publish';", $wpdb->base_prefix . $blogid . '_posts' );
			$postids = $wpdb->get_results( $query );

			$solr	 = s4wp_get_solr();
			$update	 = $solr->createUpdate();

			for ( $idx = 0; $idx < count( $postids ); $idx++ ) {
				$postid		 = $ids[ $idx ];
				$documents[] = s4wp_build_document( $update->createDocument(), get_blog_post( $blogid, $postid->ID ), $bloginfo->domain, $bloginfo->path );
				$cnt++;
				if ( $cnt == $batchsize ) {
					s4wp_post( $documents );
					$cnt		 = 0;
					$documents	 = array();
				}
			}

			if ( $documents ) {
				s4wp_post( $documents );
			}
		}
	}

}
