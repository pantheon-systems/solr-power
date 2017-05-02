<?php

class SolrSearchRelevanceTest extends SolrTestBase {

	function test_search_order_title_exact_match_most_relevant() {
		foreach ( range( 1, 7 ) as $i ) {
			self::factory()->post->create( array(
				'post_title'   => $i . ' ' . rand_str(),
				'post_content' => 'Star Wars Star Wars ' . $i . rand_str() . ' Star Wars Star Wars Star Wars Star Wars Star Wars Star Wars Star Wars',
				'post_type'    => 'post',
			) );
		}
		$post_id = self::factory()->post->create( array( 'post_title' => 'Star Wars', 'post_type' => 'post' ) );
		$query = new WP_Query( array(
			's'         => 'Star Wars',
			'post_type' => 'post',
		) );
		$posts = $query->posts;
		$this->assertEquals( $post_id, reset( $posts )->ID );
	}

}
