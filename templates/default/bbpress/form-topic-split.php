<?php

/**
 * Split Topic
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php if ( is_user_logged_in() && current_user_can( 'edit_topic', bb_get_topic_id() ) ) : ?>

		<div id="split-topic-<?php bb_topic_id(); ?>" class="bbp-topic-split">

			<form id="split_topic" name="split_topic" method="post" action="<?php the_permalink(); ?>">

				<fieldset class="bbp-form">

					<legend><?php printf( __( 'Split topic "%s"', 'bbpress' ), bb_get_topic_title() ); ?></legend>

					<div>

						<div class="bbp-template-notice info">
							<p><?php _e( 'When you split a topic, you are slicing it in half starting with the reply you just selected. Choose to use that reply as a new topic with a new title, or merge those replies into an existing topic.', 'bbpress' ); ?></p>
						</div>

						<div class="bbp-template-notice">
							<p><?php _e( 'If you use the existing topic option, replies within both topics will be merged chronologically. The order of the merged replies is based on the time and date they were posted.', 'bbpress' ); ?></p>
						</div>

						<fieldset class="bbp-form">
							<legend><?php _e( 'Split Method', 'bbpress' ); ?></legend>

							<div>
								<input name="bb_topic_split_option" id="bb_topic_split_option_reply" type="radio" checked="checked" value="reply" tabindex="<?php bb_tab_index(); ?>" />
								<label for="bb_topic_split_option_reply"><?php printf( __( 'New topic in <strong>%s</strong> titled:', 'bbpress' ), bb_get_forum_title( bb_get_topic_forum_id( bb_get_topic_id() ) ) ); ?></label>
								<input type="text" id="bb_topic_split_destination_title" value="<?php printf( __( 'Split: %s', 'bbpress' ), bb_get_topic_title() ); ?>" tabindex="<?php bb_tab_index(); ?>" size="35" name="bb_topic_split_destination_title" />
							</div>

							<?php if ( bb_has_topics( array( 'show_stickies' => false, 'post_parent' => bb_get_topic_forum_id( bb_get_topic_id() ), 'post__not_in' => array( bb_get_topic_id() ) ) ) ) : ?>

								<div>
									<input name="bb_topic_split_option" id="bb_topic_split_option_existing" type="radio" value="existing" tabindex="<?php bb_tab_index(); ?>" />
									<label for="bb_topic_split_option_existing"><?php _e( 'Use an existing topic in this forum:', 'bbpress' ); ?></label>

									<?php
										bb_dropdown( array(
											'post_type'   => bb_get_topic_post_type(),
											'post_parent' => bb_get_topic_forum_id( bb_get_topic_id() ),
											'selected'    => -1,
											'exclude'     => bb_get_topic_id(),
											'select_id'   => 'bb_destination_topic',
											'none_found'  => __( 'No other topics found!', 'bbpress' )
										) );
									?>

								</div>

							<?php endif; ?>

						</fieldset>

						<fieldset class="bbp-form">
							<legend><?php _e( 'Topic Extras', 'bbpress' ); ?></legend>

							<div>

								<?php if ( bb_is_subscriptions_active() ) : ?>

									<input name="bb_topic_subscribers" id="bb_topic_subscribers" type="checkbox" value="1" checked="checked" tabindex="<?php bb_tab_index(); ?>" />
									<label for="bb_topic_subscribers"><?php _e( 'Copy subscribers to the new topic', 'bbpress' ); ?></label><br />

								<?php endif; ?>

								<input name="bb_topic_favoriters" id="bb_topic_favoriters" type="checkbox" value="1" checked="checked" tabindex="<?php bb_tab_index(); ?>" />
								<label for="bb_topic_favoriters"><?php _e( 'Copy favoriters to the new topic', 'bbpress' ); ?></label><br />

								<?php if ( bb_allow_topic_tags() ) : ?>

									<input name="bb_topic_tags" id="bb_topic_tags" type="checkbox" value="1" checked="checked" tabindex="<?php bb_tab_index(); ?>" />
									<label for="bb_topic_tags"><?php _e( 'Copy topic tags to the new topic', 'bbpress' ); ?></label><br />

								<?php endif; ?>

							</div>
						</fieldset>

						<div class="bbp-template-notice error">
							<p><?php _e( '<strong>WARNING:</strong> This process cannot be undone.', 'bbpress' ); ?></p>
						</div>

						<div class="bbp-submit-wrapper">
							<button type="submit" tabindex="<?php bb_tab_index(); ?>" id="bb_merge_topic_submit" name="bb_merge_topic_submit" class="button submit"><?php _e( 'Submit', 'bbpress' ); ?></button>
						</div>
					</div>

					<?php bb_split_topic_form_fields(); ?>

				</fieldset>
			</form>
		</div>

	<?php else : ?>

		<div id="no-topic-<?php bb_topic_id(); ?>" class="bbp-no-topic">
			<div class="entry-content"><?php is_user_logged_in() ? _e( 'You do not have the permissions to edit this topic!', 'bbpress' ) : _e( 'You cannot edit this topic.', 'bbpress' ); ?></div>
		</div>

	<?php endif; ?>

</div>
