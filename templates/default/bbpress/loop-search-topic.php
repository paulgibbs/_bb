<?php

/**
 * Search Loop - Single Topic
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div class="bbp-topic-header">

	<div class="bbp-meta">

		<span class="bbp-topic-post-date"><?php bb_topic_post_date( bb_get_topic_id() ); ?></span>

		<a href="<?php bb_topic_permalink(); ?>" title="<?php bb_topic_title(); ?>" class="bbp-topic-permalink">#<?php bb_topic_id(); ?></a>

		<?php do_action( 'bb_theme_before_topic_admin_links' ); ?>

		<?php bb_topic_admin_links( bb_get_topic_id() ); ?>

		<?php do_action( 'bb_theme_after_topic_admin_links' ); ?>

	</div><!-- .bbp-meta -->

	<div class="bbp-topic-title">

		<?php do_action( 'bb_theme_before_topic_title' ); ?>

		<h3><?php _e( 'Topic: ', 'bbpress' ); ?>
		<a href="<?php bb_topic_permalink(); ?>" title="<?php bb_topic_title(); ?>"><?php bb_topic_title(); ?></a></h3>

		<div class="bbp-topic-title-meta">

			<?php if ( function_exists( 'bb_is_forum_group_forum' ) && bb_is_forum_group_forum( bb_get_topic_forum_id() ) ) : ?>

				<?php _e( 'in group forum ', 'bbpress' ); ?>

			<?php else : ?>

				<?php _e( 'in forum ', 'bbpress' ); ?>

			<?php endif; ?>

			<a href="<?php bb_forum_permalink( bb_get_topic_forum_id() ); ?>" title="<?php bb_forum_title( bb_get_topic_forum_id() ); ?>"><?php bb_forum_title( bb_get_topic_forum_id() ); ?></a>

		</div><!-- .bbp-topic-title-meta -->

		<?php do_action( 'bb_theme_after_topic_title' ); ?>

	</div><!-- .bbp-topic-title -->

</div><!-- .bbp-topic-header -->

<div id="post-<?php bb_topic_id(); ?>" <?php bb_topic_class(); ?>>

	<div class="bbp-topic-author">

		<?php do_action( 'bb_theme_before_topic_author_details' ); ?>

		<?php bb_topic_author_link( array( 'sep' => '<br />', 'show_role' => true ) ); ?>

		<?php if ( bb_is_user_keymaster() ) : ?>

			<?php do_action( 'bb_theme_before_topic_author_admin_details' ); ?>

			<div class="bbp-reply-ip"><?php bb_author_ip( bb_get_topic_id() ); ?></div>

			<?php do_action( 'bb_theme_after_topic_author_admin_details' ); ?>

		<?php endif; ?>

		<?php do_action( 'bb_theme_after_topic_author_details' ); ?>

	</div><!-- .bbp-topic-author -->

	<div class="bbp-topic-content">

		<?php do_action( 'bb_theme_before_topic_content' ); ?>

		<?php bb_topic_content(); ?>

		<?php do_action( 'bb_theme_after_topic_content' ); ?>

	</div><!-- .bbp-topic-content -->

</div><!-- #post-<?php bb_topic_id(); ?> -->
