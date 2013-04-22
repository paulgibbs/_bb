<?php

/**
 * Single User Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php do_action( 'bb_template_notices' ); ?>

	<div id="bbp-user-wrapper">
		<?php bb_get_template_part( 'user', 'details' ); ?>

		<div id="bbp-user-body">
			<?php if ( bb_is_favorites()                 ) bb_get_template_part( 'user', 'favorites'       ); ?>
			<?php if ( bb_is_subscriptions()             ) bb_get_template_part( 'user', 'subscriptions'   ); ?>
			<?php if ( bb_is_single_user_topics()        ) bb_get_template_part( 'user', 'topics-created'  ); ?>
			<?php if ( bb_is_single_user_replies()       ) bb_get_template_part( 'user', 'replies-created' ); ?>
			<?php if ( bb_is_single_user_edit()          ) bb_get_template_part( 'form', 'user-edit'       ); ?>
			<?php if ( bb_is_single_user_profile()       ) bb_get_template_part( 'user', 'profile'         ); ?>
		</div>
	</div>
</div>
