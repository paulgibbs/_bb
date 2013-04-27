<?php

/**
 * Forums Loop
 *
 * @package Barebones
 * @subpackage Theme
 */

?>

<?php do_action( 'bb_template_before_forums_loop' ); ?>

<ul id="forums-list-<?php bb_forum_id(); ?>" class="bb-forums">

	<li class="bb-header">

		<ul class="forum-titles">
			<li class="bb-forum-info"><?php _e( 'Forum', 'barebones' ); ?></li>
			<li class="bb-forum-topic-count"><?php _e( 'Topics', 'barebones' ); ?></li>
			<li class="bb-forum-reply-count"><?php bb_show_lead_topic() ? _e( 'Replies', 'barebones' ) : _e( 'Posts', 'barebones' ); ?></li>
			<li class="bb-forum-freshness"><?php _e( 'Freshness', 'barebones' ); ?></li>
		</ul>

	</li><!-- .bb-header -->

	<li class="bb-body">

		<?php while ( bb_forums() ) : bb_the_forum(); ?>

			<?php bb_get_template_part( 'loop', 'single-forum' ); ?>

		<?php endwhile; ?>

	</li><!-- .bb-body -->

	<li class="bb-footer">

		<div class="tr">
			<p class="td colspan4">&nbsp;</p>
		</div><!-- .tr -->

	</li><!-- .bb-footer -->

</ul><!-- .forums-directory -->

<?php do_action( 'bb_template_after_forums_loop' ); ?>
