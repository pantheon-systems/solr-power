<header class="page-header">
	<h1 class="page-title">Search Results</h1>
</header><!-- .page-header -->
<?php
if ( $query->have_posts() ) :
	while ( $query->have_posts() ) : $query->the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<?php if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) ) : ?>
					<div class="entry-meta">
					<span
						class="cat-links"><?php echo get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'solr-for-wordpress-on-pantheon' ) ); ?></span>
					</div>
					<?php
				endif;

				if ( is_single() ) :
					the_title( '<h1 class="entry-title">', '</h1>' );
				else :
					the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
				endif;
				?>

				<div class="entry-meta">
					<?php

					if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) :
						?>
						<span
							class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'solr-for-wordpress-on-pantheon' ), __( '1 Comment', 'solr-for-wordpress-on-pantheon' ), __( '% Comments', 'solr-for-wordpress-on-pantheon' ) ); ?></span>
						<?php
					endif;

					edit_post_link( __( 'Edit', 'twentyfourteen' ), '<span class="edit-link">', '</span>' );
					?>
				</div><!-- .entry-meta -->
			</header><!-- .entry-header -->

			<?php if ( is_search() ) : ?>
				<div class="entry-summary">
					<?php the_excerpt(); ?>
				</div><!-- .entry-summary -->
			<?php else : ?>
				<div class="entry-content">
					<?php
					/* translators: %s: Name of current post */
					the_content( sprintf(
						__( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'solr-for-wordpress-on-pantheon' ),
						the_title( '<span class="screen-reader-text">', '</span>', false )
					) );

					wp_link_pages( array(
						'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'solr-for-wordpress-on-pantheon' ) . '</span>',
						'after'       => '</div>',
						'link_before' => '<span>',
						'link_after'  => '</span>',
					) );
					?>
				</div><!-- .entry-content -->
			<?php endif; ?>

			<?php the_tags( '<footer class="entry-meta"><span class="tag-links">', '', '</span></footer>' ); ?>
		</article><!-- #post-## -->
	<?php endwhile; ?>
	<nav class="navigation paging-navigation" role="navigation">
		<h1 class="screen-reader-text">Posts navigation</h1>
		<div class="pagination loop-pagination">
			<?php
			$big = 999999999; // Need an unlikely integer.

			echo paginate_links( array(
				'base'               => str_replace( $big, '%#%', get_pagenum_link( $big, false ) ),
				'format'             => '?paged=%#%',
				'current'            => max( 1, get_query_var( 'paged' ) ),
				'total'              => $query->max_num_pages,
				'prev_text'          => __( 'Previous page', 'solr-for-wordpress-on-pantheon' ),
				'next_text'          => __( 'Next page', 'solr-for-wordpress-on-pantheon' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'solr-for-wordpress-on-pantheon' ) . ' </span>',

			) );
			wp_reset_postdata();
			?>
		</div>
	</nav>
<?php else : ?>
	<article>
		<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'solr-for-wordpress-on-pantheon' ); ?></p>
	</article>
<?php endif; ?>