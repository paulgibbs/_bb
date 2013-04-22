<?php

/**
 * New/Edit Topic
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php if ( !bb_is_single_forum() ) : ?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

<?php endif; ?>

<?php if ( bb_is_topic_edit() ) : ?>

	<?php bb_topic_tag_list( bb_get_topic_id() ); ?>

	<?php bb_single_topic_description( array( 'topic_id' => bb_get_topic_id() ) ); ?>

<?php endif; ?>

<?php if ( bb_current_user_can_access_create_topic_form() ) : ?>

	<div id="new-topic-<?php bb_topic_id(); ?>" class="bbp-topic-form">

		<form id="new-post" name="new-post" method="post" action="<?php the_permalink(); ?>">

			<?php do_action( 'bb_theme_before_topic_form' ); ?>

			<fieldset class="bbp-form">
				<legend>

					<?php
						if ( bb_is_topic_edit() )
							printf( __( 'Now Editing &ldquo;%s&rdquo;', 'bbpress' ), bb_get_topic_title() );
						else
							bb_is_single_forum() ? printf( __( 'Create New Topic in &ldquo;%s&rdquo;', 'bbpress' ), bb_get_forum_title() ) : _e( 'Create New Topic', 'bbpress' );
					?>

				</legend>

				<?php do_action( 'bb_theme_before_topic_form_notices' ); ?>

				<?php if ( !bb_is_topic_edit() && bb_is_forum_closed() ) : ?>

					<div class="bbp-template-notice">
						<p><?php _e( 'This forum is marked as closed to new topics, however your posting capabilities still allow you to do so.', 'bbpress' ); ?></p>
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

					<?php do_action( 'bb_theme_before_topic_form_title' ); ?>

					<p>
						<label for="bb_topic_title"><?php printf( __( 'Topic Title (Maximum Length: %d):', 'bbpress' ), bb_get_title_max_length() ); ?></label><br />
						<input type="text" id="bb_topic_title" value="<?php bb_form_topic_title(); ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_topic_title" maxlength="<?php bb_title_max_length(); ?>" />
					</p>

					<?php do_action( 'bb_theme_after_topic_form_title' ); ?>

					<?php do_action( 'bb_theme_before_topic_form_content' ); ?>

					<?php if ( !function_exists( 'wp_editor' ) ) : ?>

						<p>
							<label for="bb_topic_content"><?php _e( 'Topic:', 'bbpress' ); ?></label><br />
							<textarea id="bb_topic_content" tabindex="<?php bb_tab_index(); ?>" name="bb_topic_content" cols="60" rows="6"><?php bb_form_topic_content(); ?></textarea>
						</p>

					<?php else : ?>

						<?php bb_the_content( array( 'context' => 'topic' ) ); ?>

					<?php endif; ?>

					<?php do_action( 'bb_theme_after_topic_form_content' ); ?>

					<?php if ( !current_user_can( 'unfiltered_html' ) ) : ?>

						<p class="form-allowed-tags">
							<label><?php _e( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:','bbpress' ); ?></label><br />
							<code><?php bb_allowed_tags(); ?></code>
						</p>

					<?php endif; ?>

					<?php if ( bb_allow_topic_tags() && current_user_can( 'assign_topic_tags' ) ) : ?>

						<?php do_action( 'bb_theme_before_topic_form_tags' ); ?>

						<p>
							<label for="bb_topic_tags"><?php _e( 'Topic Tags:', 'bbpress' ); ?></label><br />
							<input type="text" value="<?php bb_form_topic_tags(); ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_topic_tags" id="bb_topic_tags" <?php disabled( bb_is_topic_spam() ); ?> />
						</p>

						<?php do_action( 'bb_theme_after_topic_form_tags' ); ?>

					<?php endif; ?>

					<?php if ( !bb_is_single_forum() ) : ?>

						<?php do_action( 'bb_theme_before_topic_form_forum' ); ?>

						<p>
							<label for="bb_forum_id"><?php _e( 'Forum:', 'bbpress' ); ?></label><br />
							<?php bb_dropdown( array( 'selected' => bb_get_form_topic_forum() ) ); ?>
						</p>

						<?php do_action( 'bb_theme_after_topic_form_forum' ); ?>

					<?php endif; ?>

					<?php if ( current_user_can( 'moderate' ) ) : ?>

						<?php do_action( 'bb_theme_before_topic_form_type' ); ?>

						<p>

							<label for="bb_stick_topic"><?php _e( 'Topic Type:', 'bbpress' ); ?></label><br />

							<?php bb_topic_type_select(); ?>

						</p>

						<?php do_action( 'bb_theme_after_topic_form_type' ); ?>

					<?php endif; ?>

					<?php if ( bb_is_subscriptions_active() && !bb_is_anonymous() && ( !bb_is_topic_edit() || ( bb_is_topic_edit() && !bb_is_topic_anonymous() ) ) ) : ?>

						<?php do_action( 'bb_theme_before_topic_form_subscriptions' ); ?>

						<p>
							<input name="bb_topic_subscription" id="bb_topic_subscription" type="checkbox" value="bb_subscribe" <?php bb_form_topic_subscribed(); ?> tabindex="<?php bb_tab_index(); ?>" />

							<?php if ( bb_is_topic_edit() && ( get_the_author_meta( 'ID' ) != bb_get_current_user_id() ) ) : ?>

								<label for="bb_topic_subscription"><?php _e( 'Notify the author of follow-up replies via email', 'bbpress' ); ?></label>

							<?php else : ?>

								<label for="bb_topic_subscription"><?php _e( 'Notify me of follow-up replies via email', 'bbpress' ); ?></label>

							<?php endif; ?>
						</p>

						<?php do_action( 'bb_theme_after_topic_form_subscriptions' ); ?>

					<?php endif; ?>

					<?php if ( bb_allow_revisions() && bb_is_topic_edit() ) : ?>

						<?php do_action( 'bb_theme_before_topic_form_revisions' ); ?>

						<fieldset class="bbp-form">
							<legend><?php _e( 'Revision', 'bbpress' ); ?></legend>
							<div>
								<input name="bb_log_topic_edit" id="bb_log_topic_edit" type="checkbox" value="1" <?php bb_form_topic_log_edit(); ?> tabindex="<?php bb_tab_index(); ?>" />
								<label for="bb_log_topic_edit"><?php _e( 'Keep a log of this edit:', 'bbpress' ); ?></label><br />
							</div>

							<div>
								<label for="bb_topic_edit_reason"><?php printf( __( 'Optional reason for editing:', 'bbpress' ), bb_get_current_user_name() ); ?></label><br />
								<input type="text" value="<?php bb_form_topic_edit_reason(); ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_topic_edit_reason" id="bb_topic_edit_reason" />
							</div>
						</fieldset>

						<?php do_action( 'bb_theme_after_topic_form_revisions' ); ?>

					<?php endif; ?>

					<?php do_action( 'bb_theme_before_topic_form_submit_wrapper' ); ?>

					<div class="bbp-submit-wrapper">

						<?php do_action( 'bb_theme_before_topic_form_submit_button' ); ?>

						<button type="submit" tabindex="<?php bb_tab_index(); ?>" id="bb_topic_submit" name="bb_topic_submit" class="button submit"><?php _e( 'Submit', 'bbpress' ); ?></button>

						<?php do_action( 'bb_theme_after_topic_form_submit_button' ); ?>

					</div>

					<?php do_action( 'bb_theme_after_topic_form_submit_wrapper' ); ?>

				</div>

				<?php bb_topic_form_fields(); ?>

			</fieldset>

			<?php do_action( 'bb_theme_after_topic_form' ); ?>

		</form>
	</div>

<?php elseif ( bb_is_forum_closed() ) : ?>

	<div id="no-topic-<?php bb_topic_id(); ?>" class="bbp-no-topic">
		<div class="bbp-template-notice">
			<p><?php printf( __( 'The forum &#8216;%s&#8217; is closed to new topics and replies.', 'bbpress' ), bb_get_forum_title() ); ?></p>
		</div>
	</div>

<?php else : ?>

	<div id="no-topic-<?php bb_topic_id(); ?>" class="bbp-no-topic">
		<div class="bbp-template-notice">
			<p><?php is_user_logged_in() ? _e( 'You cannot create new topics.', 'bbpress' ) : _e( 'You must be logged in to create new topics.', 'bbpress' ); ?></p>
		</div>
	</div>

<?php endif; ?>

<?php if ( !bb_is_single_forum() ) : ?>

</div>

<?php endif; ?>
