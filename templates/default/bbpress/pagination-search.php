<?php

/**
 * Pagination for pages of search results 
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php do_action( 'bb_template_before_pagination_loop' ); ?>

<div class="bbp-pagination">
	<div class="bbp-pagination-count">

		<?php bb_search_pagination_count(); ?>

	</div>

	<div class="bbp-pagination-links">

		<?php bb_search_pagination_links(); ?>

	</div>
</div>

<?php do_action( 'bb_template_after_pagination_loop' ); ?>
