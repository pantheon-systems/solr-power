<?php

class SolrPower_Facet_Widget extends WP_Widget {
	/**
	 * @var array Facets sent to WP_Query
	 */
	var $facets = array();

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'solrpower_facet_widget',
			'description' => 'Facet your search results.',
		);
		parent::__construct( 'solrpower_facet_widget', 'Solr Search', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$this->dummy_query();
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}
		$this->facets = filter_input( INPUT_GET, 'facet', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		echo '<form action="' . home_url( '/' ) . '" method="get" id="solr_facet">';
		$this->render_searchbox();
		$this->fetch_facets();
		echo '</form>';
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'Search';
		?>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array $instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

	/**
	 * Fetches and displays returned facets.
	 */
	function fetch_facets() {
		$solr_options = solr_options();
		if ( ! $solr_options['s4wp_output_facets'] ) {
			return;
		}
		$facets = SolrPower_WP_Query::get_instance()->facets;

		foreach ( $facets as $facet_name => $data ) {

			if ( false === $this->show_facet( $facet_name ) ) {
				continue;
			}
			/**
			 * Filter facet HTML
			 *
			 * Filter the HTML output of a facet.
			 *
			 * @param string $facet_name the facet name.
			 *
			 * @param object $data the Solarium facet data object.
			 */
			$html = apply_filters( 'solr_facet_items', false, $facet_name, $data );
			if ( $html ) {
				echo $html;
				continue;
			}
			/**
			 * Filter facet title
			 *
			 * Filter the facet title displayed in the widget.
			 *
			 * @param boolean|string the custom facet title, defaults to false.
			 *
			 * @param string $facet_name the facet name.
			 *
			 */
			$facet_nice_name = apply_filters( 'solr_facet_title', false, $facet_name );
			if ( false === $facet_nice_name ) {
				$replace         = array( '/\_taxonomy/', '/\_str/', '/\_/' );
				$facet_nice_name = ucwords( preg_replace( $replace, ' ', $facet_name ) );
			}
			$values = $data->getValues();
			if ( 0 === count( $values ) ) {
				continue;
			}
			echo '<h2>' . esc_html( $facet_nice_name ) . '</h2>';
			echo '<ul>';

			foreach ( $values as $name => $count ):

				$nice_name = str_replace( '^^', '', $name );
				$checked   = '';

				if ( isset( $this->facets[ $facet_name ] )
				     && in_array( htmlspecialchars_decode( $name ), $this->facets[ $facet_name ] )
				) {
					$checked = checked( true, true, false );
				}
				echo '<li>';
				echo '<input type="checkbox" name="facet[' . esc_attr( $facet_name ) . '][]" value="' . esc_attr( $name ) . '" ' . $checked . '> ';
				echo esc_html( $nice_name );
				echo ' (' . esc_html( $count ) . ')';
				echo '</li>';
			endforeach;

			echo '</ul>';


			echo '<a href="' . $this->reset_url( $facet_name ) . '">Reset</a>';

		}

	}

	/**
	 * Reset link below facet list.
	 *
	 * @param string $facet_name
	 */
	function reset_url( $facet_name ) {
		$facets = $this->facets;
		if ( ! isset( $facets[ $facet_name ] ) ) {
			return;
		}
		unset( $facets[ $facet_name ] );

		return add_query_arg( array( 'facet' => $facets ) );
	}

	/**
	 * Basic input textbox.
	 */
	function render_searchbox() {
		$html = '<input type="text" name="s" value="' . get_search_query() . '" id="solr_s"> <br/><br/>';
		$html .= '<input type="submit" value="Search"><br/><br/>';
		/**
		 * Filter facet widget search box HTML
		 *
		 * Filter the HTML output of the facet widget search box.
		 *
		 * @param string $html the search box html.
		 */
		echo apply_filters( 'solr_facet_searchbox', $html );
	}

	/**
	 * Determine if a facet should be visible based on options set on admin page.
	 *
	 * @param string $facet Facet name
	 *
	 * @return bool
	 */
	function show_facet( $facet ) {


		if ( 0 < strpos( $facet, 'taxonomy' ) ) {
			$facet = 'taxonomy';
		}

		if ( 0 < strpos( $facet, 'author' ) ) {
			$facet = 'author';
		}

		if ( 'post_type' === $facet ) {
			$facet = 'type';
		}

		if ( 0 < strpos( $facet, '_str' ) ) {
			$facet = 'custom_fields';
		}

		$key = 's4wp_facet_on_' . $facet;

		$solr_options = solr_options();

		if ( array_key_exists( $key, $solr_options )
		     && false != $solr_options[ $key ]
		) {
			return true;
		}

		return false;
	}

	function dummy_query() {
		// For a wildcard search, lets change the parser to lucene.
		add_filter( 'solr_query', function ( $query ) {
			$query->addParam( 'defType', 'lucene' );

			return $query;
		} );
		global $wp_query;
		$query = new WP_Query();
		if ( ! $wp_query->get( 's' ) ) {
			$query->set( 's', '*:*' );
			$query->get_posts();
		}

	}
}

/**
 * Display the search box outside of a widget.
 */
function solr_facet_search() {
	$facet = new SolrPower_Facet_Widget();
	$facet->dummy_query();
	$facet->facets = filter_input( INPUT_GET, 'facet', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
	echo '<form action="' . home_url( '/' ) . '" method="get" id="solr_facet">';
	$facet->render_searchbox();
	$facet->fetch_facets();
	echo '</form>';
}