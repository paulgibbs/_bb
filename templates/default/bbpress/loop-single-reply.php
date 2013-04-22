<?php

/**
 * Replies Loop - Single Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="post-<?php bb_reply_id(); ?>" class="bbp-reply-header">

	<div class="bbp-meta">

		<span class="bbp-reply-post-date"><?php bb_reply_post_date(); ?></span>

		<?php if ( bb_is_single_user_replies() ) : ?>

			<span class="bbp-header">
				<?php _e( 'in reply to: ', 'bbpress' ); ?>
				<a class="bbp-topic-permalink" href="<?php bb_topic_permalink( bb_get_reply_topic_id() ); ?>" title="<?php bb_topic_title( bb_get_reply_topic_id() ); ?>"><?php bb_topic_title( bb_get_reply_topic_id() ); ?></a>
			</span>

		<?php endif; ?>

		<a href="<?php bb_reply_url(); ?>" title="<?php bb_reply_title(); ?>" class="bbp-reply-permalink">#<?php bb_reply_id(); ?></a>

		<?php do_action( 'bb_theme_before_reply_admin_links' ); ?>

		<?php bb_reply_admin_links(); ?>

		<?php do_action( 'bb_theme_after_reply_admin_links' ); ?>

	</div><!-- .bbp-meta -->

</div><!-- #post-<?php bb_reply_id(); ?> -->

<div <?php bb_reply_class(); ?>>

	<div class="bbp-reply-author">

		<?php do_action( 'bb_theme_before_reply_author_details' ); ?>

		<?php bb_reply_author_link( array( 'sep' => '<br />', 'show_role' => true ) ); ?>

		<?php if ( bb_is_user_keymaster() ) : ?>

			<?php do_action( 'bb_theme_before_reply_author_admin_details' ); ?>

			<div class="bbp-reply-ip"><?php bb_author_ip( bb_get_reply_id() ); ?></div>

			<?php do_action( 'bb_theme_after_reply_author_admin_details' ); ?>

		<?php endif; ?>

		<?php do_action( 'bb_theme_after_reply_author_details' ); ?>

	</div><!-- .bbp-reply-author -->

	<div class="bbp-reply-content">

		<?php do_action( 'bb_theme_before_reply_content' ); ?>

		<?php bb_reply_content(); ?>

		<?php do_action( 'bb_theme_after_reply_content' ); ?>

	</div><!-- .bbp-reply-content -->

</div><!-- .reply -->
