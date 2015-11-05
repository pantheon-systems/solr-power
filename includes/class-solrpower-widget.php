<?php

class SolrPower_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_s4wp_mlt', 'description' => __( "Displays a list of pages similar to the page being viewed" ) );
		$this->WP_Widget( 'mlt', __( 'Similar' ), $widget_ops );
	}

	function escape( $value ) {
		//list taken from http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
		$pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
		$replace = '\\\$1';

		return preg_replace( $pattern, $replace, $value );
	}

	function widget( $args, $instance ) {

		extract( $args );
		$title	 = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? __( 'Similar' ) : $instance[ 'title' ]  );
		$count	 = empty( $instance[ 'count' ] ) ? 5 : $instance[ 'count' ];
		if ( !is_numeric( $count ) ) {
			$count = 5;
		}

		$showauthor = $instance[ 'showauthor' ];

		$solr		 = get_solr();
		$response	 = NULL;

		if ( (!is_single() && !is_page()) || !$solr ) {
			return;
		}

		$query = $solr->createSelect();
		$query->setQuery( 'permalink:' . $this->escape( get_permalink() ) )->
		getMoreLikeThis()->
		setFields( 'title,content' );

		$response = $solr->select( $query );

		if ( !$response->getResponse()->getStatusCode() == 200 ) {
			return;
		}

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$mltresults = $response->moreLikeThis;
		foreach ( $mltresults as $mltresult ) {
			$docs = $mltresult->docs;
			echo "<ul>";
			foreach ( $docs as $doc ) {
				if ( $showauthor ) {
					$author = " by {$doc->author}";
				}
				echo "<li><a href=\"" . esc_url( $doc->permalink ) . "\" title=\"" . esc_attr( $doc->title ) . "\">" . esc_html( $doc->title ) . "</a>" . esc_html( $author ) . "</li>";
			}
			echo "</ul>";
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance					 = $old_instance;
		$new_instance				 = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 5, 'showauthor' => 0 ) );
		$instance[ 'title' ]		 = strip_tags( $new_instance[ 'title' ] );
		$cnt						 = strip_tags( $new_instance[ 'count' ] );
		$instance[ 'count' ]		 = is_numeric( $cnt ) ? $cnt : 5;
		$instance[ 'showauthor' ]	 = $new_instance[ 'showauthor' ] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		$instance	 = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 5, 'showauthor' => 0 ) );
		$title		 = strip_tags( $instance[ 'title' ] );
		$count		 = strip_tags( $instance[ 'count' ] );
		$showauthor	 = $instance[ 'showauthor' ] ? 'checked="checked"' : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php _e( 'Count:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'showauthor' ) ); ?>"><?php _e( 'Show Author?:' ); ?></label>
			<input class="checkbox" type="checkbox" <?php echo $showauthor; ?> id="<?php echo esc_attr( $this->get_field_id( 'showauthor' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'showauthor' ) ); ?>" />
		</p>
		<?php
	}

}
