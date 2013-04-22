<?php

/**
 * New/Edit Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php if ( bb_is_reply_edit() ) : ?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

<?php endif; ?>

<?php if ( bb_current_user_can_access_create_reply_form() ) : ?>

	<div id="new-reply-<?php bb_topic_id(); ?>" class="bbp-reply-form">

		<form id="new-post" name="new-post" method="post" action="<?php the_permalink(); ?>">

			<?php do_action( 'bb_theme_before_reply_form' ); ?>

			<fieldset class="bbp-form">
				<legend><?php printf( __( 'Reply To: %s', 'bbpress' ), bb_get_topic_title() ); ?></legend>

				<?php do_action( 'bb_theme_before_reply_form_notices' ); ?>

				<?php if ( !bb_is_topic_open() && !bb_is_reply_edit() ) : ?>

					<div class="bbp-template-notice">
						<p><?php _e( 'This topic is marked as closed to new replies, however your posting capabilities still allow you to do so.', 'bbpress' ); ?></p>
					</div>

				<?php endif; ?>

				<?php if ( current_user_can( 'unfiltered_html' ) ) : ?>

					<div class="bbp-template-notice">
						<p><?php _e( 'Your account has the ability to post unrestricted HTML content.', 'bbpress' ); ?></p>
					</div>

				<?php endif; ?>

				<?php do_action( 'bb_template_notices' ); ?>

				<div>

					<?php bb_get_template_part( 'form', 'anonymous' ); ?>

					<?php do_action( 'bb_theme_before_reply_form_content' ); ?>

					<?php if ( !function_exists( 'wp_editor' ) ) : ?>

						<p>
							<label for="bb_reply_content"><?php _e( 'Reply:', 'bbpress' ); ?></label><br />
							<textarea id="bb_reply_content" tabindex="<?php bb_tab_index(); ?>" name="bb_reply_content" rows="6"><?php bb_form_reply_content(); ?></textarea>
						</p>

					<?php else : ?>

						<?php bb_the_content( array( 'context' => 'reply' ) ); ?>

					<?php endif; ?>

					<?php do_action( 'bb_theme_after_reply_form_content' ); ?>

					<?php if ( !current_user_can( 'unfiltered_html' ) ) : ?>

						<p class="form-allowed-tags">
							<label><?php _e( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:','bbpress' ); ?></label><br />
							<code><?php bb_allowed_tags(); ?></code>
						</p>

					<?php endif; ?>
					
					<?php if ( bb_allow_topic_tags() && current_user_can( 'assign_topic_tags' ) ) : ?>

						<?php do_action( 'bb_theme_before_reply_form_tags' ); ?>

						<p>
							<label for="bb_topic_tags"><?php _e( 'Tags:', 'bbpress' ); ?></label><br />
							<input type="text" value="<?php bb_form_topic_tags(); ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_topic_tags" id="bb_topic_tags" <?php disabled( bb_is_topic_spam() ); ?> />
						</p>

						<?php do_action( 'bb_theme_after_reply_form_tags' ); ?>

					<?php endif; ?>

					<?php if ( bb_is_subscriptions_active() && !bb_is_anonymous() && ( !bb_is_reply_edit() || ( bb_is_reply_edit() && !bb_is_reply_anonymous() ) ) ) : ?>

						<?php do_action( 'bb_theme_before_reply_form_subscription' ); ?>

						<p>

							<input name="bb_topic_subscription" id="bb_topic_subscription" type="checkbox" value="bb_subscribe"<?php bb_form_topic_subscribed(); ?> tabindex="<?php bb_tab_index(); ?>" />

							<?php if ( bb_is_reply_edit() && ( get_the_author_meta( 'ID' ) != bb_get_current_user_id() ) ) : ?>

								<label for="bb_topic_subscription"><?php _e( 'Notify the author of follow-up replies via email', 'bbpress' ); ?></label>

							<?php else : ?>

								<label for="bb_topic_subscription"><?php _e( 'Notify me of follow-up replies via email', 'bbpress' ); ?></label>

							<?php endif; ?>

						</p>

						<?php do_action( 'bb_theme_after_reply_form_subscription' ); ?>

					<?php endif; ?>

					<?php if ( bb_allow_revisions() && bb_is_reply_edit() ) : ?>

						<?php do_action( 'bb_theme_before_reply_form_revisions' ); ?>

						<fieldset class="bbp-form">
							<legend><?php _e( 'Revision', 'bbpress' ); ?></legend>
							<div>
								<input name="bb_log_reply_edit" id="bb_log_reply_edit" type="checkbox" value="1" <?php bb_form_reply_log_edit(); ?> tabindex="<?php bb_tab_index(); ?>" />
								<label for="bb_log_reply_edit"><?php _e( 'Keep a log of this edit:', 'bbpress' ); ?></label><br />
							</div>

							<div>
								<label for="bb_reply_edit_reason"><?php printf( __( 'Optional reason for editing:', 'bbpress' ), bb_get_current_user_name() ); ?></label><br />
								<input type="text" value="<?php bb_form_reply_edit_reason(); ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_reply_edit_reason" id="bb_reply_edit_reason" />
							</div>
						</fieldset>

						<?php do_action( 'bb_theme_after_reply_form_revisions' ); ?>

					<?php endif; ?>

					<?php do_action( 'bb_theme_before_reply_form_submit_wrapper' ); ?>

					<div class="bbp-submit-wrapper">

						<?php do_action( 'bb_theme_before_reply_form_submit_button' ); ?>

						<button type="submit" tabindex="<?php bb_tab_index(); ?>" id="bb_reply_submit" name="bb_reply_submit" class="button submit"><?php _e( 'Submit', 'bbpress' ); ?></button>

						<?php do_action( 'bb_theme_after_reply_form_submit_button' ); ?>

					</div>

					<?php do_action( 'bb_theme_after_reply_form_submit_wrapper' ); ?>

				</div>

				<?php bb_reply_form_fields(); ?>

			</fieldset>

			<?php do_action( 'bb_theme_after_reply_form' ); ?>

		</form>
	</div>

<?php elseif ( bb_is_topic_closed() ) : ?>

	<div id="no-reply-<?php bb_topic_id(); ?>" class="bbp-no-reply">
		<div class="bbp-template-notice">
			<p><?php printf( __( 'The topic &#8216;%s&#8217; is closed to new replies.', 'bbpress' ), bb_get_topic_title() ); ?></p>
		</div>
	</div>

<?php elseif ( bb_is_forum_closed( bb_get_topic_forum_id() ) ) : ?>

	<div id="no-reply-<?php bb_topic_id(); ?>" class="bbp-no-reply">
		<div class="bbp-template-notice">
			<p><?php printf( __( 'The forum &#8216;%s&#8217; is closed to new topics and replies.', 'bbpress' ), bb_get_forum_title( bb_get_topic_forum_id() ) ); ?></p>
		</div>
	</div>

<?php else : ?>

	<div id="no-reply-<?php bb_topic_id(); ?>" class="bbp-no-reply">
		<div class="bbp-template-notice">
			<p><?php is_user_logged_in() ? _e( 'You cannot reply to this topic.', 'bbpress' ) : _e( 'You must be logged in to reply to this topic.', 'bbpress' ); ?></p>
		</div>
	</div>

<?php endif; ?>

<?php if ( bb_is_reply_edit() ) : ?>

</div>

<?php endif; ?>
