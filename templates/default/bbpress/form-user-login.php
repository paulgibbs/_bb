<?php

/**
 * User Login Form
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<form method="post" action="<?php bb_wp_login_action( array( 'context' => 'login_post' ) ); ?>" class="bbp-login-form">
	<fieldset class="bbp-form">
		<legend><?php _e( 'Log In', 'bbpress' ); ?></legend>

		<div class="bbp-username">
			<label for="user_login"><?php _e( 'Username', 'bbpress' ); ?>: </label>
			<input type="text" name="log" value="<?php bb_sanitize_val( 'user_login', 'text' ); ?>" size="20" id="user_login" tabindex="<?php bb_tab_index(); ?>" />
		</div>

		<div class="bbp-password">
			<label for="user_pass"><?php _e( 'Password', 'bbpress' ); ?>: </label>
			<input type="password" name="pwd" value="<?php bb_sanitize_val( 'user_pass', 'password' ); ?>" size="20" id="user_pass" tabindex="<?php bb_tab_index(); ?>" />
		</div>

		<div class="bbp-remember-me">
			<input type="checkbox" name="rememberme" value="forever" <?php checked( bb_get_sanitize_val( 'rememberme', 'checkbox' ) ); ?> id="rememberme" tabindex="<?php bb_tab_index(); ?>" />
			<label for="rememberme"><?php _e( 'Keep me signed in', 'bbpress' ); ?></label>
		</div>

		<div class="bbp-submit-wrapper">

			<?php do_action( 'login_form' ); ?>

			<button type="submit" tabindex="<?php bb_tab_index(); ?>" name="user-submit" class="button submit user-submit"><?php _e( 'Log In', 'bbpress' ); ?></button>

			<?php bb_user_login_fields(); ?>

		</div>
	</fieldset>
</form>
