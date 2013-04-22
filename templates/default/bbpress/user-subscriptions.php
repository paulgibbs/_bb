<?php

/**
 * User Subscriptions
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

	<?php do_action( 'bb_template_before_user_subscriptions' ); ?>

	<?php if ( bb_is_subscriptions_active() ) : ?>

		<?php if ( bb_is_user_home() || current_user_can( 'edit_users' ) ) : ?>

			<div id="bbp-user-subscriptions" class="bbp-user-subscriptions">
				<h2 class="entry-title"><?php _e( 'Subscribed Forum Topics', 'bbpress' ); ?></h2>
				<div class="bbp-user-section">

					<?php if ( bb_get_user_subscriptions() ) : ?>

						<?php bb_get_template_part( 'pagination', 'topics' ); ?>

						<?php bb_get_template_part( 'loop',       'topics' ); ?>

						<?php bb_get_template_part( 'pagination', 'topics' ); ?>

					<?php else : ?>

						<p><?php bb_is_user_home() ? _e( 'You are not currently subscribed to any topics.', 'bbpress' ) : _e( 'This user is not currently subscribed to any topics.', 'bbpress' ); ?></p>

					<?php endif; ?>

				</div>
			</div><!-- #bbp-user-subscriptions -->

		<?php endif; ?>

	<?php endif; ?>

	<?php do_action( 'bb_template_after_user_subscriptions' ); ?>
