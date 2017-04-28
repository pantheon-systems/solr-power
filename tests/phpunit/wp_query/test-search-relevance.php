<?php

class SolrSearchRelevanceTest extends SolrTestBase {

	function test_search_order_title_exact_match_most_relevant() {
		foreach ( range( 1, 7 ) as $i ) {
			self::factory()->post->create( array(
				'post_title'   => $i . ' ' . rand_str() . ' Star Wars ',
				'post_content' => 'Star Wars Star Wars ' . $i . rand_str() . ' Star Wars',
				'post_type'    => 'post',
			) );
		}
		$post_id = self::factory()->post->create( array( 'post_title' => 'Star Wars', 'post_type' => 'post' ) );
		self::factory()->post->create( array( 'post_title' => 'Not about Star Wars or something', 'post_type' => 'post' ) );
		self::factory()->post->create( array( 'post_title' => 'Not about Star Wars or anything', 'post_type' => 'post' ) );
		$query = new WP_Query( array(
			's'         => 'Star Wars',
			'post_type' => 'post',
		) );
		$posts = $query->posts;
		$this->assertEquals( $post_id, reset( $posts )->ID );
	}

}
