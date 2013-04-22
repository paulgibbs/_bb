<?php

/**
 * Search Content Part
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<div id="bbpress-forums">

	<?php bb_breadcrumb(); ?>

	<?php bb_set_query_name( 'bb_search' ); ?>

	<?php do_action( 'bb_template_before_search' ); ?>

	<?php if ( bb_has_search_results() ) : ?>

		 <?php bb_get_template_part( 'pagination', 'search' ); ?>

		 <?php bb_get_template_part( 'loop',       'search' ); ?>

		 <?php bb_get_template_part( 'pagination', 'search' ); ?>

	<?php elseif ( bb_get_search_terms() ) : ?>

		 <?php bb_get_template_part( 'feedback',   'no-search' ); ?>

	<?php else : ?>

		<?php bb_get_template_part( 'form', 'search' ); ?>

	<?php endif; ?>

	<?php do_action( 'bb_template_after_search_results' ); ?>

</div>

