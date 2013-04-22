<?php

/**
 * Single View Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php bb_set_query_name( 'bb_view' ); ?>

	<?php if ( bb_view_query() ) : ?>

		<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

		<?php bb_get_template_part( 'loop',       'topics'    ); ?>

		<?php bb_get_template_part( 'pagination', 'topics'    ); ?>

	<?php else : ?>

		<?php bb_get_template_part( 'feedback',   'no-topics' ); ?>

	<?php endif; ?>

	<?php bb_reset_query_name(); ?>

</div>
