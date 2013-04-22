<?php

/**
 * Replies Loop
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php do_action( 'bb_template_before_replies_loop' ); ?>

<ul id="topic-<?php bb_topic_id(); ?>-replies" class="forums bbp-replies">

	<li class="bbp-header">

		<div class="bbp-reply-author"><?php  _e( 'Author',  'bbpress' ); ?></div><!-- .bbp-reply-author -->

		<div class="bbp-reply-content">

			<?php if ( !bb_show_lead_topic() ) : ?>

				<?php _e( 'Posts', 'bbpress' ); ?>

				<?php bb_user_subscribe_link(); ?>

				<?php bb_user_favorites_link(); ?>

			<?php else : ?>

				<?php _e( 'Replies', 'bbpress' ); ?>

			<?php endif; ?>

		</div><!-- .bbp-reply-content -->

	</li><!-- .bbp-header -->

	<li class="bbp-body">

		<?php while ( bb_replies() ) : bb_the_reply(); ?>

			<?php bb_get_template_part( 'loop', 'single-reply' ); ?>

		<?php endwhile; ?>

	</li><!-- .bbp-body -->

	<li class="bbp-footer">

		<div class="bbp-reply-author"><?php  _e( 'Author',  'bbpress' ); ?></div>

		<div class="bbp-reply-content">

			<?php if ( !bb_show_lead_topic() ) : ?>

				<?php _e( 'Posts', 'bbpress' ); ?>

			<?php else : ?>

				<?php _e( 'Replies', 'bbpress' ); ?>

			<?php endif; ?>

		</div><!-- .bbp-reply-content -->

	</li><!-- .bbp-footer -->

</ul><!-- #topic-<?php bb_topic_id(); ?>-replies -->

<?php do_action( 'bb_template_after_replies_loop' ); ?>
