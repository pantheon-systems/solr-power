<?php

class Test_Batch_Index extends SolrTestBase {

	public function test_batch_index_all_posts_one_call() {
		$this->__create_multiple( 5 );
		SolrPower_Sync::get_instance()->delete_all();
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 0, $stats['post'] );
		$batch_index = new SolrPower_Batch_Index;
		$this->assertEquals( 1, $batch_index->get_current_batch() );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 5, $batch_index->get_success_posts() );
		$this->assertEquals( 0, $batch_index->get_failed_posts() );
		$this->assertEquals( 0, $batch_index->get_remaining_posts() );
		$this->assertEquals( 1, $batch_index->get_total_batches() );
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 5, $stats['post'] );
	}

	public function test_batch_index_all_posts_multiple_batches() {
		$this->__create_multiple( 5 );
		SolrPower_Sync::get_instance()->delete_all();
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 0, $stats['post'] );
		// First batch
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$this->assertEquals( 1, $batch_index->get_current_batch() );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 2, $batch_index->get_success_posts() );
		$this->assertEquals( 0, $batch_index->get_failed_posts() );
		$this->assertEquals( 3, $batch_index->get_remaining_posts() );
		$this->assertEquals( 3, $batch_index->get_total_batches() );
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 2, $stats['post'] );
		// Iterate to the next set, but don't start it.
		$batch_index->fetch_next_posts();
		// Second batch
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$this->assertEquals( 2, $batch_index->get_current_batch() );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 2, $batch_index->get_success_posts() );
		$this->assertEquals( 0, $batch_index->get_failed_posts() );
		$this->assertEquals( 1, $batch_index->get_remaining_posts() );
		$this->assertEquals( 3, $batch_index->get_total_batches() );
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 4, $stats['post'] );
		// Iterate to the next set, but don't start it.
		$batch_index->fetch_next_posts();
		// Third batch
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$this->assertEquals( 3, $batch_index->get_current_batch() );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 1, $batch_index->get_success_posts() );
		$this->assertEquals( 0, $batch_index->get_failed_posts() );
		$this->assertEquals( 0, $batch_index->get_remaining_posts() );
		$this->assertEquals( 3, $batch_index->get_total_batches() );
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 5, $stats['post'] );
	}

	public function test_batch_index_resume_first_batch() {
		$this->__create_multiple( 5 );
		SolrPower_Sync::get_instance()->delete_all();
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 0, $stats['post'] );
		// First batch
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$this->assertEquals( 1, $batch_index->get_current_batch() );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 2, $batch_index->get_success_posts() );
		$this->assertEquals( 0, $batch_index->get_failed_posts() );
		$this->assertEquals( 3, $batch_index->get_remaining_posts() );
		$this->assertEquals( 3, $batch_index->get_total_batches() );
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 2, $stats['post'] );
		// Iterate to the next set, but don't start it.
		$batch_index->fetch_next_posts();
		// Second batch
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$this->assertEquals( 2, $batch_index->get_current_batch() );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 2, $batch_index->get_success_posts() );
		$this->assertEquals( 0, $batch_index->get_failed_posts() );
		$this->assertEquals( 1, $batch_index->get_remaining_posts() );
		$this->assertEquals( 3, $batch_index->get_total_batches() );
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 4, $stats['post'] );
		// Iterate to the next set, but don't start it.
		$batch_index->fetch_next_posts();
		// Restart back at the first batch
		$batch_index = new SolrPower_Batch_Index( array( 'batch' => 1, 'posts_per_page' => 2 ) );
		$this->assertEquals( 1, $batch_index->get_current_batch() );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 2, $batch_index->get_success_posts() );
		$this->assertEquals( 0, $batch_index->get_failed_posts() );
		$this->assertEquals( 3, $batch_index->get_remaining_posts() );
		$this->assertEquals( 3, $batch_index->get_total_batches() );
		$stats = SolrPower_Api::get_instance()->index_stats();
		// Even though we restarted batch indexing, all existing posts remain indexed
		$this->assertEquals( 4, $stats['post'] );
	}

	public function test_batch_index_restart_after_delete_all() {
		$this->__create_multiple( 5 );
		SolrPower_Sync::get_instance()->delete_all();
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 0, $stats['post'] );
		// First batch
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$batch_index->fetch_next_posts();
		while( $batch_index->have_posts() ) {
			$batch_index->index_post();
		}
		$this->assertEquals( 4, $batch_index->get_success_posts() );
		$this->assertEquals( 1, $batch_index->get_remaining_posts() );
		// Delete all should reset the index.
		SolrPower_Sync::get_instance()->delete_all();
		$stats = SolrPower_Api::get_instance()->index_stats();
		$this->assertEquals( 0, $stats['post'] );
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$this->assertEquals( 5, $batch_index->get_remaining_posts() );
	}

	public function mock_solarium_client($solr) {
		return new MockSolariumClient($solr->getOptions());
	}

	public function test_returns_contents_of_h1_tags_if_present_in_error() {
		// can i create a new class, return it via s4wp_solr, and throw an exception in __construct ?
		add_filter( 's4wp_solr', array( $this, 'mock_solarium_client' ), 1, 1 );

		$this->__create_multiple( 1 );
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$batch_index->have_posts();

		MockSolariumClient::$error_msg = "<h1>danger will robinson</h1>";
		$result = $batch_index->index_post();

		$this->assertEquals( "danger will robinson", $result['message'] );
	}

	public function test_returns_contents_of_title_tags_if_present_in_error() {
		add_filter( 's4wp_solr', array( $this, 'mock_solarium_client' ), 1, 1 );

		$this->__create_multiple( 1 );
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$batch_index->have_posts();

		MockSolariumClient::$error_msg = "<title>vague error</title>";
		$result = $batch_index->index_post();

		$this->assertEquals( "vague error", $result['message'] );
	}

	public function test_h1_takes_precedence_over_title_in_error() {
		add_filter( 's4wp_solr', array( $this, 'mock_solarium_client' ), 1, 1 );

		$this->__create_multiple( 1 );
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$batch_index->have_posts();

		MockSolariumClient::$error_msg = "<title>vague error</title><h1>specific error</h1>";
		$result = $batch_index->index_post();

		$this->assertEquals( "specific error", $result['message'] );
	}

	public function test_returns_full_error_message_if_no_title_or_h1_is_present() {
		add_filter( 's4wp_solr', array( $this, 'mock_solarium_client' ), 1, 1 );

		$this->__create_multiple( 1 );
		$batch_index = new SolrPower_Batch_Index( array( 'posts_per_page' => 2 ) );
		$batch_index->have_posts();

		MockSolariumClient::$error_msg = "full error";
		$result = $batch_index->index_post();

		$this->assertEquals( "full error", $result['message'] );
	}

}


class MockSolariumClient extends Solarium\Client {
	public static $error_msg = "";

	public function update(Solarium\Core\Query\QueryInterface $update, $endpoint = null) {
		throw new Exception(self::$error_msg);
	}
}


