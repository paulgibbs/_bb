<?php

/**
 * Barebones Core Functions
 *
 * @package Barebones
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Versions ******************************************************************/

/**
 * Output the barebones version
 *
 * @since Barebones (1.0)
 * @uses bb_get_version() To get the barebones version
 */
function bb_version() {
	echo bb_get_version();
}
	/**
	 * Return the barebones version
	 *
	 * @since Barebones (1.0)
	 * @retrun string The barebones version
	 */
	function bb_get_version() {
		return barebones()->version;
	}

/**
 * Output the barebones database version
 *
 * @since Barebones (1.0)
 * @uses bb_get_version() To get the barebones version
 */
function bb_db_version() {
	echo bb_get_db_version();
}
	/**
	 * Return the barebones database version
	 *
	 * @since Barebones (1.0)
	 * @retrun string The barebones version
	 */
	function bb_get_db_version() {
		return barebones()->db_version;
	}

/**
 * Output the barebones database version directly from the database
 *
 * @since Barebones (1.0)
 * @uses bb_get_version() To get the current barebones version
 */
function bb_db_version_raw() {
	echo bb_get_db_version_raw();
}
	/**
	 * Return the barebones database version directly from the database
	 *
	 * @since Barebones (1.0)
	 * @retrun string The current barebones version
	 */
	function bb_get_db_version_raw() {
		return get_option( '_bb_db_version', '' );
	}

/** Post Meta *****************************************************************/

/**
 * Update a posts forum meta ID
 *
 * @since Barebones (1.0)
 *
 * @param int $post_id The post to update
 * @param int $forum_id The forum
 */
function bb_update_forum_id( $post_id, $forum_id ) {

	// Allow the forum ID to be updated 'just in time' before save
	$forum_id = apply_filters( 'bb_update_forum_id', $forum_id, $post_id );

	// Update the post meta forum ID
	update_post_meta( $post_id, '_bb_forum_id', (int) $forum_id );
}

/**
 * Update a posts topic meta ID
 *
 * @since Barebones (1.0)
 *
 * @param int $post_id The post to update
 * @param int $forum_id The forum
 */
function bb_update_topic_id( $post_id, $topic_id ) {

	// Allow the topic ID to be updated 'just in time' before save
	$topic_id = apply_filters( 'bb_update_topic_id', $topic_id, $post_id );

	// Update the post meta topic ID
	update_post_meta( $post_id, '_bb_topic_id', (int) $topic_id );
}

/**
 * Update a posts reply meta ID
 *
 * @since Barebones (1.0)
 *
 * @param int $post_id The post to update
 * @param int $forum_id The forum
 */
function bb_update_reply_id( $post_id, $reply_id ) {

	// Allow the reply ID to be updated 'just in time' before save
	$reply_id = apply_filters( 'bb_update_reply_id', $reply_id, $post_id );

	// Update the post meta reply ID
	update_post_meta( $post_id, '_bb_reply_id',(int) $reply_id );
}

/** Views *********************************************************************/

/**
 * Get the registered views
 *
 * Does nothing much other than return the {@link $bbp->views} variable
 *
 * @since Barebones (1.0)
 *
 * @return array Views
 */
function bb_get_views() {
	return barebones()->views;
}

/**
 * Register a barebones view
 *
 * @todo Implement feeds - See {@link http://trac.example.org/ticket/1422}
 *
 * @since Barebones (1.0)
 *
 * @param string $view View name
 * @param string $title View title
 * @param mixed $query_args {@link bb_has_topics()} arguments.
 * @param bool $feed Have a feed for the view? Defaults to true. NOT IMPLEMENTED
 * @param string $capability Capability that the current user must have
 * @uses sanitize_title() To sanitize the view name
 * @uses esc_html() To sanitize the view title
 * @return array The just registered (but processed) view
 */
function bb_register_view( $view, $title, $query_args = '', $feed = true, $capability = '' ) {

	// Bail if user does not have capability
	if ( ! empty( $capability ) && ! current_user_can( $capability ) )
		return false;

	$bbp   = barebones();
	$view  = sanitize_title( $view );
	$title = esc_html( $title );

	if ( empty( $view ) || empty( $title ) )
		return false;

	$query_args = bb_parse_args( $query_args, '', 'register_view' );

	// Set show_stickies to false if it wasn't supplied
	if ( !isset( $query_args['show_stickies'] ) )
		$query_args['show_stickies'] = false;

	$bbp->views[$view] = array(
		'title'  => $title,
		'query'  => $query_args,
		'feed'   => $feed
	);

	return $bbp->views[$view];
}

/**
 * Deregister a barebones view
 *
 * @since Barebones (1.0)
 *
 * @param string $view View name
 * @uses sanitize_title() To sanitize the view name
 * @return bool False if the view doesn't exist, true on success
 */
function bb_deregister_view( $view ) {
	$bbp  = barebones();
	$view = sanitize_title( $view );

	if ( !isset( $bbp->views[$view] ) )
		return false;

	unset( $bbp->views[$view] );

	return true;
}

/**
 * Run the view's query
 *
 * @since Barebones (1.0)
 *
 * @param string $view Optional. View id
 * @param mixed $new_args New arguments. See {@link bb_has_topics()}
 * @uses bb_get_view_id() To get the view id
 * @uses bb_get_view_query_args() To get the view query args
 * @uses sanitize_title() To sanitize the view name
 * @uses bb_has_topics() To make the topics query
 * @return bool False if the view doesn't exist, otherwise if topics are there
 */
function bb_view_query( $view = '', $new_args = '' ) {

	$view = bb_get_view_id( $view );
	if ( empty( $view ) )
		return false;

	$query_args = bb_get_view_query_args( $view );

	if ( !empty( $new_args ) ) {
		$new_args   = bb_parse_args( $new_args, '', 'view_query' );
		$query_args = array_merge( $query_args, $new_args );
	}

	return bb_has_topics( $query_args );
}

/**
 * Return the view's query arguments
 *
 * @since Barebones (1.0)
 *
 * @param string $view View name
 * @uses bb_get_view_id() To get the view id
 * @return array Query arguments
 */
function bb_get_view_query_args( $view ) {
	$view   = bb_get_view_id( $view );
	$retval = !empty( $view ) ? barebones()->views[$view]['query'] : false;

	return apply_filters( 'bb_get_view_query_args', $retval, $view );
}

/** Errors ********************************************************************/

/**
 * Adds an error message to later be output in the theme
 *
 * @since Barebones (1.0)
 *
 * @see WP_Error()
 * @uses WP_Error::add();
 *
 * @param string $code Unique code for the error message
 * @param string $message Translated error message
 * @param string $data Any additional data passed with the error message
 */
function bb_add_error( $code = '', $message = '', $data = '' ) {
	barebones()->errors->add( $code, $message, $data );
}

/**
 * Check if error messages exist in queue
 *
 * @since Barebones (1.0)
 *
 * @see WP_Error()
 *
 * @uses is_wp_error()
 * @usese WP_Error::get_error_codes()
 */
function bb_has_errors() {
	$has_errors = barebones()->errors->get_error_codes() ? true : false;

	return apply_filters( 'bb_has_errors', $has_errors, barebones()->errors );
}

/** Mentions ******************************************************************/

/**
 * Searches through the content to locate usernames, designated by an @ sign.
 *
 * @since Barebones (1.0)
 *
 * @param string $content The content
 * @return bool|array $usernames Existing usernames. False if no matches.
 */
function bb_find_mentions( $content = '' ) {
	$pattern   = '/[@]+([A-Za-z0-9-_\.@]+)\b/';
	preg_match_all( $pattern, $content, $usernames );
	$usernames = array_unique( array_filter( $usernames[1] ) );

	// Bail if no usernames
	if ( empty( $usernames ) )
		return false;

	return $usernames;
}

/**
 * Finds and links @-mentioned users in the content
 *
 * @since Barebones (1.0)
 *
 * @uses bb_find_mentions() To get usernames in content areas
 * @return string $content Content filtered for mentions
 */
function bb_mention_filter( $content = '' ) {

	// Get Usernames and bail if none exist
	$usernames = bb_find_mentions( $content );
	if ( empty( $usernames ) )
		return $content;

	// Loop through usernames and link to profiles
	foreach( (array) $usernames as $username ) {

		// Skip if username does not exist or user is not active
		$user = get_user_by( 'slug', $username );
		if ( empty( $user->ID ) || bb_is_user_inactive( $user->ID ) )
			continue;

		// Replace name in content
		$content = preg_replace( '/(@' . $username . '\b)/', sprintf( '<a href="%1$s" rel="nofollow">@%2$s</a>', bb_get_user_profile_url( $user->ID ), $username ), $content );
	}

	// Return modified content
	return $content;
}

/** Post Statuses *************************************************************/

/**
 * Return the public post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_public_status_id() {
	return barebones()->public_status_id;
}

/**
 * Return the pending post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_pending_status_id() {
	return barebones()->pending_status_id;
}

/**
 * Return the private post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_private_status_id() {
	return barebones()->private_status_id;
}

/**
 * Return the hidden post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_hidden_status_id() {
	return barebones()->hidden_status_id;
}

/**
 * Return the closed post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_closed_status_id() {
	return barebones()->closed_status_id;
}

/**
 * Return the spam post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_spam_status_id() {
	return barebones()->spam_status_id;
}

/**
 * Return the trash post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_trash_status_id() {
	return barebones()->trash_status_id;
}

/**
 * Return the orphan post status ID
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_orphan_status_id() {
	return barebones()->orphan_status_id;
}

/** Rewrite IDs ***************************************************************/

/**
 * Return the unique ID for user profile rewrite rules
 *
 * @since Barebones (1.0)
 * @return string
 */
function bb_get_user_rewrite_id() {
	return barebones()->user_id;
}

/**
 * Return the unique ID for all edit rewrite rules (forum|topic|reply|tag|user)
 *
 * @since Barebones (1.0)
 * @return string
 */
function bb_get_edit_rewrite_id() {
	return barebones()->edit_id;
}

/**
 * Return the unique ID for all search rewrite rules
 *
 * @since Barebones (1.0)
 *
 * @return string
 */
function bb_get_search_rewrite_id() {
	return barebones()->search_id;
}

/**
 * Return the unique ID for user topics rewrite rules
 *
 * @since Barebones (1.0)
 * @return string
 */
function bb_get_user_topics_rewrite_id() {
	return barebones()->tops_id;
}

/**
 * Return the unique ID for user replies rewrite rules
 *
 * @since Barebones (1.0)
 * @return string
 */
function bb_get_user_replies_rewrite_id() {
	return barebones()->reps_id;
}

/**
 * Return the unique ID for user caps rewrite rules
 *
 * @since Barebones (1.0)
 * @return string
 */
function bb_get_user_favorites_rewrite_id() {
	return barebones()->favs_id;
}

/**
 * Return the unique ID for user caps rewrite rules
 *
 * @since Barebones (1.0)
 * @return string
 */
function bb_get_user_subscriptions_rewrite_id() {
	return barebones()->subs_id;
}

/**
 * Return the unique ID for topic view rewrite rules
 *
 * @since Barebones (1.0)
 * @return string
 */
function bb_get_view_rewrite_id() {
	return barebones()->view_id;
}

/**
 * Delete a blogs rewrite rules, so that they are automatically rebuilt on
 * the subsequent page load.
 *
 * @since Barebones (1.0)
 */
function bb_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}

/** Requests ******************************************************************/

/**
 * Return true|false if this is a POST request
 *
 * @since Barebones (1.0)
 * @return bool
 */
function bb_is_post_request() {
	return (bool) ( 'POST' == strtoupper( $_SERVER['REQUEST_METHOD'] ) );
}

/**
 * Return true|false if this is a GET request
 *
 * @since Barebones (1.0)
 * @return bool
 */
function bb_is_get_request() {
	return (bool) ( 'GET' == strtoupper( $_SERVER['REQUEST_METHOD'] ) );
}

