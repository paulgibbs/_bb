<?php

/**
 * Single Reply Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php do_action( 'bb_template_before_single_reply' ); ?>

	<?php if ( post_password_required() ) : ?>

		<?php bb_get_template_part( 'form', 'protected' ); ?>

	<?php else : ?>

		<?php bb_get_template_part( 'loop', 'single-reply' ); ?>

	<?php endif; ?>

	<?php do_action( 'bb_template_after_single_reply' ); ?>

</div>
