<?php

/**
 * User Topics Created
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

	<?php do_action( 'bb_template_before_user_topics_created' ); ?>

	<div id="bbp-user-topics-started" class="bbp-user-topics-started">
		<h2 class="entry-title"><?php _e( 'Forum Topics Started', 'bbpress' ); ?></h2>
		<div class="bbp-user-section">

			<?php if ( bb_get_user_topics_started() ) : ?>

				<?php bb_get_template_part( 'pagination', 'topics' ); ?>

				<?php bb_get_template_part( 'loop',       'topics' ); ?>

				<?php bb_get_template_part( 'pagination', 'topics' ); ?>

			<?php else : ?>

				<p><?php bb_is_user_home() ? _e( 'You have not created any topics.', 'bbpress' ) : _e( 'This user has not created any topics.', 'bbpress' ); ?></p>

			<?php endif; ?>

		</div>
	</div><!-- #bbp-user-topics-started -->

	<?php do_action( 'bb_template_after_user_topics_created' ); ?>
