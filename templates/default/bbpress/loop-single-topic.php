<?php

/**
 * Topics Loop - Single
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<ul id="bbp-topic-<?php bb_topic_id(); ?>" <?php bb_topic_class(); ?>>

	<li class="bbp-topic-title">

		<?php if ( bb_is_user_home() ) : ?>

			<?php if ( bb_is_favorites() ) : ?>

				<span class="bbp-topic-action">

					<?php do_action( 'bb_theme_before_topic_favorites_action' ); ?>

					<?php bb_user_favorites_link( array( 'before' => '', 'favorite' => '+', 'favorited' => '&times;' ) ); ?>

					<?php do_action( 'bb_theme_after_topic_favorites_action' ); ?>

				</span>

			<?php elseif ( bb_is_subscriptions() ) : ?>

				<span class="bbp-topic-action">

					<?php do_action( 'bb_theme_before_topic_subscription_action' ); ?>

					<?php bb_user_subscribe_link( array( 'before' => '', 'subscribe' => '+', 'unsubscribe' => '&times;' ) ); ?>

					<?php do_action( 'bb_theme_after_topic_subscription_action' ); ?>

				</span>

			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'bb_theme_before_topic_title' ); ?>

		<a class="bbp-topic-permalink" href="<?php bb_topic_permalink(); ?>" title="<?php bb_topic_title(); ?>"><?php bb_topic_title(); ?></a>

		<?php do_action( 'bb_theme_after_topic_title' ); ?>

		<?php bb_topic_pagination(); ?>

		<?php do_action( 'bb_theme_before_topic_meta' ); ?>

		<p class="bbp-topic-meta">

			<?php do_action( 'bb_theme_before_topic_started_by' ); ?>

			<span class="bbp-topic-started-by"><?php printf( __( 'Started by: %1$s', 'bbpress' ), bb_get_topic_author_link( array( 'size' => '14' ) ) ); ?></span>

			<?php do_action( 'bb_theme_after_topic_started_by' ); ?>

			<?php if ( !bb_is_single_forum() || ( bb_get_topic_forum_id() != bb_get_forum_id() ) ) : ?>

				<?php do_action( 'bb_theme_before_topic_started_in' ); ?>

				<span class="bbp-topic-started-in"><?php printf( __( 'in: <a href="%1$s">%2$s</a>', 'bbpress' ), bb_get_forum_permalink( bb_get_topic_forum_id() ), bb_get_forum_title( bb_get_topic_forum_id() ) ); ?></span>

				<?php do_action( 'bb_theme_after_topic_started_in' ); ?>

			<?php endif; ?>

		</p>

		<?php do_action( 'bb_theme_after_topic_meta' ); ?>

		<?php bb_topic_row_actions(); ?>

	</li>

	<li class="bbp-topic-voice-count"><?php bb_topic_voice_count(); ?></li>

	<li class="bbp-topic-reply-count"><?php bb_show_lead_topic() ? bb_topic_reply_count() : bb_topic_post_count(); ?></li>

	<li class="bbp-topic-freshness">

		<?php do_action( 'bb_theme_before_topic_freshness_link' ); ?>

		<?php bb_topic_freshness_link(); ?>

		<?php do_action( 'bb_theme_after_topic_freshness_link' ); ?>

		<p class="bbp-topic-meta">

			<?php do_action( 'bb_theme_before_topic_freshness_author' ); ?>

			<span class="bbp-topic-freshness-author"><?php bb_author_link( array( 'post_id' => bb_get_topic_last_active_id(), 'size' => 14 ) ); ?></span>

			<?php do_action( 'bb_theme_after_topic_freshness_author' ); ?>

		</p>
	</li>

</ul><!-- #bbp-topic-<?php bb_topic_id(); ?> -->
