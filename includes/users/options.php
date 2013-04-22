<?php

/**
 * bbPress User Options
 *
 * @package bbPress
 * @subpackage UserOptions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the default user options and their values
 *
 * @since barebones (1.0)
 * @return array Filtered user option names and values
 */
function bb_get_default_user_options() {

	// Default options
	return apply_filters( 'bb_get_default_user_options', array(
		'_bb_last_posted'   => '0', // For checking flooding
		'_bb_topic_count'   => '0', // Total topics per site
		'_bb_reply_count'   => '0', // Total replies per site
		'_bb_favorites'     => '',  // Favorite topics per site
		'_bb_subscriptions' => ''   // Subscribed topics per site
	) );
}

/**
 * Add default user options
 *
 * This is destructive, so existing bbPress user options will be overridden.
 *
 * @since barebones (1.0)
 * @uses bb_get_default_user_options() To get default options
 * @uses update_user_option() Adds default options
 * @uses do_action() Calls 'bb_add_user_options'
 */
function bb_add_user_options( $user_id = 0 ) {

	// Validate user id
	$user_id = bb_get_user_id( $user_id );
	if ( empty( $user_id ) )
		return;

	// Add default options
	foreach ( bb_get_default_user_options() as $key => $value )
		update_user_option( $user_id, $key, $value );

	// Allow previously activated plugins to append their own user options.
	do_action( 'bb_add_user_options', $user_id );
}

/**
 * Delete default user options
 *
 * Hooked to bb_uninstall, it is only called once when bbPress is uninstalled.
 * This is destructive, so existing bbPress user options will be destroyed.
 *
 * @since barebones (1.0)
 * @uses bb_get_default_user_options() To get default options
 * @uses delete_user_option() Removes default options
 * @uses do_action() Calls 'bb_delete_options'
 */
function bb_delete_user_options( $user_id = 0 ) {

	// Validate user id
	$user_id = bb_get_user_id( $user_id );
	if ( empty( $user_id ) )
		return;

	// Add default options
	foreach ( bb_get_default_user_options() as $key => $value )
		delete_user_option( $user_id, $key );

	// Allow previously activated plugins to append their own options.
	do_action( 'bb_delete_user_options', $user_id );
}

/**
 * Add filters to each bbPress option and allow them to be overloaded from
 * inside the $bbp->options array.
 *
 * @since barebones (1.0)
 * @uses bb_get_default_user_options() To get default options
 * @uses add_filter() To add filters to 'pre_option_{$key}'
 * @uses do_action() Calls 'bb_add_option_filters'
 */
function bb_setup_user_option_filters() {

	// Add filters to each bbPress option
	foreach ( bb_get_default_user_options() as $key => $value )
		add_filter( 'get_user_option_' . $key, 'bb_filter_get_user_option', 10, 3 );

	// Allow previously activated plugins to append their own options.
	do_action( 'bb_setup_user_option_filters' );
}

/**
 * Filter default options and allow them to be overloaded from inside the
 * $bbp->user_options array.
 *
 * @since barebones (1.0)
 * @param bool $value Optional. Default value false
 * @return mixed false if not overloaded, mixed if set
 */
function bb_filter_get_user_option( $value = false, $option = '', $user = 0 ) {
	$bbp = bbpress();

	// Check the options global for preset value
	if ( isset( $user->ID ) && isset( $bbp->user_options[$user->ID] ) && !empty( $bbp->user_options[$user->ID][$option] ) )
		$value = $bbp->user_options[$user->ID][$option];

	// Always return a value, even if false
	return $value;
}

/** Post Counts ***************************************************************/

/**
 * Output a users topic count
 *
 * @since barebones (1.0)
 *
 * @param int $user_id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses bb_get_user_topic_count()
 * @return string
 */
function bb_user_topic_count( $user_id = 0, $integer = false ) {
	echo bb_get_user_topic_count( $user_id, $integer );
}
	/**
	 * Return a users reply count
	 *
	 * @since barebones (1.0)
	 *
	 * @param int $user_id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses bb_get_user_id()
	 * @uses get_user_option()
	 * @uses apply_filters()
	 * @return string
	 */
	function bb_get_user_topic_count( $user_id = 0, $integer = false ) {

		// Validate user id
		$user_id = bb_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$count  = (int) get_user_option( '_bb_topic_count', $user_id );
		$filter = ( false == $integer ) ? 'bb_get_user_topic_count_int' : 'bb_get_user_topic_count';

		return apply_filters( $filter, $count, $user_id );
	}

/**
 * Output a users reply count
 *
 * @since barebones (1.0)
 *
 * @param int $user_id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses bb_get_user_reply_count()
 * @return string
 */
function bb_user_reply_count( $user_id = 0, $integer = false ) {
	echo bb_get_user_reply_count( $user_id, $integer );
}
	/**
	 * Return a users reply count
	 *
	 * @since barebones (1.0)
	 *
	 * @param int $user_id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses bb_get_user_id()
	 * @uses get_user_option()
	 * @uses apply_filters()
	 * @return string
	 */
	function bb_get_user_reply_count( $user_id = 0, $integer = false ) {

		// Validate user id
		$user_id = bb_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$count  = (int) get_user_option( '_bb_reply_count', $user_id );
		$filter = ( true == $integer ) ? 'bb_get_user_topic_count_int' : 'bb_get_user_topic_count';

		return apply_filters( $filter, $count, $user_id );
	}

/**
 * Output a users total post count
 *
 * @since barebones (1.0)
 *
 * @param int $user_id
 * @param boolean $integer Optional. Whether or not to format the result
 * @uses bb_get_user_post_count()
 * @return string
 */
function bb_user_post_count( $user_id = 0, $integer = false ) {
	echo bb_get_user_post_count( $user_id, $integer );
}
	/**
	 * Return a users total post count
	 *
	 * @since barebones (1.0)
	 *
	 * @param int $user_id
	 * @param boolean $integer Optional. Whether or not to format the result
	 * @uses bb_get_user_id()
	 * @uses get_user_option()
	 * @uses apply_filters()
	 * @return string
	 */
	function bb_get_user_post_count( $user_id = 0, $integer = false ) {

		// Validate user id
		$user_id = bb_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$topics  = bb_get_user_topic_count( $user_id, true );
		$replies = bb_get_user_reply_count( $user_id, true );
		$count   = (int) $topics + $replies;
		$filter  = ( true == $integer ) ? 'bb_get_user_post_count_int' : 'bb_get_user_post_count';

		return apply_filters( $filter, $count, $user_id );
	}

/** Last Posted ***************************************************************/

/**
 * Update a users last posted time, for use with post throttling
 *
 * @since barebones (1.0)
 * @param int $user_id User ID to update
 * @param int $time Time in time() format
 * @return bool False if no user or failure, true if successful
 */
function bb_update_user_last_posted( $user_id = 0, $time = 0 ) {

	// Validate user id
	$user_id = bb_get_user_id( $user_id );
	if ( empty( $user_id ) )
		return false;

	// Set time to now if nothing is passed
	if ( empty( $time ) )
		$time = time();

	return update_user_option( $user_id, '_bb_last_posted', $time );
}

/**
 * Output the raw value of the last posted time.
 *
 * @since barebones (1.0)
 * @param int $user_id User ID to retrieve value for
 * @uses bb_get_user_last_posted() To output the last posted time
 */
function bb_user_last_posted( $user_id = 0 ) {
	echo bb_get_user_last_posted( $user_id );
}

	/**
	 * Return the raw value of teh last posted time.
	 *
	 * @since barebones (1.0)
	 * @param int $user_id User ID to retrieve value for
	 * @return mixed False if no user, time() format if exists
	 */
	function bb_get_user_last_posted( $user_id = 0 ) {

		// Validate user id
		$user_id = bb_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;

		$time = get_user_option( '_bb_last_posted', $user_id );

		return apply_filters( 'bb_get_user_last_posted', $time, $user_id );
	}
