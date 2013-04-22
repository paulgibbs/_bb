<?php

/**
 * bbPress - Forum Archive
 *
 * @package bbPress
 * @subpackage Theme
 */

get_header(); ?>

	<?php do_action( 'bb_before_main_content' ); ?>

	<?php do_action( 'bb_template_notices' ); ?>

	<div id="forum-front" class="bbp-forum-front">
		<h1 class="entry-title"><?php bb_forum_archive_title(); ?></h1>
		<div class="entry-content">

			<?php bb_get_template_part( 'content', 'archive-forum' ); ?>

		</div>
	</div><!-- #forum-front -->

	<?php do_action( 'bb_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
