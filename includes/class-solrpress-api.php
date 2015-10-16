<?php

class SolrPress_Api {

	/**
	 * Singleton instance
	 * @var SolrPress_Api|Bool
	 */
	private static $instance = false;

	/**
	 * Grab instance of object.
	 * @return SolrPress_Api
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
			add_action( 'deactivate_blog', 's4wp_handle_deactivate_blog' );
			add_action( 'activate_blog', 's4wp_handle_activate_blog' );
			add_action( 'archive_blog', 's4wp_handle_archive_blog' );
			add_action( 'unarchive_blog', 's4wp_handle_unarchive_blog' );
			add_action( 'make_spam_blog', 's4wp_handle_spam_blog' );
			add_action( 'unspam_blog', 's4wp_handle_unspam_blog' );
			add_action( 'delete_blog', 's4wp_handle_delete_blog' );
			add_action( 'wpmu_new_blog', 's4wp_handle_new_blog' );
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
	
	function handle_delete($post_id) {
    global $current_blog;
    $post_info = get_post($post_id);
    $plugin_s4wp_settings = s4wp_get_option();
    $delete_page = $plugin_s4wp_settings['s4wp_delete_page'];
    $delete_post = $plugin_s4wp_settings['s4wp_delete_post'];


        if (is_multisite()) {
            s4wp_delete($current_blog->domain . $current_blog->path . $post_info->ID);
        } else {
            s4wp_delete($post_info->ID);
        }

}

}
