<?php

/**
 * New/Edit Forum
 *
 * @package Barebones
 * @subpackage Theme
 */

?>

<?php if ( bb_is_forum_edit() ) : ?>

<div id="barebones-forums">

	<?php bb_breadcrumb(); ?>

	<?php bb_single_forum_description( array( 'forum_id' => bb_get_forum_id() ) ); ?>

<?php endif; ?>

<?php if ( bb_current_user_can_access_create_forum_form() ) : ?>

	<div id="new-forum-<?php bb_forum_id(); ?>" class="bb-forum-form">

		<form id="new-post" name="new-post" method="post" action="<?php the_permalink(); ?>">

			<?php do_action( 'bb_theme_before_forum_form' ); ?>

			<fieldset class="bb-form">
				<legend>

					<?php
						if ( bb_is_forum_edit() )
							printf( __( 'Now Editing &ldquo;%s&rdquo;', 'barebones' ), bb_get_forum_title() );
						else
							bb_is_single_forum() ? printf( __( 'Create New Forum in &ldquo;%s&rdquo;', 'barebones' ), bb_get_forum_title() ) : _e( 'Create New Forum', 'barebones' );
					?>

				</legend>

				<?php do_action( 'bb_theme_before_forum_form_notices' ); ?>

				<?php if ( !bb_is_forum_edit() && bb_is_forum_closed() ) : ?>

					<div class="bb-template-notice">
						<p><?php _e( 'This forum is closed to new content, however your account still allows you to do so.', 'barebones' ); ?></p>
					</div>

				<?php endif; ?>

				<?php if ( current_user_can( 'unfiltered_html' ) ) : ?>

					<div class="bb-template-notice">
						<p><?php _e( 'Your account has the ability to post unrestricted HTML content.', 'barebones' ); ?></p>
					</div>

				<?php endif; ?>

				<?php do_action( 'bb_template_notices' ); ?>

				<div>

					<?php do_action( 'bb_theme_before_forum_form_title' ); ?>

					<p>
						<label for="bb_forum_title"><?php printf( __( 'Forum Name (Maximum Length: %d):', 'barebones' ), bb_get_title_max_length() ); ?></label><br />
						<input type="text" id="bb_forum_title" value="<?php bb_form_forum_title(); ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_forum_title" maxlength="<?php bb_title_max_length(); ?>" />
					</p>

					<?php do_action( 'bb_theme_after_forum_form_title' ); ?>

					<?php do_action( 'bb_theme_before_forum_form_content' ); ?>

					<?php if ( !function_exists( 'wp_editor' ) ) : ?>

						<p>
							<label for="bb_forum_content"><?php _e( 'Forum Description:', 'barebones' ); ?></label><br />
							<textarea id="bb_forum_content" tabindex="<?php bb_tab_index(); ?>" name="bb_forum_content" cols="60" rows="10"><?php bb_form_forum_content(); ?></textarea>
						</p>

					<?php else : ?>

						<?php bb_the_content( array( 'context' => 'forum' ) ); ?>

					<?php endif; ?>

					<?php do_action( 'bb_theme_after_forum_form_content' ); ?>

					<?php if ( !current_user_can( 'unfiltered_html' ) ) : ?>

						<p class="form-allowed-tags">
							<label><?php _e( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes:','barebones' ); ?></label><br />
							<code><?php bb_allowed_tags(); ?></code>
						</p>

					<?php endif; ?>

					<?php do_action( 'bb_theme_before_forum_form_type' ); ?>

					<p>
						<label for="bb_forum_type"><?php _e( 'Forum Type:', 'barebones' ); ?></label><br />
						<?php bb_form_forum_type_dropdown(); ?>
					</p>

					<?php do_action( 'bb_theme_after_forum_form_type' ); ?>

					<?php do_action( 'bb_theme_before_forum_form_status' ); ?>

					<p>
						<label for="bb_forum_status"><?php _e( 'Status:', 'barebones' ); ?></label><br />
						<?php bb_form_forum_status_dropdown(); ?>
					</p>

					<?php do_action( 'bb_theme_after_forum_form_status' ); ?>

					<?php do_action( 'bb_theme_before_forum_form_status' ); ?>

					<p>
						<label for="bb_forum_visibility"><?php _e( 'Visibility:', 'barebones' ); ?></label><br />
						<?php bb_form_forum_visibility_dropdown(); ?>
					</p>

					<?php do_action( 'bb_theme_after_forum_visibility_status' ); ?>

					<?php do_action( 'bb_theme_before_forum_form_parent' ); ?>

					<p>
						<label for="bb_forum_parent_id"><?php _e( 'Parent Forum:', 'barebones' ); ?></label><br />

						<?php
							bb_dropdown( array(
								'select_id' => 'bb_forum_parent_id',
								'show_none' => __( '(No Parent)', 'barebones' ),
								'selected'  => bb_get_form_forum_parent(),
								'exclude'   => bb_get_forum_id()
							) );
						?>
					</p>

					<?php do_action( 'bb_theme_after_forum_form_parent' ); ?>

					<?php do_action( 'bb_theme_before_forum_form_submit_wrapper' ); ?>

					<div class="bb-submit-wrapper">

						<?php do_action( 'bb_theme_before_forum_form_submit_button' ); ?>

						<button type="submit" tabindex="<?php bb_tab_index(); ?>" id="bb_forum_submit" name="bb_forum_submit" class="button submit"><?php _e( 'Submit', 'barebones' ); ?></button>

						<?php do_action( 'bb_theme_after_forum_form_submit_button' ); ?>

					</div>

					<?php do_action( 'bb_theme_after_forum_form_submit_wrapper' ); ?>

				</div>

				<?php bb_forum_form_fields(); ?>

			</fieldset>

			<?php do_action( 'bb_theme_after_forum_form' ); ?>

		</form>
	</div>

<?php elseif ( bb_is_forum_closed() ) : ?>

	<div id="no-forum-<?php bb_forum_id(); ?>" class="bb-no-forum">
		<div class="bb-template-notice">
			<p><?php printf( __( 'The forum &#8216;%s&#8217; is closed to new content.', 'barebones' ), bb_get_forum_title() ); ?></p>
		</div>
	</div>

<?php else : ?>

	<div id="no-forum-<?php bb_forum_id(); ?>" class="bb-no-forum">
		<div class="bb-template-notice">
			<p><?php is_user_logged_in() ? _e( 'You cannot create new forums.', 'barebones' ) : _e( 'You must be logged in to create new forums.', 'barebones' ); ?></p>
		</div>
	</div>

<?php endif; ?>

<?php if ( bb_is_forum_edit() ) : ?>

</div>

<?php endif; ?>
