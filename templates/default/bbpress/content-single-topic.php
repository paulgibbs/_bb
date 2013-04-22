<?php

/**
 * Single Topic Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php do_action( 'bb_template_before_single_topic' ); ?>

	<?php if ( post_password_required() ) : ?>

		<?php bb_get_template_part( 'form', 'protected' ); ?>

	<?php else : ?>

		<?php bb_topic_tag_list(); ?>

		<?php bb_single_topic_description(); ?>

		<?php if ( bb_show_lead_topic() ) : ?>

			<?php bb_get_template_part( 'content', 'single-topic-lead' ); ?>

		<?php endif; ?>

		<?php if ( bb_has_replies() ) : ?>

			<?php bb_get_template_part( 'pagination', 'replies' ); ?>

			<?php bb_get_template_part( 'loop',       'replies' ); ?>

			<?php bb_get_template_part( 'pagination', 'replies' ); ?>

		<?php endif; ?>

		<?php bb_get_template_part( 'form', 'reply' ); ?>

	<?php endif; ?>

	<?php do_action( 'bb_template_after_single_topic' ); ?>

</div>
