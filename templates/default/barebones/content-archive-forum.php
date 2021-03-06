<?php

/**
 * Archive Forum Content Part
 *
 * @package Barebones
 * @subpackage Theme
 */

?>

<div id="barebones-forums">

	<div class="bb-search-form">

		<?php bb_get_template_part( 'form', 'search' ); ?>

	</div>

	<?php bb_breadcrumb(); ?>

	<?php do_action( 'bb_template_before_forums_index' ); ?>

	<?php if ( bb_has_forums() ) : ?>

		<?php bb_get_template_part( 'loop',     'forums'    ); ?>

	<?php else : ?>

		<?php bb_get_template_part( 'feedback', 'no-forums' ); ?>

	<?php endif; ?>

	<?php do_action( 'bb_template_after_forums_index' ); ?>

</div>
