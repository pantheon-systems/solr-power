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

}
