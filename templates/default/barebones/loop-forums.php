<?php

/**
 * Forums Loop
 *
 * @package barebones
 * @subpackage Theme
 */

?>

<?php do_action( 'bb_template_before_forums_loop' ); ?>

<ul id="forums-list-<?php bb_forum_id(); ?>" class="bbp-forums">

	<li class="bbp-header">

		<ul class="forum-titles">
			<li class="bbp-forum-info"><?php _e( 'Forum', 'barebones' ); ?></li>
			<li class="bbp-forum-topic-count"><?php _e( 'Topics', 'barebones' ); ?></li>
			<li class="bbp-forum-reply-count"><?php bb_show_lead_topic() ? _e( 'Replies', 'barebones' ) : _e( 'Posts', 'barebones' ); ?></li>
			<li class="bbp-forum-freshness"><?php _e( 'Freshness', 'barebones' ); ?></li>
		</ul>

	</li><!-- .bbp-header -->

	<li class="bbp-body">

		<?php while ( bb_forums() ) : bb_the_forum(); ?>

			<?php bb_get_template_part( 'loop', 'single-forum' ); ?>

		<?php endwhile; ?>

	</li><!-- .bbp-body -->

	<li class="bbp-footer">

		<div class="tr">
			<p class="td colspan4">&nbsp;</p>
		</div><!-- .tr -->

	</li><!-- .bbp-footer -->

</ul><!-- .forums-directory -->

<?php do_action( 'bb_template_after_forums_loop' ); ?>
