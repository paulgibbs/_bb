<?php

/**
 * Move Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php if ( is_user_logged_in() && current_user_can( 'edit_topic', bb_get_topic_id() ) ) : ?>

		<div id="move-reply-<?php bb_topic_id(); ?>" class="bbp-reply-move">

			<form id="move_reply" name="move_reply" method="post" action="<?php the_permalink(); ?>">

				<fieldset class="bbp-form">

					<legend><?php printf( __( 'Move reply "%s"', 'bbpress' ), bb_get_reply_title() ); ?></legend>

					<div>

						<div class="bbp-template-notice info">
							<p><?php _e( 'You can either make this reply a new topic with a new title, or merge it into an existing topic.', 'bbpress' ); ?></p>
						</div>

						<div class="bbp-template-notice">
							<p><?php _e( 'If you choose an existing topic, replies will be ordered by the time and date they were created.', 'bbpress' ); ?></p>
						</div>

						<fieldset class="bbp-form">
							<legend><?php _e( 'Move Method', 'bbpress' ); ?></legend>

							<div>
								<input name="bb_reply_move_option" id="bb_reply_move_option_reply" type="radio" checked="checked" value="topic" tabindex="<?php bb_tab_index(); ?>" />
								<label for="bb_reply_move_option_reply"><?php printf( __( 'New topic in <strong>%s</strong> titled:', 'bbpress' ), bb_get_forum_title( bb_get_reply_forum_id( bb_get_reply_id() ) ) ); ?></label>
								<input type="text" id="bb_reply_move_destination_title" value="<?php printf( __( 'Moved: %s', 'bbpress' ), bb_get_reply_title() ); ?>" tabindex="<?php bb_tab_index(); ?>" size="35" name="bb_reply_move_destination_title" />
							</div>

							<?php if ( bb_has_topics( array( 'show_stickies' => false, 'post_parent' => bb_get_reply_forum_id( bb_get_reply_id() ), 'post__not_in' => array( bb_get_reply_topic_id( bb_get_reply_id() ) ) ) ) ) : ?>

								<div>
									<input name="bb_reply_move_option" id="bb_reply_move_option_existing" type="radio" value="existing" tabindex="<?php bb_tab_index(); ?>" />
									<label for="bb_reply_move_option_existing"><?php _e( 'Use an existing topic in this forum:', 'bbpress' ); ?></label>

									<?php
										bb_dropdown( array(
											'post_type'   => bb_get_topic_post_type(),
											'post_parent' => bb_get_reply_forum_id( bb_get_reply_id() ),
											'selected'    => -1,
											'exclude'     => bb_get_reply_topic_id( bb_get_reply_id() ),
											'select_id'   => 'bb_destination_topic',
											'none_found'  => __( 'No other topics found!', 'bbpress' )
										) );
									?>

								</div>

							<?php endif; ?>

						</fieldset>

						<div class="bbp-template-notice error">
							<p><?php _e( '<strong>WARNING:</strong> This process cannot be undone.', 'bbpress' ); ?></p>
						</div>

						<div class="bbp-submit-wrapper">
							<button type="submit" tabindex="<?php bb_tab_index(); ?>" id="bb_move_reply_submit" name="bb_move_reply_submit" class="button submit"><?php _e( 'Submit', 'bbpress' ); ?></button>
						</div>
					</div>

					<?php bb_move_reply_form_fields(); ?>

				</fieldset>
			</form>
		</div>

	<?php else : ?>

		<div id="no-reply-<?php bb_reply_id(); ?>" class="bbp-no-reply">
			<div class="entry-content"><?php is_user_logged_in() ? _e( 'You do not have the permissions to edit this reply!', 'bbpress' ) : _e( 'You cannot edit this reply.', 'bbpress' ); ?></div>
		</div>

	<?php endif; ?>

</div>
