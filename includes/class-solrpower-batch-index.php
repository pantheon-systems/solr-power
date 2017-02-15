<?php

/**
 * Index a batch of WordPress posts
 */
class SolrPower_Batch_Index {

	/**
	 * Total posts to be indexed.
	 *
	 * @var integer
	 */
	private $total_posts;

	/**
	 * Remaining posts to be indexed.
	 *
	 * @var integer
	 */
	private $remaining_posts;

	/**
	 * Query arguments used to fetch post ids from WP_Query.
	 *
	 * @var array
	 */
	private $query_args;

	/**
	 * Solr update instance
	 *
	 * @var object
	 */
	private $solr_update;

	/**
	 * Number of posts successfully indexed.
	 *
	 * @var integer
	 */
	private $success_posts = 0;

	/**
	 * Number of posts failed to index.
	 *
	 * @var integer
	 */
	private $failed_posts = 0;

	/**
	 * Post ids to index.
	 *
	 * @var array
	 */
	private $post_ids = array();

	/**
	 * Instantiate the batch index object.
	 */
	public function __construct( $query_args = array() ) {
		$defaults = array(
			'post_status'     => 'publish',
			'post_type'       => apply_filters( 'solr_post_types', get_post_types( array( 'exclude_from_search' => false ) ) ),
			'orderby'         => 'ID',
			'order'           => 'ASC',
			'posts_per_page'  => 500,
			'paged'           => 1,
		);
		$clean_query_args = array();
		foreach( $defaults as $key => $value ) {
			$clean_query_args[ $key ] = isset( $query_args[ $key ] ) ? $query_args[ $key ] : $value;
		}
		$clean_query_args['fields'] = 'ids'; // Always need to use post ids
		$this->query_args = $clean_query_args;
		$query = new WP_Query( $clean_query_args );
		$this->post_ids = $query->posts;
		$this->total_posts = $this->remaining_posts = $query->found_posts;
		// Initialize the Solr updater
		$solr = get_solr();
		$this->solr_update = $solr->createUpdate();
	}

	/**
	 * Get the total posts to be indexed.
	 *
	 * @return integer
	 */
	public function get_total_posts() {
		return $this->total_posts;
	}

	/**
	 * Get the remaining posts to be indexed.
	 *
	 * @return integer
	 */
	public function get_remaining_posts() {
		return $this->remaining_posts;
	}

	/**
	 * Get the number of posts successfully indexed.
	 *
	 * @return integer
	 */
	public function get_success_posts() {
		return $this->success_posts;
	}

	/**
	 * Get the number of posts that failed to be indexed.
	 *
	 * @return integer
	 */
	public function get_failed_posts() {
		return $this->failed_posts;
	}

	/**
	 * Whether or not there are remaining posts to index.
	 *
	 * @return bool
	 */
	public function have_posts() {
		if ( ! empty( $this->post_ids ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the current page for this batch index.
	 *
	 * @return integer
	 */
	public function get_current_page() {
		return $this->query_args['paged'];
	}

	/**
	 * Fetch the next posts to index.
	 *
	 * @return bool
	 */
	public function fetch_next_posts() {
		self::clear_object_cache();
		$this->query_args['paged'] = $this->query_args['paged'] + 1;
		$query = new WP_Query( $this->query_args );
		$this->post_ids = $query->posts;
		if ( ! empty( $this->post_ids ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Index the next post in the stack.
	 *
	 * @return array Result of the index process.
	 */
	public function index_post() {
		$result = array(
			'post_id'    => 0,
			'post_title' => '',
			'status'     => '',
			'message'    => '',
		);
		if ( empty( $this->post_ids ) ) {
			return $result;
		}
		$post_id = array_shift( $this->post_ids );
		$post = get_post( $post_id );
		$result['post_id'] = $post_id;
		$result['post_title'] = html_entity_decode( $post->post_title );
		$documents[] = SolrPower_Sync::get_instance()->build_document( $this->solr_update->createDocument(), $post );
		$sync_result = SolrPower_Sync::get_instance()->post( $documents, true, FALSE );
		if ( false !== $sync_result ) {
			$this->success_posts++;
			$result['status'] = 'success';
		} else {
			$this->failed_posts++;
			$result['status'] = 'failed';
			$result['message'] = SolrPower_Sync::get_instance()->error_msg;
		}
		$this->remaining_posts--;
		return $result;
	}

	/**
	 * Clear objects in WordPress internal object cache.
	 *
	 * Because WordPress' in-memory cache can grow indefinitely, it needs to
	 * be cleared occasionally for long processes.
	 */
	private static function clear_object_cache() {
		global $wpdb, $wp_object_cache;

		$wpdb->queries = array(); // or define( 'WP_IMPORTING', true );

		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}

		$wp_object_cache->group_ops = array();
		$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();

		if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important
		}
	}

}