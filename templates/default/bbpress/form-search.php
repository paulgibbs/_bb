<?php

/**
 * Search 
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<form role="search" method="get" id="bbp-search-form" action="<?php bb_search_url(); ?>">
	<div>
		<label class="screen-reader-text hidden" for="bb_search"><?php _e( 'Search for:', 'bbpress' ); ?></label>
		<input tabindex="<?php bb_tab_index(); ?>" type="text" value="<?php echo esc_attr( bb_get_search_terms() ); ?>" name="bb_search" id="bb_search" />
		<input tabindex="<?php bb_tab_index(); ?>" class="button" type="submit" id="bb_search_submit" value="<?php esc_attr_e( 'Search', 'bbpress' ); ?>" />
	</div>
</form>
