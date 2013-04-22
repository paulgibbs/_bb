<?php

/**
 * Topic Tag Edit Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php bb_topic_tag_description(); ?>

	<?php do_action( 'bb_template_before_topic_tag_edit' ); ?>

	<?php bb_get_template_part( 'form', 'topic-tag' ); ?>

	<?php do_action( 'bb_template_after_topic_tag_edit' ); ?>

</div>
