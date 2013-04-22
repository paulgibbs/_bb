<?php

/**
 * Archive Topic Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php if ( bb_is_topic_tag() ) bb_topic_tag_description(); ?>

	<?php do_action( 'bb_template_before_topics_index' ); ?>

	<?php if ( bb_has_topics() ) : ?>

		<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

		<?php bb_get_template_part( 'loop',       'topics'    ); ?>

		<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

	<?php else : ?>

		<?php bb_get_template_part( 'feedback',   'no-topics' ); ?>

	<?php endif; ?>

	<?php do_action( 'bb_template_after_topics_index' ); ?>

</div>
