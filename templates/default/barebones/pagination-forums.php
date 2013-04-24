<?php

/**
 * Pagination for pages of topics (when viewing a forum)
 *
 * @package barebones
 * @subpackage Theme
 */

?>

<?php do_action( 'bb_template_before_pagination_loop' ); ?>

<div class="bbp-pagination">
	<div class="bbp-pagination-count">

		<?php bb_forum_pagination_count(); ?>

	</div>

	<div class="bbp-pagination-links">

		<?php bb_forum_pagination_links(); ?>

	</div>
</div>

<?php do_action( 'bb_template_after_pagination_loop' ); ?>
