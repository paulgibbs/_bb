<?php

/**
 * Template Name: bbPress - Topics (No Replies)
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'bb_before_main_content' ); ?>

	<?php do_action( 'bb_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="topics-front" class="bbp-topics-front">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php the_content(); ?>

				<div id="bbpress-forums">

					<?php bb_breadcrumb(); ?>

					<?php bb_set_query_name( 'bb_no_replies' ); ?>

					<?php if ( bb_has_topics( array( 'meta_key' => '_bb_reply_count', 'meta_value' => '1', 'meta_compare' => '<', 'orderby' => 'date', 'show_stickies' => false ) ) ) : ?>

						<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

						<?php bb_get_template_part( 'loop',       'topics'    ); ?>

						<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

					<?php else : ?>

						<?php bb_get_template_part( 'feedback',   'no-topics' ); ?>

					<?php endif; ?>

					<?php bb_reset_query_name(); ?>

				</div>
			</div>
		</div><!-- #topics-front -->

	<?php endwhile; ?>

	<?php do_action( 'bb_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
