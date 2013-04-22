<?php

/**
 * Single Forum
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'bb_before_main_content' ); ?>

	<?php do_action( 'bb_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php if ( bb_user_can_view_forum() ) : ?>

			<div id="forum-<?php bb_forum_id(); ?>" class="bbp-forum-content">
				<h1 class="entry-title"><?php bb_forum_title(); ?></h1>
				<div class="entry-content">

					<?php bb_get_template_part( 'content', 'single-forum' ); ?>

				</div>
			</div><!-- #forum-<?php bb_forum_id(); ?> -->

		<?php else : // Forum exists, user no access ?>

			<?php bb_get_template_part( 'feedback', 'no-access' ); ?>

		<?php endif; ?>

	<?php endwhile; ?>

	<?php do_action( 'bb_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
