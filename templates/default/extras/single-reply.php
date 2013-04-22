<?php

/**
 * Single Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'bb_before_main_content' ); ?>

	<?php do_action( 'bb_template_notices' ); ?>

	<?php if ( bb_user_can_view_forum( array( 'forum_id' => bb_get_reply_forum_id() ) ) ) : ?>

		<?php while ( have_posts() ) : the_post(); ?>

			<div id="bbp-reply-wrapper-<?php bb_reply_id(); ?>" class="bbp-reply-wrapper">
				<h1 class="entry-title"><?php bb_reply_title(); ?></h1>
				<div class="entry-content">

					<?php bb_get_template_part( 'content', 'single-reply' ); ?>

				</div><!-- .entry-content -->
			</div><!-- #bbp-reply-wrapper-<?php bb_reply_id(); ?> -->

		<?php endwhile; ?>

	<?php elseif ( bb_is_forum_private( bb_get_reply_forum_id(), false ) ) : ?>

		<?php bb_get_template_part( 'feedback', 'no-access' ); ?>

	<?php endif; ?>

	<?php do_action( 'bb_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
