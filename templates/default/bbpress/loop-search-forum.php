<?php

/**
 * Search Loop - Single Forum
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div class="bbp-forum-header">

	<div class="bbp-meta">

		<span class="bbp-forum-post-date"><?php printf( __( 'Last updated %s', 'bbpress' ), bb_get_forum_last_active_time() ); ?></span>

		<a href="<?php bb_forum_permalink(); ?>" title="<?php bb_forum_title(); ?>" class="bbp-forum-permalink">#<?php bb_forum_id(); ?></a>

	</div><!-- .bbp-meta -->

	<div class="bbp-forum-title">

		<?php do_action( 'bb_theme_before_forum_title' ); ?>

		<h3><?php _e( 'Forum: ', 'bbpress' ); ?><a href="<?php bb_forum_permalink(); ?>" title="<?php bb_forum_title(); ?>"><?php bb_forum_title(); ?></a></h3>

		<?php do_action( 'bb_theme_after_forum_title' ); ?>

	</div><!-- .bbp-forum-title -->

</div><!-- .bbp-forum-header -->

<div id="post-<?php bb_forum_id(); ?>" <?php bb_forum_class(); ?>>

	<div class="bbp-forum-content">

		<?php do_action( 'bb_theme_before_forum_content' ); ?>

		<?php the_excerpt(); ?>

		<?php do_action( 'bb_theme_after_forum_content' ); ?>

	</div><!-- .bbp-forum-content -->

</div><!-- #post-<?php bb_forum_id(); ?> -->
