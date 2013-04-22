<?php

/**
 * Anonymous User
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php if ( bb_is_anonymous() || ( bb_is_topic_edit() && bb_is_topic_anonymous() ) || ( bb_is_reply_edit() && bb_is_reply_anonymous() ) ) : ?>

	<?php do_action( 'bb_theme_before_anonymous_form' ); ?>

	<fieldset class="bbp-form">
		<legend><?php ( bb_is_topic_edit() || bb_is_reply_edit() ) ? _e( 'Author Information', 'bbpress' ) : _e( 'Your information:', 'bbpress' ); ?></legend>

		<?php do_action( 'bb_theme_anonymous_form_extras_top' ); ?>

		<p>
			<label for="bb_anonymous_author"><?php _e( 'Name (required):', 'bbpress' ); ?></label><br />
			<input type="text" id="bb_anonymous_author"  value="<?php bb_is_topic_edit() ? bb_topic_author()       : bb_is_reply_edit() ? bb_reply_author()       : bb_current_anonymous_user_data( 'name' );    ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_anonymous_name" />
		</p>

		<p>
			<label for="bb_anonymous_email"><?php _e( 'Mail (will not be published) (required):', 'bbpress' ); ?></label><br />
			<input type="text" id="bb_anonymous_email"   value="<?php bb_is_topic_edit() ? bb_topic_author_email() : bb_is_reply_edit() ? bb_reply_author_email() : bb_current_anonymous_user_data( 'email' );   ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_anonymous_email" />
		</p>

		<p>
			<label for="bb_anonymous_website"><?php _e( 'Website:', 'bbpress' ); ?></label><br />
			<input type="text" id="bb_anonymous_website" value="<?php bb_is_topic_edit() ? bb_topic_author_url()   : bb_is_reply_edit() ? bb_reply_author_url()   : bb_current_anonymous_user_data( 'website' ); ?>" tabindex="<?php bb_tab_index(); ?>" size="40" name="bb_anonymous_website" />
		</p>

		<?php do_action( 'bb_theme_anonymous_form_extras_bottom' ); ?>

	</fieldset>

	<?php do_action( 'bb_theme_after_anonymous_form' ); ?>

<?php endif; ?>
