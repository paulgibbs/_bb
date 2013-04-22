<?php

/**
 * bbPress Search Functions
 *
 * @package bbPress
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Query *********************************************************************/

/**
 * Run the search query
 *
 * @since barebones (1.0)
 *
 * @param mixed $new_args New arguments
 * @uses bb_get_search_query_args() To get the search query args
 * @uses bb_parse_args() To parse the args
 * @uses bb_has_search_results() To make the search query
 * @return bool False if no results, otherwise if search results are there
 */
function bb_search_query( $new_args = '' ) {

	// Existing arguments 
	$query_args = bb_get_search_query_args();

	// Merge arguments
	if ( !empty( $new_args ) ) {
		$new_args   = bb_parse_args( $new_args, '', 'search_query' );
		$query_args = array_merge( $query_args, $new_args );
	}

	return bb_has_search_results( $query_args );
}

/**
 * Return the search's query args
 *
 * @since barebones (1.0)
 *
 * @uses bb_get_search_terms() To get the search terms
 * @return array Query arguments
 */
function bb_get_search_query_args() {

	// Get search terms
	$search_terms = bb_get_search_terms();
	$retval = !empty( $search_terms ) ? array( 's' => $search_terms ) : array();

	return apply_filters( 'bb_get_search_query_args', $retval );
}
