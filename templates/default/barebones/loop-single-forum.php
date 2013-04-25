<?php

/**
 * Forums Loop - Single Forum
 *
 * @package Barebones
 * @subpackage Theme
 */

?>

<ul id="bbp-forum-<?php bb_forum_id(); ?>" <?php bb_forum_class(); ?>>

	<li class="bbp-forum-info">

		<?php do_action( 'bb_theme_before_forum_title' ); ?>

		<a class="bbp-forum-title" href="<?php bb_forum_permalink(); ?>" title="<?php bb_forum_title(); ?>"><?php bb_forum_title(); ?></a>

		<?php do_action( 'bb_theme_after_forum_title' ); ?>

		<?php do_action( 'bb_theme_before_forum_description' ); ?>

		<div class="bbp-forum-content"><?php the_content(); ?></div>

		<?php do_action( 'bb_theme_after_forum_description' ); ?>

		<?php do_action( 'bb_theme_before_forum_sub_forums' ); ?>

		<?php bb_list_forums(); ?>

		<?php do_action( 'bb_theme_after_forum_sub_forums' ); ?>

		<?php bb_forum_row_actions(); ?>

	</li>

	<li class="bbp-forum-topic-count"><?php bb_forum_topic_count(); ?></li>

	<li class="bbp-forum-reply-count"><?php bb_show_lead_topic() ? bb_forum_reply_count() : bb_forum_post_count(); ?></li>

	<li class="bbp-forum-freshness">

		<?php do_action( 'bb_theme_before_forum_freshness_link' ); ?>

		<?php bb_forum_freshness_link(); ?>

		<?php do_action( 'bb_theme_after_forum_freshness_link' ); ?>

		<p class="bbp-topic-meta">

			<?php do_action( 'bb_theme_before_topic_author' ); ?>

			<span class="bbp-topic-freshness-author"><?php bb_author_link( array( 'post_id' => bb_get_forum_last_active_id(), 'size' => 14 ) ); ?></span>

			<?php do_action( 'bb_theme_after_topic_author' ); ?>

		</p>
	</li>

</ul><!-- #bbp-forum-<?php bb_forum_id(); ?> -->
