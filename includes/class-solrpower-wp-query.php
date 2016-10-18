<?php

class SolrPower_WP_Query {

	/**
	 * Singleton instance
	 * @var SolrPower_WP_Query|Bool
	 */
	private static $instance = false;

	/**
	 * Array of found Solr returned posts based on query hash.
	 * @var array
	 */
	private $found_posts = array();

	/**
	 * Returned facets from search.
	 * @var Solarium\QueryType\Select\Result\Facet\Field[] $facets
	 */
	var $facets = array();
	/**
	 * @var string Query being sent to Solr.
	 */
	var $qry;
	/**
	 * @var array Response from Solr.
	 */
	var $search;
	/**
	 * @var array Additional filter queries being sent to Solr.
	 */
	var $fq = array();

	/**
	 * Grab instance of object.
	 * @return SolrPower_WP_Query
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			add_action( 'init', array( self::$instance, 'setup' ) );
		}

		return self::$instance;
	}

	/**
	 * SolrPower_WP_Query constructor.
	 */
	function __construct() {

	}

	/**
	 * SolrPower_WP_Query instance initial setup method.
	 */
	function setup() {
		// We don't want to do a Solr query if we're doing AJAX or in the admin area.

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			/**
			 * Allow Solr Search with AJAX
			 *
			 * By default the plugin won't query Solr on AJAX requests. Set to true to override
			 *
			 * @param bool $solr_allow_ajax True to query on AJAX or false [default false].
			 */
			if ( false === apply_filters( 'solr_allow_ajax', false ) ) {
				return;
			}
		}

		/**
		 * Allow Solr Search in WordPress Dashboard
		 *
		 * By default the plugin won't query Solr in the WordPress Dashboard. Set to true to override
		 *
		 * @param bool $solr_allow_admin True to query in WordPress Dashboard or false [default false].
		 */
		if ( is_admin() && false === apply_filters( 'solr_allow_admin', false ) ) {
			return;
		}


		add_filter( 'posts_request', array( $this, 'posts_request' ), 10, 2 );

		// Nukes the FOUND_ROWS() database query
		add_filter( 'found_posts_query', array( $this, 'found_posts_query' ), 5, 2 );

		add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );
	}

	/**
	 * Reset the variables in the object to avoid issues with future queries.
	 */
	function reset_vars() {
		$this->fq  = array();
		$this->qry = '';
	}

	/**
	 * @param string $request SQL Query
	 * @param WP_Query $query
	 *
	 * @return string
	 */
	function posts_request( $request, $query ) {
		if ( ! $query->is_search() && ! $query->get( 'solr_integrate' ) ) {
			return $request;
		}
		add_filter( 'solr_query', array( SolrPower_Api::get_instance(), 'dismax_query' ), 10, 2 );
		$solr_options = SolrPower_Options::get_instance()->get_option();

		$the_page = ( ! $query->get( 'paged' ) ) ? 1 : $query->get( 'paged' );

		$qry = $this->build_query( $query );
		if ( '' === $qry ) { // If we don't have anything to query, let's do a wildcard.
			$qry = '*';
		}
		$this->qry = $qry;
		$offset    = $query->get( 'posts_per_page' ) * ( $the_page - 1 );
		$count     = $query->get( 'posts_per_page' );
		$fq        = $this->parse_facets( $query );
		$sortby    = ( isset( $solr_options['s4wp_default_sort'] ) && ! empty( $solr_options['s4wp_default_sort'] ) ) ? $solr_options['s4wp_default_sort'] : 'score';
		$order     = ( $query->get( 'order', false ) ) ? strtolower( $query->get( 'order' ) ) : 'desc';
		if ( $query->get( 'orderby', false ) ) {
			$orderby = $query->get( 'orderby' );

			if ( is_array( $orderby ) ) {
				$new_order = array();
				foreach ( $orderby as $order_key => $order_val ) {
					$new_order[] = $this->parse_orderby( $order_key, $query ) . ' ' . strtolower( $order_val );
				}
				$sortby = implode( ',', $new_order );
				$order  = false;
			} else {
				$sortby = $this->parse_orderby( $orderby, $query );
			}
		}


		$fields = null;
		switch ( $query->get( 'fields' ) ) {
			case 'ids':
				$fields = 'ID';
				break;
			default:
				$fields = null;
				break;
		}
		$query->set( 'fields', '' );
		unset( $query->query['fields'] );
		$search = SolrPower_Api::get_instance()->query( $qry, $offset, $count, $fq, $sortby, $order, $fields );

		if ( is_null( $search ) ) {
			return false;
		}
		$this->search = $search;
		if ( $search->getFacetSet() ) {
			$this->facets = $search->getFacetSet()->getFacets();
		}
		$search = $search->getData();

		$search_header        = $search['responseHeader'];
		$search               = $search['response'];
		$query->found_posts   = $search['numFound'];
		$query->max_num_pages = ceil( $search['numFound'] / $query->get( 'posts_per_page' ) );

		SolrPower_Api::get_instance()->add_log( array(
			'Results Found' => $search['numFound'],
			'Query Time'    => $search_header['QTime'] . 'ms'
		) );

		$posts = $this->parse_results( $search );

		$this->found_posts[ spl_object_hash( $query ) ] = $posts;
		$this->reset_vars();
		global $wpdb;

		return "SELECT * FROM $wpdb->posts WHERE 1=0";
	}

	/**
	 * Converts Solr results to WP_Posts.
	 *
	 * @param array $search Solr results array.
	 *
	 * @return array
	 */
	private function parse_results( $search ) {
		$posts = array();

		foreach ( $search['docs'] as $post_array ) {
			$post = new stdClass();

			foreach ( $post_array as $key => $value ) {
				if ( 'displaydate' === $key ) {
					$post->post_date = $value;
					continue;
				}
				if ( 'displaymodified' === $key ) {
					$post->post_modified = $value;
					continue;
				}
				if ( 'post_date' === $key || 'post_modified' === $key ) {
					continue;
				}

				if ( 'post_id' === $key ) {
					$post->ID = $value;
					continue;
				}

				$post->$key = $value;
			}
			$post->solr = true;
			$posts[]    = $post;
		}

		return $posts;

	}

	/**
	 * Converts the orderby to a Solr-friendly sortby.
	 *
	 * @param string|array $orderby
	 * @param WP_Query $query
	 *
	 * @return string
	 */
	private function parse_orderby( $orderby, $query ) {
		// Used to filter values.
		$allowed_keys = array(
			'post_name',
			'post_author',
			'post_date',
			'post_title',
			'post_modified',
			'post_parent',
			'post_type',
			'name',
			'author',
			'date',
			'title',
			'modified',
			'parent',
			'type',
			'ID',
			'menu_order',
			'comment_count',
			'rand',
		);

		$primary_meta_key   = '';
		$primary_meta_query = false;
		$meta_clauses       = $query->meta_query->get_clauses();
		if ( ! empty( $meta_clauses ) ) {
			$primary_meta_query = reset( $meta_clauses );

			if ( ! empty( $primary_meta_query['key'] ) ) {
				$primary_meta_key = $primary_meta_query['key'];
				$allowed_keys[]   = $primary_meta_key;
			}

			$allowed_keys[] = 'meta_value';
			$allowed_keys[] = 'meta_value_num';
			$allowed_keys   = array_merge( $allowed_keys, array_keys( $meta_clauses ) );
		}

		if ( ! in_array( $orderby, $allowed_keys ) ) {
			return false;
		}

		switch ( $orderby ) {
			case 'post_name':
			case 'post_author':
			case 'post_date':
			case 'post_title':
			case 'post_modified':
			case 'post_parent':
			case 'post_type':
			case 'ID':
			case 'menu_order':
			case 'comment_count':
				$orderby_clause = $orderby;
				break;
			case 'rand':
				$orderby_clause = 'random';
				break;
			case $primary_meta_key:
			case 'meta_value':
				if ( ! empty( $primary_meta_query['type'] ) ) {
					$orderby_clause = "{$primary_meta_query['key']}_{$this->meta_type(array('type'=>$primary_meta_query['cast']),true)}";
				} else {
					$orderby_clause = "{$primary_meta_query['key']}_s";
				}
				break;
			case 'meta_value_num':
				$orderby_clause = "{$primary_meta_query['alias']}_i";
				break;
			default:
				if ( array_key_exists( $orderby, $meta_clauses ) ) {
					// $orderby corresponds to a meta_query clause.
					$meta_clause    = $meta_clauses[ $orderby ];
					$orderby_clause = "{$meta_clause['key']}_{$this->meta_type(array('type'=>$meta_clause['cast']),true)}";
				} else {
					// Default: order by post field.
					$orderby_clause = "post_" . sanitize_key( $orderby );
				}

				break;
		}


		return $orderby_clause;
	}

	/**
	 * @param string $sql
	 * @param WP_Query $query
	 *
	 * @return string
	 */
	function found_posts_query( $sql, $query ) {
		if ( ! $query->is_search() ) {
			return $sql;
		}

		return '';
	}

	/**
	 * @param array $posts
	 * @param WP_Query $query
	 *
	 * @return mixed
	 */
	function the_posts( $posts, &$query ) {
		if ( ! isset( $this->found_posts[ spl_object_hash( $query ) ] ) ) {
			return $posts;
		}

		$new_posts = $this->found_posts[ spl_object_hash( $query ) ];

		return $new_posts;
	}

	/**
	 * Checks for 'facet' as WP_Query variable or query string and sets it up for a filter query.
	 *
	 * @param WP_Query $query
	 *
	 * @return array
	 */
	function parse_facets( $query ) {
		$plugin_s4wp_settings = solr_options();
		$default_operator     = ( isset( $plugin_s4wp_settings['s4wp_default_operator'] ) ) ? $plugin_s4wp_settings['s4wp_default_operator'] : 'OR';

		$facets = $query->get( 'facet' );
		if ( ! $facets ) {
			$facets = filter_input( INPUT_GET, 'facet', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		}
		if ( ! $facets ) {
			if ( is_array( $this->fq ) && ! empty( $this->fq ) ) {
				$return = $this->fq;

				return implode( ' ' . $default_operator . ' ', $return );
			}

			return array();
		}
		$return = array();
		foreach ( $facets as $facet_name => $facet_arr ) {
			$fq = array();
			foreach ( $facet_arr as $facet ):
				// htmlspecialchars_decode because of the single quote issue in facet widget. Ex: "Hello'" category
				$fq[] = '"' . htmlspecialchars( htmlspecialchars_decode( $facet, ENT_QUOTES ) ) . '"';
			endforeach;
			$return[] = $facet_name . ':(' . implode( ' OR ', $fq ) . ')';
		}


		// Additional Filter Query:
		$return = array_merge( $return, $this->fq );

		return implode( ' ' . $default_operator . ' ', $return );

	}

	/**
	 * Parses WP_Query variables to a Solr query.
	 *
	 * @param WP_Query $query
	 *
	 * @return string
	 */
	private function build_query( $query ) {
		$whitelist = array(
			'post_type',
			'p',
			'page_id',
			'post_status',
			'post_parent',
			'name'

		);
		$convert   = array(
			'p'       => 'ID',
			'page_id' => 'ID',
			'name'    => 'post_name'
		);
		if ( ! $query->get( 'solr_integrate' ) ) {
			return $query->get( 's' );
		}

		$solr_query = array();

		if ( ! empty( $query->meta_query->queries ) ) {
			$solr_query[] = $this->parse_meta_query( $query->meta_query->queries );
		}

		if ( ! empty( $query->tax_query->queries ) ) {
			$solr_query[] = $this->parse_tax_query( $query->tax_query->queries );
		}
		foreach ( $query->query_vars as $var_key => $var_value ) {


			if ( 'post_type' === $var_key && 'any' === $var_value ) {
				continue;
			}
			if ( ! empty( $var_value ) && in_array( $var_key, $whitelist ) ) {
				$var_value    = ( is_array( $var_value ) ) ? '(' . implode( ' OR ', $var_value ) . ')' : $var_value;
				$var_key      = ( isset( $convert[ $var_key ] ) ) ? $convert[ $var_key ] : $var_key;
				$solr_query[] = '(' . $var_key . ':' . $var_value . ')';
			}
		}

		return implode( 'AND', $solr_query );
	}

	/**
	 * Parses tax queries to Solr.
	 *
	 * @param array $tax_query
	 *
	 * @return string
	 */
	private function parse_tax_query( $tax_query ) {
		$query          = array();
		$wildcards_used = array();
		$tax_fq         = array();
		$relation       = 'OR';
		if ( isset( $tax_query['relation'] ) ) {
			$relation = $tax_query['relation'];
			unset( $tax_query['relation'] );
		}

		foreach ( $tax_query as $tax_key => $tax_value ) {
			if ( ! is_array( $tax_value ) ) {
				continue;
			}
			if ( ! isset( $tax_value['terms'] ) ) {
				$tax_value['terms'] = array();
			}
			if ( isset( $tax_value[0]['taxonomy'] ) ) {
				$query[] = $this->parse_tax_query( $tax_value );
				continue;
			}
			if ( ! isset( $tax_value['taxonomy'] ) ) {
				// Kill the query.
				continue;
			}

			$used_terms = array();
			if ( is_array( $tax_value['terms'] ) ) {
				$terms = array();
				foreach ( $tax_value['terms'] as $term ) {
					if ( ! in_array( $term, $used_terms ) ) {
						$terms[]      = '"' . $term . '"';
						$used_terms[] = $term;
					}
				}
			} else {
				$terms = array( $tax_value['terms'] );
			}
			$tax_value['field'] = ( isset( $tax_value['field'] ) ) ? $tax_value['field'] : 'term_id';

			$field = $this->tax_field_name( $tax_value['field'], $tax_value['taxonomy'] );

			$tax_value['operator'] = ( isset( $tax_value['operator'] ) ) ? $tax_value['operator'] : 'IN';

			$tax_value['include_children'] = ( isset( $tax_value['include_children'] ) ) ? $tax_value['include_children'] : true;

			switch ( $tax_value['operator'] ) {

				case 'NOT IN':

					$multi_query = array();
					foreach ( $terms as $value ) {
						$multi_query[] = '(' . $field . ':' . $value . ')';
						$wildcard      = '(ID:*)';


						if ( ! in_array( $wildcard, $wildcards_used ) ) {
							$wildcards_used[] = $wildcard;
							$query[]          = $wildcard;
						}
					}
					$tax_fq[] = '!(' . implode( 'OR', $multi_query ) . ')';

					break;

				case 'AND':
					$wildcard = '(' . $field . ':*)';
					if ( ! in_array( $wildcard, $wildcards_used ) ) {
						$wildcards_used[] = $wildcard;
						$query[]          = $wildcard;
					}
					$tax_fq[] = '(' . $field . ':(' . implode( 'AND', $terms ) . '))';
					break;

				case 'EXISTS':
					$terms         = ( ! empty( $terms ) ) ? $terms : array( '*' );
					$multi_query   = array();
					$multi_query[] = '(' . $field . ':' . implode( 'OR', $terms ) . ')';
					$wildcard      = '(' . $field . ':*)';

					if ( ! in_array( $wildcard, $multi_query ) ) {
						$multi_query[]    = $wildcard;
						$wildcards_used[] = $wildcard;
					}

					$query[] = '(ID:*)';

					$fq = implode( 'OR', $multi_query );
					if ( ! in_array( $fq, $tax_fq ) ) {
						$tax_fq[] = $fq;
					}
					break;

				case 'NOT EXISTS':
					$terms         = ( ! empty( $terms ) ) ? $terms : array( '*' );
					$multi_query   = array();
					$multi_query[] = '!(' . $field . ':' . implode( 'AND', $terms ) . ')';
					$wildcard      = '!(' . $field . ':*)';

					if ( ! in_array( $wildcard, $multi_query ) ) {
						$multi_query[]    = $wildcard;
						$wildcards_used[] = $wildcard;
					}

					$query[] = '(ID:*)';

					$fq = implode( 'AND', $multi_query );
					if ( ! in_array( $fq, $tax_fq ) ) {
						$tax_fq[] = $fq;
					}
					break;

				default: // 'IN'
					$multi_query   = array();
					$multi_query[] = '(' . $field . ':(' . implode( 'OR', $terms ) . '))';
					if ( $tax_value['include_children'] && is_taxonomy_hierarchical( $tax_value['taxonomy'] ) ) {
						$multi_query[] = '(parent_' . $field . ':' . implode( 'OR', $terms ) . ')';
					}
					$query[] = '(' . implode( 'OR', $multi_query ) . ')';
					break;
			}
		}
		if ( ! empty( $tax_fq ) ) {
			$this->fq[] = '(' . implode( 'OR', $tax_fq ) . ')';
		}

		return '(' . implode( $relation, $query ) . ')';

	}

	private function tax_field_name( $tax_field, $taxonomy ) {
		if ( 'category' === $taxonomy ) {
			$taxonomy = 'categories';
		} elseif ( 'post_tag' === $taxonomy ) {
			$taxonomy = 'tags';
		} else {
			$taxonomy .= '_taxonomy';
		}
		switch ( $tax_field ) {
			case 'slug':
				$field = $taxonomy . '_slug_str';
				break;
			case 'name':
				$field = $taxonomy . '_str';
				break;
			case 'term_taxonomy_id':
				$field = 'term_taxonomy_id';
				break;
			default:
				$field = $taxonomy . '_id';
				break;
		}

		return $field;
	}

	/**
	 * Parses meta queries to Solr.
	 *
	 * @param array $meta_query
	 *
	 * @return string
	 */
	private function parse_meta_query( $meta_query ) {
		$options      = solr_options();
		$indexed_keys = $options['s4wp_index_custom_fields'];
		$query        = array();
		$relation     = 'AND'; // AND is default in core.
		if ( isset( $meta_query['relation'] ) ) {
			$relation = $meta_query['relation'];
			unset( $meta_query['relation'] );
		}
		$wildcards_used = array();
		foreach ( $meta_query as $meta_key => $meta_value ) {
			if ( ! is_array( $meta_value ) ) {
				continue;
			}
			if ( isset( $meta_value[0]['key'] ) ) {
				$query[] = $this->parse_meta_query( $meta_value );
				continue;
			}
			$compare = '=';
			if ( isset( $meta_value['compare'] ) ) {
				$compare = $meta_value['compare'];
			}
			$type = $this->meta_type( $meta_value );
			switch ( $compare ) {
				case '<=':
					$query[] = '(' . $meta_value['key'] . '_' . $type . ':[* TO ' . $this->set_query_value( $meta_value['value'], $type ) . '])';
					break;
				case '>=':
					$query[] = '(' . $meta_value['key'] . '_' . $type . ':[' . $this->set_query_value( $meta_value['value'], $type ) . ' TO *])';
					break;
				case '!=':

					$multi_query = array();
					$wildcard    = '(' . $meta_value['key'] . '_' . $type . ':*)';

					if ( ! in_array( $wildcard, $wildcards_used ) ) {
						$multi_query[]    = $wildcard;
						$wildcards_used[] = $wildcard;
					}
					$multi_query[] = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';

					$query[] = implode( 'AND', $multi_query );
					break;
				case 'NOT EXISTS':
					$meta_value['value'] = ( isset( $meta_value['value'] ) ) ? $meta_value['value'] : '*';
					$multi_query         = array();
					$multi_query[]       = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';
					$wildcard            = '!(' . $meta_value['key'] . '_' . $type . ':*)';

					if ( ! in_array( $wildcard, $multi_query ) ) {
						$multi_query[]    = $wildcard;
						$wildcards_used[] = $wildcard;
					}

					$query[] = '(ID:*)';

					$fq = implode( 'AND', $multi_query );
					if ( ! in_array( $fq, $this->fq ) ) {
						$this->fq[] = $fq;
					}
					break;
				case '<';
					$query[]    = '(' . $meta_value['key'] . '_' . $type . ':[* TO ' . $this->set_query_value( $meta_value['value'], $type ) . '])';
					$this->fq[] = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';
					break;

				case '>':
					$query[]    = '(' . $meta_value['key'] . '_' . $type . ':[' . $this->set_query_value( $meta_value['value'], $type ) . ' TO *])';
					$this->fq[] = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';
					break;
				case 'LIKE':
					$type    = 'str'; // Cannot do a LIKE search with a number.
					$query[] = '(' . $meta_value['key'] . '_' . $type . ':*' . $meta_value['value'] . '*)';
					break;
				case 'NOT LIKE':
					$type        = 'str'; // Cannot do a LIKE search with a number.
					$multi_query = array();
					$wildcard    = '(' . $meta_value['key'] . '_' . $type . ':*)';

					if ( ! in_array( $wildcard, $wildcards_used ) ) {
						$multi_query[]    = $wildcard;
						$wildcards_used[] = $wildcard;
					}
					$multi_query[] = '!(' . $meta_value['key'] . '_' . $type . ':*' . $meta_value['value'] . '*)';

					$query[] = implode( 'AND', $multi_query );
					break;
				case 'BETWEEN':
					$query[] = '(' . $meta_value['key'] . '_' . $type . ':[' . $this->set_query_value( $meta_value['value'][0], $type ) . ' TO ' . $this->set_query_value( $meta_value['value'][1], $type ) . '])';
					break;
				case 'NOT BETWEEN':
					$multi_query   = array();
					$multi_query[] = '(' . $meta_value['key'] . '_' . $type . ':[* TO ' . $this->set_query_value( $meta_value['value'][0], $type ) . '])';
					$multi_query[] = '(' . $meta_value['key'] . '_' . $type . ':[' . $this->set_query_value( $meta_value['value'][1], $type ) . ' TO *])';
					$query[]       = implode( 'OR', $multi_query );
					$this->fq[]    = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'][0], $type ) . ')';
					$this->fq[]    = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'][1], $type ) . ')';
					break;

				case 'IN':
					if ( is_array( $meta_value['value'] ) ) {
						$multi_query = array();
						foreach ( $meta_value['value'] as $value ) {
							$multi_query[] = '(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $value, $type ) . ')';
						}
						$query[] = '(' . implode( 'OR', $multi_query ) . ')';
					} else {
						$query[] = '(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';
					}
					break;

				case 'NOT IN':
					if ( is_array( $meta_value['value'] ) ) {
						$multi_query = array();
						foreach ( $meta_value['value'] as $value ) {
							$multi_query[] = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $value, $type ) . ')';
							$wildcard      = '(' . $meta_value['key'] . '_' . $type . ':*)';


							if ( ! in_array( $wildcard, $wildcards_used ) ) {
								$wildcards_used[] = $wildcard;
								$query[]          = $wildcard;
							}
						}
						$this->fq[] = '(' . implode( 'OR', $multi_query ) . ')';
					} else {
						$this->fq[] = '!(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';
					}
					break;
				default:
					$meta_value['value'] = ( isset( $meta_value['value'] ) ) ? $meta_value['value'] : '*';
					if ( ! isset( $meta_value['key'] ) ) {
						$multi_query = array();
						foreach ( $indexed_keys as $the_key ) {
							$multi_query[] = '(' . $the_key . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';
						}
						$query[] = implode( 'OR', $multi_query );
					} else {
						$query[] = '(' . $meta_value['key'] . '_' . $type . ':' . $this->set_query_value( $meta_value['value'], $type ) . ')';
					}
					break;
			}
		}

		return '(' . implode( $relation, $query ) . ')';

	}

	/**
	 * Determines the dynamic field type based on the meta_type specified.
	 *
	 * @param array $meta_value
	 * @param bool $orderby
	 *
	 * @return string
	 */
	private function meta_type( $meta_value, $orderby = false ) {
		if ( ! isset( $meta_value['type'] ) ) {
			if ( isset( $meta_value['value'] ) && is_numeric( $meta_value['value'] ) ) {
				return 'i';
			}

			// if $orderby is true, set the dynamic field type as *_s since it is not multivalued in schema.
			return ( $orderby ) ? 's' : 'str';
		}
		switch ( true ) {
			case stristr( $meta_value['type'], 'DECIMAL' ):
				return 'd';
				break;
			case stristr( $meta_value['type'], 'NUMERIC' ):
				return 'i';
				break;
			case stristr( $meta_value['type'], 'SIGNED' ):
				return 'i';
				break;

			default:
				return ( $orderby ) ? 's' : 'str';
				break;

		}
	}

	/**
	 * Sets query value formatting based on type.
	 *
	 * @param string $value
	 * @param null|string $type
	 *
	 * @return float|int|string
	 */
	private function set_query_value( $value, $type = null ) {

		if ( '*' === $value ) {
			return $value;
		}

		switch ( $type ) {
			case 'd':
				return floatval( $value );
				break;
			case 'i':
				return absint( $value );
				break;
			default:
				return '"' . $value . '"';
				break;
		}


	}
}


