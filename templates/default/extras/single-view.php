<?php

/**
 * Single View
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'bb_before_main_content' ); ?>

	<?php do_action( 'bb_template_notices' ); ?>

	<div id="bbp-view-<?php bb_view_id(); ?>" class="bbp-view">
		<h1 class="entry-title"><?php bb_view_title(); ?></h1>
		<div class="entry-content">

			<?php bb_get_template_part( 'content', 'single-view' ); ?>

		</div>
	</div><!-- #bbp-view-<?php bb_view_id(); ?> -->

	<?php do_action( 'bb_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
