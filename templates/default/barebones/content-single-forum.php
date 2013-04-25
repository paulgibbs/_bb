<?php

/**
 * Single Forum Content Part
 *
 * @package Barebones
 * @subpackage Theme
 */

?>

<div id="barebones-forums">

	<?php bb_breadcrumb(); ?>

	<?php do_action( 'bb_template_before_single_forum' ); ?>

	<?php if ( post_password_required() ) : ?>

		<?php bb_get_template_part( 'form', 'protected' ); ?>

	<?php else : ?>

		<?php bb_single_forum_description(); ?>

		<?php if ( bb_has_forums() ) : ?>

			<?php bb_get_template_part( 'loop', 'forums' ); ?>

		<?php endif; ?>

		<?php if ( !bb_is_forum_category() && bb_has_topics() ) : ?>

			<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

			<?php bb_get_template_part( 'loop',       'topics'    ); ?>

			<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

			<?php bb_get_template_part( 'form',       'topic'     ); ?>

		<?php elseif ( !bb_is_forum_category() ) : ?>

			<?php bb_get_template_part( 'feedback',   'no-topics' ); ?>

			<?php bb_get_template_part( 'form',       'topic'     ); ?>

		<?php endif; ?>

	<?php endif; ?>

	<?php do_action( 'bb_template_after_single_forum' ); ?>

</div>
