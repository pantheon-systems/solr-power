<?php
/**
 * Template Name: Search
 *
 * @package Solr_Power
 */

?>

<?php get_header(); ?>
<div id="content">

<div class="solr clearfix">

<?php
$results = s4wp_search_results();
if ( ! isset( $results['results'] ) || null === $results['results'] ) {
	echo '<div class="solr_noresult"><h2>Sorry, search is unavailable right now</h2><p>Try again later?</p></div>';
} else {
	?>

	<div class="solr1 clearfix">
		<div class="solr_search">
	<?php
	if ( ! empty( $results['qtime'] ) ) {
		printf( "<label class='solr_response'>Response time: <span id=\"qrytime\">{$results['qtime']}</span> s</label>" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	// if server id has been defined keep hold of it.
	$server = filter_input( INPUT_GET, 'server', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	if ( $server ) {
		$serverval = '<input name="server" type="hidden" value="' . esc_attr( $server ) . '" />';
	} else {
		$serverval = '';
	}
	?>

			<form name="searchbox" method="get" id="searchbox" action="">
					<input id="qrybox" name="s" type="text" class="solr_field" value="<?php echo esc_attr( $results['query'] ); ?>"/>
					<?php echo $serverval; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<input id="searchbtn" type="submit" value="Search" />
			</form>
		</div>

	</div>

	<div class="solr2">

		<div class="solr_results_header clearfix">
			<div class="solr_results_headerL">

	<?php
	if ( $results['hits'] && $results['query'] && $results['qtime'] ) {
		if ( $results['firstresult'] === $results['lastresult'] ) {
			printf( "Displaying result %s of <span id='resultcnt'>%s</span> hits", $results['firstresult'], $results['hits'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			printf( "Displaying results %s-%s of <span id='resultcnt'>%s</span> hits", $results['firstresult'], $results['lastresult'], $results['hits'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
	?>

			</div>
			<div class="solr_results_headerR">
				<ol class="solr_sort2">
					<li class="solr_sort_drop"><a href="<?php echo $results['sorting']['scoredesc'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPressDotOrg.sniffs.OutputEscaping.UnescapedOutputParameter ?>">Relevance<span></span></a></li>
					<li><a href="<?php echo $results['sorting']['datedesc'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPressDotOrg.sniffs.OutputEscaping.UnescapedOutputParameter ?>">Newest</a></li>
					<li><a href="<?php echo $results['sorting']['dateasc'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPressDotOrg.sniffs.OutputEscaping.UnescapedOutputParameter ?>">Oldest</a></li>
					<li><a href="<?php echo $results['sorting']['commentsdesc'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPressDotOrg.sniffs.OutputEscaping.UnescapedOutputParameter ?>">Most Comments</a></li>
					<li><a href="<?php echo $results['sorting']['commentsasc'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPressDotOrg.sniffs.OutputEscaping.UnescapedOutputParameter ?>">Least Comments</a></li>
				</ol>
				<div class="solr_sort">Sort by:</div>
			</div>
		</div>

		<div class="solr_results">

	<?php
	if ( 0 === (int) $results['hits'] ) {
		printf(
			"<div class='solr_noresult'>
						<h2>Sorry, no results were found.</h2>
						<h3>Perhaps you mispelled your search query, or need to try using broader search terms.</h3>
						<p>For example, instead of searching for 'Apple iPhone 3.0 3GS', try something simple like 'iPhone'.</p>
					</div>\n"
		);
	} else {
		printf( "<ol>\n" );
		foreach ( $results['results'] as $result ) {

			printf( "<li onclick=\"window.location='%s'\">\n", esc_url_raw( $result['permalink'] ) );
			printf( "<h2><a href='%s'>%s</a></h2>\n", esc_url_raw( $result['permalink'] ), esc_textarea( $result['title'] ) );
			echo '<p>';
			foreach ( explode( '...', $result['teaser'] ) as $this_result ) {
				if ( ! empty( $this_result ) ) {
					echo '...' . $this_result . '...<br /><br />'; // phpcs:ignore WordPressDotOrg.sniffs.OutputEscaping.UnescapedOutputParameter
				}
			}

			if ( $result['numcomments'] > 0 ) {
				printf( "<a href='%s'>(comment match)</a>", esc_url_raw( $result['comment_link'] ) );
			}

			echo "</p>\n";

			printf(
				"<label> By <a href='%s'>%s</a> in %s %s - <a href='%s'>%s comments</a></label>\n",
				esc_url_raw( $result['authorlink'] ),
				esc_textarea( $result['author'] ),
				wp_kses_post( get_the_category_list( ', ', '', $result['id'] ) ),
				esc_textarea( gmdate( 'm/d/Y', strtotime( $result['date'] ) ) ),
				esc_url_raw( $result['comment_link'] ),
				esc_textarea( $result['numcomments'] )
			);
			printf( "</li>\n" );
		}
		printf( "</ol>\n" );
	} // End if().
	?>

	<?php
	if ( $results['pager'] ) {
		printf( '<div class="solr_pages">' );
		$itemlinks = array();
		$pagecnt   = 0;
		$pagemax   = 10;
		$next      = '';
		$prev      = '';
		$found     = false;
		foreach ( $results['pager'] as $pageritm ) {
			if ( $pageritm['link'] ) {
				if ( $found && '' === $next ) {
					$next = $pageritm['link'];
				} elseif ( false === $found ) {
					$prev = $pageritm['link'];
				}
				$itemlinks[] = sprintf( '<a href="%s">%s</a>', $pageritm['link'], $pageritm['page'] );
			} else {
				$found       = true;
				$itemlinks[] = sprintf( '<a class="solr_pages_on" href="%s">%s</a>', $pageritm['link'], $pageritm['page'] );
			}

			$pagecnt += 1;
			if ( $pagecnt === $pagemax ) {
				break;
			}
		}

		if ( '' !== $prev ) {
			printf( '<a href="%s">Previous</a>', esc_url_raw( $prev ) );
		}

		foreach ( $itemlinks as $itemlink ) {
			echo wp_kses_post( $itemlink );
		}

		if ( '' !== $next ) {
			printf( '<a href="%s">Next</a>', esc_url_raw( $next ) );
		}

		printf( "</div>\n" );
	} // End if().
	?>


		</div>
	</div>

	<div class="solr3">
		<ul class="solr_facets">

			<li class="solr_active">
				<ol>
	<?php
	if ( $results['facets']['selected'] ) {
		foreach ( $results['facets']['selected'] as $selectedfacet ) {
			printf( '<li><span></span><a href="%s">%s<b>x</b></a></li>', esc_url_raw( $selectedfacet['removelink'] ), esc_textarea( $selectedfacet['name'] ) );
		}
	}
	?>
				</ol>
			</li>

	<?php
	if ( $results['facets'] && 1 != $results['hits'] ) {
		foreach ( $results['facets'] as $facet ) {
			// don't display facets with only 1 value.
			if ( isset( $facet['items'] ) and sizeof( $facet['items'] ) > 1 ) {
				printf( "<li>\n<h3>%s</h3>\n", wp_kses_post( $facet['name'] ) );
				s4wp_print_facet_items( $facet['items'], '<ol>', '</ol>', '<li>', '</li>', '<li><ol>', '</ol></li>', '<li>', '</li>' );
				printf( "</li>\n" );
			}
		}
	}
	?>

		</ul>
	</div>

</div>

</div>
<?php } // End if().
get_footer(); ?>
