<?php

/**
 * User Favorites
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

	<?php do_action( 'bb_template_before_user_favorites' ); ?>

	<div id="bbp-user-favorites" class="bbp-user-favorites">
		<h2 class="entry-title"><?php _e( 'Favorite Forum Topics', 'bbpress' ); ?></h2>
		<div class="bbp-user-section">

			<?php if ( bb_get_user_favorites() ) : ?>

				<?php bb_get_template_part( 'pagination', 'topics' ); ?>

				<?php bb_get_template_part( 'loop',       'topics' ); ?>

				<?php bb_get_template_part( 'pagination', 'topics' ); ?>

			<?php else : ?>

				<p><?php bb_is_user_home() ? _e( 'You currently have no favorite topics.', 'bbpress' ) : _e( 'This user has no favorite topics.', 'bbpress' ); ?></p>

			<?php endif; ?>

		</div>
	</div><!-- #bbp-user-favorites -->

	<?php do_action( 'bb_template_after_user_favorites' ); ?>
