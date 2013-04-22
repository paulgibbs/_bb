<?php

/**
 * Single User
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'bb_before_main_content' ); ?>

	<div id="bbp-user-<?php bb_current_user_id(); ?>" class="bbp-single-user">
		<div class="entry-content">

			<?php bb_get_template_part( 'content', 'single-user' ); ?>

		</div><!-- .entry-content -->
	</div><!-- #bbp-user-<?php bb_current_user_id(); ?> -->

	<?php do_action( 'bb_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
