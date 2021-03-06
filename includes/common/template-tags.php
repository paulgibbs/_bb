<?php

/**
 * Barebones Common Template Tags
 *
 * Common template tags are ones that are used by more than one component, like
 * forums, topics, replies, users, topic tags, etc...
 *
 * @package Barebones
 * @subpackage TemplateTags
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** URLs **********************************************************************/

/**
 * Ouput the forum URL
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_forums_url() To get the forums URL
 * @param string $path Additional path with leading slash
 */
function bb_forums_url( $path = '/' ) {
	echo bb_get_forums_url( $path );
}
	/**
	 * Return the forum URL
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses home_url() To get the home URL
	 * @uses bb_get_root_slug() To get the forum root location
	 * @param string $path Additional path with leading slash
	 */
	function bb_get_forums_url( $path = '/' ) {
		return home_url( bb_get_root_slug() . $path );
	}

/**
 * Ouput the forum URL
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topics_url() To get the topics URL
 * @param string $path Additional path with leading slash
 */
function bb_topics_url( $path = '/' ) {
	echo bb_get_topics_url( $path );
}
	/**
	 * Return the forum URL
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses home_url() To get the home URL
	 * @uses bb_get_topic_archive_slug() To get the topics archive location
	 * @param string $path Additional path with leading slash
	 * @return The URL to the topics archive
	 */
	function bb_get_topics_url( $path = '/' ) {
		return home_url( bb_get_topic_archive_slug() . $path );
	}

/** Add-on Actions ************************************************************/

/**
 * Add our custom head action to wp_head
 *
 * @since Barebones (1.0)
 *
 * @uses do_action() Calls 'bb_head'
*/
function bb_head() {
	do_action( 'bb_head' );
}

/**
 * Add our custom head action to wp_head
 *
 * @since Barebones (1.0)
 *
 * @uses do_action() Calls 'bb_footer'
 */
function bb_footer() {
	do_action( 'bb_footer' );
}

/** is_ ***********************************************************************/

/**
 * Check if current site is public
 *
 * @since Barebones (1.0)
 *
 * @param int $site_id
 * @uses get_current_blog_id()
 * @uses get_blog_option()
 * @uses apply_filters()
 * @return bool True if site is public, false if private
 */
function bb_is_site_public( $site_id = 0 ) {

	// Get the current site ID
	if ( empty( $site_id ) )
		$site_id = get_current_blog_id();

	// Get the site visibility setting
	$public = get_blog_option( $site_id, 'blog_public', 1 );

	return (bool) apply_filters( 'bb_is_site_public', $public, $site_id );
}

/**
 * Check if current page is a barebones forum
 *
 * @since Barebones (1.0)
 *
 * @param int $post_id Possible post_id to check
 * @uses bb_get_forum_post_type() To get the forum post type
 * @return bool True if it's a forum page, false if not
 */
function bb_is_forum( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a forum
	if ( !empty( $post_id ) && ( bb_get_forum_post_type() == get_post_type( $post_id ) ))
		$retval = true;

	return (bool) apply_filters( 'bb_is_forum', $retval, $post_id );
}

/**
 * Check if we are viewing a forum archive.
 *
 * @since Barebones (1.0)
 *
 * @uses is_post_type_archive() To check if we are looking at the forum archive
 * @uses bb_get_forum_post_type() To get the forum post type ID
 *
 * @return bool
 */
function bb_is_forum_archive() {

	// Default to false
	$retval = false;

	// In forum archive
	if ( is_post_type_archive( bb_get_forum_post_type() ) || bb_is_query_name( 'bb_forum_archive' ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_forum_archive', $retval );
}

/**
 * Viewing a single forum
 *
 * @since Barebones (1.0)
 *
 * @uses is_single()
 * @uses bb_get_forum_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function bb_is_single_forum() {

	// Assume false
	$retval = false;

	// Edit is not a single forum
	if ( bb_is_forum_edit() )
		return false;

	// Single and a match
	if ( is_singular( bb_get_forum_post_type() ) || bb_is_query_name( 'bb_single_forum' ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_forum', $retval );
}

/**
 * Check if current page is a forum edit page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_forum_edit is true
 * @return bool True if it's the forum edit page, false if not
 */
function bb_is_forum_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_forum_edit ) && ( $wp_query->bb_is_forum_edit == true ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' == $pagenow ) && ( get_post_type() == bb_get_forum_post_type() ) && ( !empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_forum_edit', $retval );
}

/**
 * Check if current page is a barebones topic
 *
 * @since Barebones (1.0)
 *
 * @param int $post_id Possible post_id to check
 * @uses bb_get_topic_post_type() To get the topic post type
 * @uses get_post_type() To get the post type of the post id
 * @return bool True if it's a topic page, false if not
 */
function bb_is_topic( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a topic
	if ( !empty( $post_id ) && ( bb_get_topic_post_type() == get_post_type( $post_id ) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_topic', $retval, $post_id );
}

/**
 * Viewing a single topic
 *
 * @since Barebones (1.0)
 *
 * @uses is_single()
 * @uses bb_get_topic_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function bb_is_single_topic() {

	// Assume false
	$retval = false;

	// Edit is not a single topic
	if ( bb_is_topic_edit() )
		return false;

	// Single and a match
	if ( is_singular( bb_get_topic_post_type() ) || bb_is_query_name( 'bb_single_topic' ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_topic', $retval );
}

/**
 * Check if we are viewing a topic archive.
 *
 * @since Barebones (1.0)
 *
 * @uses is_post_type_archive() To check if we are looking at the topic archive
 * @uses bb_get_topic_post_type() To get the topic post type ID
 *
 * @return bool
 */
function bb_is_topic_archive() {

	// Default to false
	$retval = false;

	// In topic archive
	if ( is_post_type_archive( bb_get_topic_post_type() ) || bb_is_query_name( 'bb_topic_archive' ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_topic_archive', $retval );
}

/**
 * Check if current page is a topic edit page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_topic_edit is true
 * @return bool True if it's the topic edit page, false if not
 */
function bb_is_topic_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_topic_edit ) && ( $wp_query->bb_is_topic_edit == true ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' == $pagenow ) && ( get_post_type() == bb_get_topic_post_type() ) && ( !empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_topic_edit', $retval );
}

/**
 * Check if current page is a topic merge page
 *
 * @since Barebones (1.0)
 *
 * @uses bb_is_topic_edit() To check if it's a topic edit page
 * @return bool True if it's the topic merge page, false if not
 */
function bb_is_topic_merge() {

	// Assume false
	$retval = false;

	// Check topic edit and GET params
	if ( bb_is_topic_edit() && !empty( $_GET['action'] ) && ( 'merge' == $_GET['action'] ) )
		return true;

	return (bool) apply_filters( 'bb_is_topic_merge', $retval );
}

/**
 * Check if current page is a topic split page
 *
 * @since Barebones (1.0)
 *
 * @uses bb_is_topic_edit() To check if it's a topic edit page
 * @return bool True if it's the topic split page, false if not
 */
function bb_is_topic_split() {

	// Assume false
	$retval = false;

	// Check topic edit and GET params
	if ( bb_is_topic_edit() && !empty( $_GET['action'] ) && ( 'split' == $_GET['action'] ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_topic_split', $retval );
}

/**
 * Check if the current page is a topic tag
 *
 * @since Barebones (1.0)
 *
 * @return bool True if it's a topic tag, false if not
 */
function bb_is_topic_tag() {

	// Bail if topic-tags are off
	if ( ! bb_allow_topic_tags() )
		return false;

	// Return false if editing a topic tag
	if ( bb_is_topic_tag_edit() )
		return false;

	// Assume false
	$retval = false;

	// Check tax and query vars
	if ( is_tax( bb_get_topic_tag_tax_id() ) || !empty( barebones()->topic_query->is_tax ) || get_query_var( 'bb_topic_tag' ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_topic_tag', $retval );
}

/**
 * Check if the current page is editing a topic tag
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_topic_tag_edit is true
 * @return bool True if editing a topic tag, false if not
 */
function bb_is_topic_tag_edit() {
	global $wp_query, $pagenow, $taxnow;

	// Bail if topic-tags are off
	if ( ! bb_allow_topic_tags() )
		return false;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_topic_tag_edit ) && ( true == $wp_query->bb_is_topic_tag_edit ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'edit-tags.php' == $pagenow ) && ( bb_get_topic_tag_tax_id() == $taxnow ) && ( !empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_topic_tag_edit', $retval );
}

/**
 * Check if the current post type is one of barebones's
 *
 * @since Barebones (1.0)
 *
 * @param mixed $the_post Optional. Post object or post ID.
 * @uses get_post_type()
 * @uses bb_get_forum_post_type()
 * @uses bb_get_topic_post_type()
 * @uses bb_get_reply_post_type()
 *
 * @return bool
 */
function bb_is_custom_post_type( $the_post = false ) {

	// Assume false
	$retval = false;

	// Viewing one of the barebones post types
	if ( in_array( get_post_type( $the_post ), array(
		bb_get_forum_post_type(),
		bb_get_topic_post_type(),
		bb_get_reply_post_type()
	) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_custom_post_type', $retval, $the_post );
}

/**
 * Check if current page is a barebones reply
 *
 * @since Barebones (1.0)
 *
 * @param int $post_id Possible post_id to check
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses get_post_type() To get the post type of the post id
 * @return bool True if it's a reply page, false if not
 */
function bb_is_reply( $post_id = 0 ) {

	// Assume false
	$retval = false;

	// Supplied ID is a reply
	if ( !empty( $post_id ) && ( bb_get_reply_post_type() == get_post_type( $post_id ) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_reply', $retval, $post_id );
}

/**
 * Check if current page is a reply edit page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_reply_edit is true
 * @return bool True if it's the reply edit page, false if not
 */
function bb_is_reply_edit() {
	global $wp_query, $pagenow;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_reply_edit ) && ( true == $wp_query->bb_is_reply_edit ) )
		$retval = true;

	// Editing in admin
	elseif ( is_admin() && ( 'post.php' == $pagenow ) && ( get_post_type() == bb_get_reply_post_type() ) && ( !empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_reply_edit', $retval );
}

/**
 * Check if current page is a reply move page
 *
 * @uses bb_is_reply_move() To check if it's a reply move page
 * @return bool True if it's the reply move page, false if not
 */
function bb_is_reply_move() {

	// Assume false
	$retval = false;

	// Check reply edit and GET params
	if ( bb_is_reply_edit() && !empty( $_GET['action'] ) && ( 'move' == $_GET['action'] ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_reply_move', $retval );
}

/**
 * Viewing a single reply
 *
 * @since Barebones (1.0)
 *
 * @uses is_single()
 * @uses bb_get_reply_post_type()
 * @uses get_post_type()
 * @uses apply_filters()
 *
 * @return bool
 */
function bb_is_single_reply() {

	// Assume false
	$retval = false;

	// Edit is not a single reply
	if ( bb_is_reply_edit() )
		return false;

	// Single and a match
	if ( is_singular( bb_get_reply_post_type() ) || ( bb_is_query_name( 'bb_single_reply' ) ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_reply', $retval );
}

/**
 * Check if current page is a barebones user's favorites page (profile page)
 *
 * @since Barebones (1.0)
 *
 * @return bool True if it's the favorites page, false if not
 */
function bb_is_favorites() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_favs ) && ( true == $wp_query->bb_is_single_user_favs ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_favorites', $retval );
}

/**
 * Check if current page is a barebones user's subscriptions page (profile page)
 *
 * @since Barebones (1.0)
 *
 * @return bool True if it's the subscriptions page, false if not
 */
function bb_is_subscriptions() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_subs ) && ( true == $wp_query->bb_is_single_user_subs ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_subscriptions', $retval );
}

/**
 * Check if current page shows the topics created by a barebones user (profile
 * page)
 *
 * @since Barebones (1.0)
 *
 * @return bool True if it's the topics created page, false if not
 */
function bb_is_topics_created() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_topics ) && ( true == $wp_query->bb_is_single_user_topics ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_topics_created', $retval );
}

/**
 * Check if current page shows the topics created by a barebones user (profile
 * page)
 *
 * @since Barebones (1.0)
 *
 * @return bool True if it's the topics created page, false if not
 */
function bb_is_replies_created() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_replies ) && ( true == $wp_query->bb_is_single_user_replies ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_replies_created', $retval );
}

/**
 * Check if current page is the currently logged in users author page
 *
 * @since Barebones (1.0)
 * @uses bb_is_single_user() Check query variable
 * @uses is_user_logged_in() Must be logged in to be home
 * @uses bb_get_displayed_user_id()
 * @uses bb_get_current_user_id()
 * @return bool True if it's the user's home, false if not
 */
function bb_is_user_home() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_home ) && ( true == $wp_query->bb_is_single_user_home ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_user_home', $retval );
}

/**
 * Check if current page is the currently logged in users author edit page
 *
 * @since Barebones (1.0)
 * @uses bb_is_single_user_edit() Check query variable
 * @uses is_user_logged_in() Must be logged in to be home
 * @uses bb_get_displayed_user_id()
 * @uses bb_get_current_user_id()
 * @return bool True if it's the user's home, false if not
 */
function bb_is_user_home_edit() {

	// Assume false
	$retval = false;

	if ( bb_is_user_home() && bb_is_single_user_edit() )
		$retval = true;

	return (bool) apply_filters( 'bb_is_user_home_edit', $retval );
}

/**
 * Check if current page is a user profile page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_single_user is set to true
 * @return bool True if it's a user's profile page, false if not
 */
function bb_is_single_user() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user ) && ( true == $wp_query->bb_is_single_user ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_user', $retval );
}

/**
 * Check if current page is a user profile edit page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_single_user_edit is set to true
 * @return bool True if it's a user's profile edit page, false if not
 */
function bb_is_single_user_edit() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_edit ) && ( true == $wp_query->bb_is_single_user_edit ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_user_edit', $retval );
}

/**
 * Check if current page is a user profile page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_single_user_profile is set to true
 * @return bool True if it's a user's profile page, false if not
 */
function bb_is_single_user_profile() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_profile ) && ( true == $wp_query->bb_is_single_user_profile ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_user_profile', $retval );
}

/**
 * Check if current page is a user topics created page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_single_user_topics is set to true
 * @return bool True if it's a user's topics page, false if not
 */
function bb_is_single_user_topics() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_topics ) && ( true == $wp_query->bb_is_single_user_topics ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_user_topics', $retval );
}

/**
 * Check if current page is a user replies created page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_single_user_replies is set to true
 * @return bool True if it's a user's replies page, false if not
 */
function bb_is_single_user_replies() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_single_user_replies ) && ( true == $wp_query->bb_is_single_user_replies ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_user_replies', $retval );
}

/**
 * Check if current page is a view page
 *
 * @since Barebones (1.0)
 *
 * @global WP_Query $wp_query To check if WP_Query::bb_is_view is true
 * @uses bb_is_query_name() To get the query name
 * @return bool Is it a view page?
 */
function bb_is_single_view() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_view ) && ( true == $wp_query->bb_is_view ) )
		$retval = true;

	// Check query name
	if ( empty( $retval ) && bb_is_query_name( 'bb_single_view' ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_single_view', $retval );
}

/**
 * Check if current page is a search page
 *
 * @since Barebones (1.0)
 *
 * @global WP_Query $wp_query To check if WP_Query::bb_is_search is true
 * @uses bb_is_query_name() To get the query name
 * @return bool Is it a search page?
 */
function bb_is_search() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_search ) && ( true == $wp_query->bb_is_search ) )
		$retval = true;

	// Check query name
	if ( empty( $retval ) && bb_is_query_name( 'bb_search' ) )
		$retval = true;

	// Check $_GET
	if ( empty( $retval ) && isset( $_GET[bb_get_search_rewrite_id()] ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_search', $retval );
}

/**
 * Check if current page is an edit page
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Query Checks if WP_Query::bb_is_edit is true
 * @return bool True if it's the edit page, false if not
 */
function bb_is_edit() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( !empty( $wp_query->bb_is_edit ) && ( $wp_query->bb_is_edit == true ) )
		$retval = true;

	return (bool) apply_filters( 'bb_is_edit', $retval );
}

/**
 * Use the above is_() functions to output a body class for each scenario
 *
 * @since Barebones (1.0)
 *
 * @param array $wp_classes
 * @param array $custom_classes
 * @uses bb_is_single_forum()
 * @uses bb_is_single_topic()
 * @uses bb_is_topic_edit()
 * @uses bb_is_topic_merge()
 * @uses bb_is_topic_split()
 * @uses bb_is_single_reply()
 * @uses bb_is_reply_edit()
 * @uses bb_is_reply_move()
 * @uses bb_is_single_view()
 * @uses bb_is_single_user_edit()
 * @uses bb_is_single_user()
 * @uses bb_is_user_home()
 * @uses bb_is_subscriptions()
 * @uses bb_is_favorites()
 * @uses bb_is_topics_created()
 * @uses bb_is_forum_archive()
 * @uses bb_is_topic_archive()
 * @uses bb_is_topic_tag()
 * @uses bb_is_topic_tag_edit()
 * @uses bb_get_topic_tag_tax_id()
 * @uses bb_get_topic_tag_slug()
 * @uses bb_get_topic_tag_id()
 * @return array Body Classes
 */
function bb_body_class( $wp_classes, $custom_classes = false ) {

	$bb_classes = array();

	/** Archives **************************************************************/

	if ( bb_is_forum_archive() ) {
		$bb_classes[] = bb_get_forum_post_type() . '-archive';

	} elseif ( bb_is_topic_archive() ) {
		$bb_classes[] = bb_get_topic_post_type() . '-archive';

	/** Topic Tags ************************************************************/

	} elseif ( bb_is_topic_tag() ) {
		$bb_classes[] = bb_get_topic_tag_tax_id();
		$bb_classes[] = bb_get_topic_tag_tax_id() . '-' . bb_get_topic_tag_slug();
		$bb_classes[] = bb_get_topic_tag_tax_id() . '-' . bb_get_topic_tag_id();
	} elseif ( bb_is_topic_tag_edit() ) {
		$bb_classes[] = bb_get_topic_tag_tax_id() . '-edit';
		$bb_classes[] = bb_get_topic_tag_tax_id() . '-' . bb_get_topic_tag_slug() . '-edit';
		$bb_classes[] = bb_get_topic_tag_tax_id() . '-' . bb_get_topic_tag_id()   . '-edit';

	/** Components ************************************************************/

	} elseif ( bb_is_single_forum() ) {
		$bb_classes[] = bb_get_forum_post_type();

	} elseif ( bb_is_single_topic() ) {
		$bb_classes[] = bb_get_topic_post_type();

	} elseif ( bb_is_single_reply() ) {
		$bb_classes[] = bb_get_reply_post_type();

	} elseif ( bb_is_topic_edit() ) {
		$bb_classes[] = bb_get_topic_post_type() . '-edit';

	} elseif ( bb_is_topic_merge() ) {
		$bb_classes[] = bb_get_topic_post_type() . '-merge';

	} elseif ( bb_is_topic_split() ) {
		$bb_classes[] = bb_get_topic_post_type() . '-split';

	} elseif ( bb_is_reply_edit() ) {
		$bb_classes[] = bb_get_reply_post_type() . '-edit';

	} elseif ( bb_is_reply_move() ) {
		$bb_classes[] = bb_get_reply_post_type() . '-move';

	} elseif ( bb_is_single_view() ) {
		$bb_classes[] = 'bb-view';

	/** User ******************************************************************/

	} elseif ( bb_is_single_user_edit() ) {
		$bb_classes[] = 'bb-user-edit';
		$bb_classes[] = 'single';
		$bb_classes[] = 'singular';

	} elseif ( bb_is_single_user() ) {
		$bb_classes[] = 'bb-user-page';
		$bb_classes[] = 'single';
		$bb_classes[] = 'singular';

	} elseif ( bb_is_user_home() ) {
		$bb_classes[] = 'bb-user-home';
		$bb_classes[] = 'single';
		$bb_classes[] = 'singular';

	} elseif ( bb_is_user_home_edit() ) {
		$bb_classes[] = 'bb-user-home-edit';
		$bb_classes[] = 'single';
		$bb_classes[] = 'singular';

	} elseif ( bb_is_topics_created() ) {
		$bb_classes[] = 'bb-topics-created';
		$bb_classes[] = 'single';
		$bb_classes[] = 'singular';

	} elseif ( bb_is_favorites() ) {
		$bb_classes[] = 'bb-favorites';
		$bb_classes[] = 'single';
		$bb_classes[] = 'singular';

	} elseif ( bb_is_subscriptions() ) {
		$bb_classes[] = 'bb-subscriptions';
		$bb_classes[] = 'single';
		$bb_classes[] = 'singular';

	/** Search ****************************************************************/

	} elseif ( bb_is_search() ) {
		$bb_classes[] = 'bb-search';
		$bb_classes[] = 'forum-search';
	}

	/** Clean up **************************************************************/

	// Add barebones class if we are within a barebones page
	if ( !empty( $bb_classes ) ) {
		$bb_classes[] = 'barebones';
	}

	// Merge WP classes with barebones classes and remove any duplicates
	$classes = array_unique( array_merge( (array) $bb_classes, (array) $wp_classes ) );

	return apply_filters( 'bb_get_the_body_class', $classes, $bb_classes, $wp_classes, $custom_classes );
}

/**
 * Use the above is_() functions to return if in any barebones page
 *
 * @since Barebones (1.0)
 *
 * @uses bb_is_single_forum()
 * @uses bb_is_single_topic()
 * @uses bb_is_topic_edit()
 * @uses bb_is_topic_merge()
 * @uses bb_is_topic_split()
 * @uses bb_is_single_reply()
 * @uses bb_is_reply_edit()
 * @uses bb_is_reply_move()
 * @uses bb_is_single_view()
 * @uses bb_is_single_user_edit()
 * @uses bb_is_single_user()
 * @uses bb_is_user_home()
 * @uses bb_is_subscriptions()
 * @uses bb_is_favorites()
 * @uses bb_is_topics_created()
 * @return bool In a barebones page
 */
function is_barebones() {

	// Defalt to false
	$retval = false;

	/** Archives **************************************************************/

	if ( bb_is_forum_archive() ) {
		$retval = true;

	} elseif ( bb_is_topic_archive() ) {
		$retval = true;

	/** Topic Tags ************************************************************/

	} elseif ( bb_is_topic_tag() ) {
		$retval = true;

	} elseif ( bb_is_topic_tag_edit() ) {
		$retval = true;

	/** Components ************************************************************/

	} elseif ( bb_is_single_forum() ) {
		$retval = true;

	} elseif ( bb_is_single_topic() ) {
		$retval = true;

	} elseif ( bb_is_single_reply() ) {
		$retval = true;

	} elseif ( bb_is_topic_edit() ) {
		$retval = true;

	} elseif ( bb_is_topic_merge() ) {
		$retval = true;

	} elseif ( bb_is_topic_split() ) {
		$retval = true;

	} elseif ( bb_is_reply_edit() ) {
		$retval = true;

	} elseif ( bb_is_reply_move() ) {
		$retval = true;

	} elseif ( bb_is_single_view() ) {
		$retval = true;

	/** User ******************************************************************/

	} elseif ( bb_is_single_user_edit() ) {
		$retval = true;

	} elseif ( bb_is_single_user() ) {
		$retval = true;

	} elseif ( bb_is_user_home() ) {
		$retval = true;

	} elseif ( bb_is_user_home_edit() ) {
		$retval = true;

	} elseif ( bb_is_topics_created() ) {
		$retval = true;

	} elseif ( bb_is_favorites() ) {
		$retval = true;

	} elseif ( bb_is_subscriptions() ) {
		$retval = true;

	/** Search ****************************************************************/

	} elseif ( bb_is_search() ) {
		$retval = true;
	}

	/** Done ******************************************************************/

	return (bool) apply_filters( 'is_barebones', $retval );
}

/** Forms *********************************************************************/

/**
 * Output the login form action url
 *
 * @since Barebones (1.0)
 *
 * @param string $url Pass a URL to redirect to
 * @uses add_query_arg() To add a arg to the url
 * @uses site_url() Toget the site url
 * @uses apply_filters() Calls 'bb_wp_login_action' with the url and args
 */
function bb_wp_login_action( $args = '' ) {

	// Parse arguments against default values
	$r = bb_parse_args( $args, array(
		'action'  => '',
		'context' => ''
	), 'login_action' );

	// Add action as query arg
	if ( !empty( $r['action'] ) ) {
		$login_url = add_query_arg( array( 'action' => $r['action'] ), 'wp-login.php' );

	// No query arg
	} else {
		$login_url = 'wp-login.php';
	}

	$login_url = site_url( $login_url, $r['context'] );

	echo apply_filters( 'bb_wp_login_action', $login_url, $r );
}

/**
 * Output hidden request URI field for user forms.
 *
 * The referer link is the current Request URI from the server super global. To
 * check the field manually, use bb_get_redirect_to().
 *
 * @since Barebones (1.0)
 *
 * @param string $redirect_to Pass a URL to redirect to
 *
 * @uses wp_get_referer() To get the referer
 * @uses esc_attr() To escape the url
 * @uses apply_filters() Calls 'bb_redirect_to_field', passes field and to
 */
function bb_redirect_to_field( $redirect_to = '' ) {

	// Make sure we are directing somewhere
	if ( empty( $redirect_to ) ) {
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$redirect_to = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} else {
			$redirect_to = wp_get_referer();
		}
	}

	// Remove loggedout query arg if it's there
	$redirect_to    = (string) esc_attr( remove_query_arg( 'loggedout', $redirect_to ) );
	$redirect_field = '<input type="hidden" id="bb_redirect_to" name="redirect_to" value="' . $redirect_to . '" />';

	echo apply_filters( 'bb_redirect_to_field', $redirect_field, $redirect_to );
}

/**
 * Echo sanitized $_REQUEST value.
 *
 * Use the $input_type parameter to properly process the value. This
 * ensures correct sanitization of the value for the receiving input.
 *
 * @since Barebones (1.0)
 *
 * @param string $request Name of $_REQUEST to look for
 * @param string $input_type Type of input. Default: text. Accepts:
 *                            textarea|password|select|radio|checkbox
 * @uses bb_get_sanitize_val() To sanitize the value.
 */
function bb_sanitize_val( $request = '', $input_type = 'text' ) {
	echo bb_get_sanitize_val( $request, $input_type );
}
	/**
	 * Return sanitized $_REQUEST value.
	 *
	 * Use the $input_type parameter to properly process the value. This
	 * ensures correct sanitization of the value for the receiving input.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param string $request Name of $_REQUEST to look for
	 * @param string $input_type Type of input. Default: text. Accepts:
	 *                            textarea|password|select|radio|checkbox
	 * @uses esc_attr() To escape the string
	 * @uses apply_filters() Calls 'bb_get_sanitize_val' with the sanitized
	 *                        value, request and input type
	 * @return string Sanitized value ready for screen display
	 */
	function bb_get_sanitize_val( $request = '', $input_type = 'text' ) {

		// Check that requested
		if ( empty( $_REQUEST[$request] ) )
			return false;

		// Set request varaible
		$pre_ret_val = $_REQUEST[$request];

		// Treat different kinds of fields in different ways
		switch ( $input_type ) {
			case 'text'     :
			case 'textarea' :
				$retval = esc_attr( stripslashes( $pre_ret_val ) );
				break;

			case 'password' :
			case 'select'   :
			case 'radio'    :
			case 'checkbox' :
			default :
				$retval = esc_attr( $pre_ret_val );
				break;
		}

		return apply_filters( 'bb_get_sanitize_val', $retval, $request, $input_type );
	}

/**
 * Output the current tab index of a given form
 *
 * Use this function to handle the tab indexing of user facing forms within a
 * template file. Calling this function will automatically increment the global
 * tab index by default.
 *
 * @since Barebones (1.0)
 *
 * @param int $auto_increment Optional. Default true. Set to false to prevent
 *                             increment
 */
function bb_tab_index( $auto_increment = true ) {
	echo bb_get_tab_index( $auto_increment );
}

	/**
	 * Output the current tab index of a given form
	 *
	 * Use this function to handle the tab indexing of user facing forms
	 * within a template file. Calling this function will automatically
	 * increment the global tab index by default.
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters Allows return value to be filtered
	 * @param int $auto_increment Optional. Default true. Set to false to
	 *                             prevent the increment
	 * @return int $bb->tab_index The global tab index
	 */
	function bb_get_tab_index( $auto_increment = true ) {
		$bbp = barebones();

		if ( true === $auto_increment )
			++$bb->tab_index;

		return apply_filters( 'bb_get_tab_index', (int) $bb->tab_index );
	}

/**
 * Output a select box allowing to pick which forum/topic a new topic/reply
 * belongs in.
 *
 * Can be used for any post type, but is mostly used for topics and forums.
 *
 * @since Barebones (1.0)
 *
 * @param mixed $args See {@link bb_get_dropdown()} for arguments
 */
function bb_dropdown( $args = '' ) {
	echo bb_get_dropdown( $args );
}
	/**
	 * Output a select box allowing to pick which forum/topic a new
	 * topic/reply belongs in.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param mixed $args The function supports these args:
	 *  - post_type: Post type, defaults to bb_get_forum_post_type() (bb_forum)
	 *  - selected: Selected ID, to not have any value as selected, pass
	 *               anything smaller than 0 (due to the nature of select
	 *               box, the first value would of course be selected -
	 *               though you can have that as none (pass 'show_none' arg))
	 *  - sort_column: Sort by? Defaults to 'menu_order, post_title'
	 *  - post_parent: Post parent. Defaults to 0
	 *  - post_status: Which all post_statuses to find in? Can be an array
	 *                  or CSV of publish, category, closed, private, spam,
	 *                  trash (based on post type) - if not set, these are
	 *                  automatically determined based on the post_type
	 *  - posts_per_page: Retrieve all forums/topics. Defaults to -1 to get
	 *                     all posts
	 *  - walker: Which walker to use? Defaults to
	 *             {@link BB_Walker_Dropdown}
	 *  - select_id: ID of the select box. Defaults to 'bb_forum_id'
	 *  - tab: Tabindex value. False or integer
	 *  - options_only: Show only <options>? No <select>?
	 *  - show_none: False or something like __( '(No Forum)', 'barebones' ),
	 *                will have value=""
	 *  - none_found: False or something like
	 *                 __( 'No forums to post to!', 'barebones' )
	 *  - disable_categories: Disable forum categories and closed forums?
	 *                         Defaults to true. Only for forums and when
	 *                         the category option is displayed.
	 * @uses BB_Walker_Dropdown() As the default walker to generate the
	 *                              dropdown
	 * @uses current_user_can() To check if the current user can read
	 *                           private forums
	 * @uses bb_get_forum_post_type() To get the forum post type
	 * @uses bb_get_topic_post_type() To get the topic post type
	 * @uses walk_page_dropdown_tree() To generate the dropdown using the
	 *                                  walker
	 * @uses apply_filters() Calls 'bb_get_dropdown' with the dropdown
	 *                        and args
	 * @return string The dropdown
	 */
	function bb_get_dropdown( $args = '' ) {

		/** Arguments *********************************************************/

		// Parse arguments against default values
		$r = bb_parse_args( $args, array(
			'post_type'          => bb_get_forum_post_type(),
			'selected'           => 0,
			'sort_column'        => 'menu_order',
			'exclude'            => array(),
			'post_parent'        => null,
			'numberposts'        => -1,
			'orderby'            => 'menu_order',
			'order'              => 'ASC',
			'walker'             => '',

			// Output-related
			'select_id'          => 'bb_forum_id',
			'tab'                => bb_get_tab_index(),
			'options_only'       => false,
			'show_none'          => false,
			'none_found'         => false,
			'disable_categories' => true,
			'disabled'           => ''
		), 'get_dropdown' );

		if ( empty( $r['walker'] ) ) {
			$r['walker']            = new BB_Walker_Dropdown();
			$r['walker']->tree_type = $r['post_type'];
		}

		// Force 0
		if ( is_numeric( $r['selected'] ) && $r['selected'] < 0 ) {
			$r['selected'] = 0;
		}

		// Force array
		if ( !empty( $r['exclude'] ) && !is_array( $r['exclude'] ) ) {
			$r['exclude'] = explode( ',', $r['exclude'] );
		}

		/** Post Status *******************************************************/

		// Define local variable(s)
		$post_stati = array();

		// Public
		$post_stati[] = bb_get_public_status_id();

		// Forums
		if ( bb_get_forum_post_type() == $r['post_type'] ) {

			// Private forums
			if ( current_user_can( 'read_private_forums' ) ) {
				$post_stati[] = bb_get_private_status_id();
			}

			// Hidden forums
			if ( current_user_can( 'read_hidden_forums' ) ) {
				$post_stati[] = bb_get_hidden_status_id();
			}
		}

		// Setup the post statuses
		$r['post_status'] = implode( ',', $post_stati );

		/** Setup variables ***************************************************/

		$name      = esc_attr( $r['select_id'] );
		$select_id = $name;
		$tab       = (int) $r['tab'];
		$retval    = '';
		$disabled  = disabled( isset( barebones()->options[$r['disabled']] ), true, false );
		$post_arr  = array(
			'post_type'          => $r['post_type'],
			'post_status'        => $r['post_status'],
			'sort_column'        => $r['sort_column'],
			'exclude'            => $r['exclude'],
			'post_parent'        => $r['post_parent'],
			'numberposts'        => $r['numberposts'],
			'orderby'            => $r['orderby'],
			'order'              => $r['order'],
			'walker'             => $r['walker'],
			'disable_categories' => $r['disable_categories']
		);

		$posts = get_posts( $post_arr );

		/** Drop Down *********************************************************/

		// Items found
		if ( !empty( $posts ) ) {
			if ( empty( $r['options_only'] ) ) {
				$tab     = !empty( $tab ) ? ' tabindex="' . $tab . '"' : '';
				$retval .= '<select name="' . $name . '" id="' . $select_id . '"' . $tab  . $disabled . '>' . "\n";
			}

			$retval .= !empty( $r['show_none'] ) ? "\t<option value=\"\" class=\"level-0\">" . $r['show_none'] . '</option>' : '';
			$retval .= walk_page_dropdown_tree( $posts, 0, $r );

			if ( empty( $r['options_only'] ) ) {
				$retval .= '</select>';
			}

		// No items found - Display feedback if no custom message was passed
		} elseif ( empty( $r['none_found'] ) ) {

			// Switch the response based on post type
			switch ( $r['post_type'] ) {

				// Topics
				case bb_get_topic_post_type() :
					$retval = __( 'No topics available', 'barebones' );
					break;

				// Forums
				case bb_get_forum_post_type() :
					$retval = __( 'No forums available', 'barebones' );
					break;

				// Any other
				default :
					$retval = __( 'None available', 'barebones' );
					break;
			}
		}

		return apply_filters( 'bb_get_dropdown', $retval, $r );
	}

/**
 * Output the required hidden fields when creating/editing a forum
 *
 * @since Barebones (1.0)
 *
 * @uses bb_is_forum_edit() To check if it's the forum edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses bb_forum_id() To output the forum id
 * @uses bb_is_single_forum() To check if it's a forum page
 * @uses bb_forum_id() To output the forum id
 */
function bb_forum_form_fields() {

	if ( bb_is_forum_edit() ) : ?>

		<input type="hidden" name="action"       id="bb_post_action" value="bb-edit-forum" />
		<input type="hidden" name="bb_forum_id" id="bb_forum_id"    value="<?php bb_forum_id(); ?>" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'bb-unfiltered-html-forum_' . bb_get_forum_id(), '_bb_unfiltered_html_forum', false );

		?>

		<?php wp_nonce_field( 'bb-edit-forum_' . bb_get_forum_id() );

	else :

		if ( bb_is_single_forum() ) : ?>

			<input type="hidden" name="bb_forum_parent_id" id="bb_forum_parent_id" value="<?php bb_forum_parent_id(); ?>" />

		<?php endif; ?>

		<input type="hidden" name="action" id="bb_post_action" value="bb-new-forum" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'bb-unfiltered-html-forum_new', '_bb_unfiltered_html_forum', false );

		?>

		<?php wp_nonce_field( 'bb-new-forum' );

	endif;
}

/**
 * Output the required hidden fields when creating/editing a topic
 *
 * @since Barebones (1.0)
 *
 * @uses bb_is_topic_edit() To check if it's the topic edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses bb_topic_id() To output the topic id
 * @uses bb_is_single_forum() To check if it's a forum page
 * @uses bb_forum_id() To output the forum id
 */
function bb_topic_form_fields() {

	if ( bb_is_topic_edit() ) : ?>

		<input type="hidden" name="action"       id="bb_post_action" value="bb-edit-topic" />
		<input type="hidden" name="bb_topic_id" id="bb_topic_id"    value="<?php bb_topic_id(); ?>" />

		<?php

		if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'bb-unfiltered-html-topic_' . bb_get_topic_id(), '_bb_unfiltered_html_topic', false );

		?>

		<?php wp_nonce_field( 'bb-edit-topic_' . bb_get_topic_id() );

	else :

		if ( bb_is_single_forum() ) : ?>

			<input type="hidden" name="bb_forum_id" id="bb_forum_id" value="<?php bb_forum_id(); ?>" />

		<?php endif; ?>

		<input type="hidden" name="action" id="bb_post_action" value="bb-new-topic" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'bb-unfiltered-html-topic_new', '_bb_unfiltered_html_topic', false ); ?>

		<?php wp_nonce_field( 'bb-new-topic' );

	endif;
}

/**
 * Output the required hidden fields when creating/editing a reply
 *
 * @since Barebones (1.0)
 *
 * @uses bb_is_reply_edit() To check if it's the reply edit page
 * @uses wp_nonce_field() To generate hidden nonce fields
 * @uses bb_reply_id() To output the reply id
 * @uses bb_topic_id() To output the topic id
 * @uses bb_forum_id() To output the forum id
 */
function bb_reply_form_fields() {

	if ( bb_is_reply_edit() ) : ?>

		<input type="hidden" name="bb_reply_title" id="bb_reply_title" value="<?php bb_reply_title(); ?>" />
		<input type="hidden" name="bb_reply_id"    id="bb_reply_id"    value="<?php bb_reply_id(); ?>" />
		<input type="hidden" name="action"          id="bb_post_action" value="bb-edit-reply" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'bb-unfiltered-html-reply_' . bb_get_reply_id(), '_bb_unfiltered_html_reply', false ); ?>

		<?php wp_nonce_field( 'bb-edit-reply_' . bb_get_reply_id() );

	else : ?>

		<input type="hidden" name="bb_reply_title" id="bb_reply_title" value="<?php printf( __( 'Reply To: %s', 'barebones' ), bb_get_topic_title() ); ?>" />
		<input type="hidden" name="bb_topic_id"    id="bb_topic_id"    value="<?php bb_topic_id(); ?>" />
		<input type="hidden" name="action"          id="bb_post_action" value="bb-new-reply" />

		<?php if ( current_user_can( 'unfiltered_html' ) )
			wp_nonce_field( 'bb-unfiltered-html-reply_' . bb_get_topic_id(), '_bb_unfiltered_html_reply', false ); ?>

		<?php wp_nonce_field( 'bb-new-reply' );

		// Show redirect field if not viewing a specific topic
		if ( bb_is_query_name( 'bb_single_topic' ) ) :
			bb_redirect_to_field( get_permalink() );

		endif;
	endif;
}

/**
 * Output the required hidden fields when editing a user
 *
 * @since Barebones (1.0)
 *
 * @uses bb_displayed_user_id() To output the displayed user id
 * @uses wp_nonce_field() To generate a hidden nonce field
 * @uses wp_referer_field() To generate a hidden referer field
 */
function bb_edit_user_form_fields() {
?>

	<input type="hidden" name="action"  id="bb_post_action" value="bb-update-user" />
	<input type="hidden" name="user_id" id="user_id"         value="<?php bb_displayed_user_id(); ?>" />

	<?php wp_nonce_field( 'update-user_' . bb_get_displayed_user_id() );
}

/**
 * Merge topic form fields
 *
 * Output the required hidden fields when merging a topic
 *
 * @since Barebones (1.0)
 *
 * @uses wp_nonce_field() To generate a hidden nonce field
 * @uses bb_topic_id() To output the topic id
 */
function bb_merge_topic_form_fields() {
?>

	<input type="hidden" name="action"       id="bb_post_action" value="bb-merge-topic" />
	<input type="hidden" name="bb_topic_id" id="bb_topic_id"    value="<?php bb_topic_id(); ?>" />

	<?php wp_nonce_field( 'bb-merge-topic_' . bb_get_topic_id() );
}

/**
 * Split topic form fields
 *
 * Output the required hidden fields when splitting a topic
 *
 * @since Barebones (1.0)
 *
 * @uses wp_nonce_field() To generate a hidden nonce field
 */
function bb_split_topic_form_fields() {
?>

	<input type="hidden" name="action"       id="bb_post_action" value="bb-split-topic" />
	<input type="hidden" name="bb_reply_id" id="bb_reply_id"    value="<?php echo absint( $_GET['reply_id'] ); ?>" />

	<?php wp_nonce_field( 'bb-split-topic_' . bb_get_topic_id() );
}

/**
 * Move reply form fields
 *
 * Output the required hidden fields when moving a reply
 *
 * @uses wp_nonce_field() To generate a hidden nonce field
 */
function bb_move_reply_form_fields() {
?>

	<input type="hidden" name="action"       id="bb_post_action" value="bb-move-reply" />
	<input type="hidden" name="bb_reply_id" id="bb_reply_id"    value="<?php echo absint( $_GET['reply_id'] ); ?>" />

	<?php wp_nonce_field( 'bb-move-reply_' . bb_get_reply_id() );
}

/**
 * Output a textarea or TinyMCE if enabled
 *
 * @since Barebones (1.0)
 *
 * @param array $args
 * @uses bb_get_the_content() To return the content to output
 */
function bb_the_content( $args = array() ) {
	echo bb_get_the_content( $args );
}
	/**
	 * Return a textarea or TinyMCE if enabled
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $args
	 *
	 * @uses apply_filter() To filter args and output
	 * @uses wp_parse_pargs() To compare args
	 * @uses bb_use_wp_editor() To see if WP editor is in use
	 * @uses bb_is_edit() To see if we are editing something
	 * @uses wp_editor() To output the WordPress editor
	 *
	 * @return string HTML from output buffer
	 */
	function bb_get_the_content( $args = array() ) {

		// Parse arguments against default values
		$r = bb_parse_args( $args, array(
			'context'           => 'topic',
			'before'            => '<div class="bb-the-content-wrapper">',
			'after'             => '</div>',
			'wpautop'           => true,
			'media_buttons'     => false,
			'textarea_rows'     => '12',
			'tabindex'          => bb_get_tab_index(),
			'tabfocus_elements' => 'bb_topic_title,bb_topic_tags',
			'editor_class'      => 'bb-the-content',
			'tinymce'           => true,
			'teeny'             => true,
			'quicktags'         => true,
			'dfw'               => false
		), 'get_the_content' );

		// Assume we are not editing
		$post_content = call_user_func( 'bb_get_form_' . $r['context'] . '_content' );

		// Start an output buffor
		ob_start();

		// Output something before the editor
		if ( !empty( $r['before'] ) ) {
			echo $r['before'];
		}

		// Use TinyMCE if available
		if ( bb_use_wp_editor() ) :

			// Enable additional TinyMCE plugins before outputting the editor
			add_filter( 'tiny_mce_plugins',   'bb_get_tiny_mce_plugins'   );
			add_filter( 'teeny_mce_plugins',  'bb_get_tiny_mce_plugins'   );
			add_filter( 'teeny_mce_buttons',  'bb_get_teeny_mce_buttons'  );
			add_filter( 'quicktags_settings', 'bb_get_quicktags_settings' );

			// Output the editor
			wp_editor( htmlspecialchars_decode( $post_content, ENT_QUOTES ), 'bb_' . $r['context'] . '_content', array(
				'wpautop'           => $r['wpautop'],
				'media_buttons'     => $r['media_buttons'],
				'textarea_rows'     => $r['textarea_rows'],
				'tabindex'          => $r['tabindex'],
				'tabfocus_elements' => $r['tabfocus_elements'],
				'editor_class'      => $r['editor_class'],
				'tinymce'           => $r['tinymce'],
				'teeny'             => $r['teeny'],
				'quicktags'         => $r['quicktags'],
				'dfw'               => $r['dfw'],
			) );

			// Remove additional TinyMCE plugins after outputting the editor
			remove_filter( 'tiny_mce_plugins',   'bb_get_tiny_mce_plugins'   );
			remove_filter( 'teeny_mce_plugins',  'bb_get_tiny_mce_plugins'   );
			remove_filter( 'teeny_mce_buttons',  'bb_get_teeny_mce_buttons'  );
			remove_filter( 'quicktags_settings', 'bb_get_quicktags_settings' );

		/**
		 * Fallback to normal textarea.
		 *
		 * Note that we do not use esc_textarea() here to prevent double
		 * escaping the editable output, mucking up existing content.
		 */
		else : ?>

			<textarea id="bb_<?php echo esc_attr( $r['context'] ); ?>_content" class="<?php echo esc_attr( $r['editor_class'] ); ?>" name="bb_<?php echo esc_attr( $r['context'] ); ?>_content" cols="60" rows="<?php echo esc_attr( $r['textarea_rows'] ); ?>" tabindex="<?php echo esc_attr( $r['tabindex'] ); ?>"><?php echo $post_content; ?></textarea>

		<?php endif;

		// Output something after the editor
		if ( !empty( $r['after'] ) ) {
			echo $r['after'];
		}

		// Put the output into a usable variable
		$output = ob_get_contents();

		// Flush the output buffer
		ob_end_clean();

		return apply_filters( 'bb_get_the_content', $output, $args, $post_content );
	}

/**
 * Edit TinyMCE plugins to match core behaviour
 *
 * @since Barebones (1.0)
 *
 * @param array $plugins
 * @see tiny_mce_plugins, teeny_mce_plugins
 * @return array
 */
function bb_get_tiny_mce_plugins( $plugins = array() ) {

	// Unset fullscreen
	foreach ( $plugins as $key => $value ) {
		if ( 'fullscreen' == $value ) {
			unset( $plugins[$key] );
			break;
		}
	}

	// Add the tabfocus plugin
	$plugins[] = 'tabfocus';

	return apply_filters( 'bb_get_tiny_mce_plugins', $plugins );
}

/**
 * Edit TeenyMCE buttons to match allowedtags
 *
 * @since Barebones (1.0)
 *
 * @param array $buttons
 * @see teeny_mce_buttons
 * @return array
 */
function bb_get_teeny_mce_buttons( $buttons = array() ) {

	// Remove some buttons from TeenyMCE
	$buttons = array_diff( $buttons, array(
		'underline',
		'justifyleft',
		'justifycenter',
		'justifyright'
	) );

	// Images
	array_push( $buttons, 'image' );

	return apply_filters( 'bb_get_teeny_mce_buttons', $buttons );
}

/**
 * Edit TinyMCE quicktags buttons to match allowedtags
 *
 * @since Barebones (1.0)
 *
 * @param array $buttons
 * @see quicktags_settings
 * @return array Quicktags settings
 */
function bb_get_quicktags_settings( $settings = array() ) {

	// Get buttons out of settings
	$buttons_array = explode( ',', $settings['buttons'] );

	// Diff the ones we don't want out
	$buttons = array_diff( $buttons_array, array(
		'ins',
		'more',
		'spell'
	) );

	// Put them back into a string in the $settings array
	$settings['buttons'] = implode( ',', $buttons );

	return apply_filters( 'bb_get_quicktags_settings', $settings );
}

/** Views *********************************************************************/

/**
 * Output the view id
 *
 * @since Barebones (1.0)
 *
 * @param string $view Optional. View id
 * @uses bb_get_view_id() To get the view id
 */
function bb_view_id( $view = '' ) {
	echo bb_get_view_id( $view );
}

	/**
	 * Get the view id
	 *
	 * If a view id is supplied, that is used. Otherwise the 'bb_view'
	 * query var is checked for.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param string $view Optional. View id.
	 * @uses sanitize_title() To sanitize the view id
	 * @uses get_query_var() To get the view id from query var 'bb_view'
	 * @return bool|string ID on success, false on failure
	 */
	function bb_get_view_id( $view = '' ) {
		$bbp = barebones();

		$view = !empty( $view ) ? sanitize_title( $view ) : get_query_var( 'bb_view' );

		if ( array_key_exists( $view, $bb->views ) )
			return $view;

		return false;
	}

/**
 * Output the view name aka title
 *
 * @since Barebones (1.0)
 *
 * @param string $view Optional. View id
 * @uses bb_get_view_title() To get the view title
 */
function bb_view_title( $view = '' ) {
	echo bb_get_view_title( $view );
}

	/**
	 * Get the view name aka title
	 *
	 * If a view id is supplied, that is used. Otherwise the bb_view
	 * query var is checked for.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param string $view Optional. View id
	 * @uses bb_get_view_id() To get the view id
	 * @return bool|string Title on success, false on failure
	 */
	function bb_get_view_title( $view = '' ) {
		$bbp = barebones();

		$view = bb_get_view_id( $view );
		if ( empty( $view ) )
			return false;

		return $bb->views[$view]['title'];
	}

/**
 * Output the view url
 *
 * @since Barebones (1.0)
 *
 * @param string $view Optional. View id
 * @uses bb_get_view_url() To get the view url
 */
function bb_view_url( $view = false ) {
	echo bb_get_view_url( $view );
}
	/**
	 * Return the view url
	 *
	 * @since Barebones (1.0)
	 *
	 * @param string $view Optional. View id
	 * @uses sanitize_title() To sanitize the view id
	 * @uses home_url() To get blog home url
	 * @uses add_query_arg() To add custom args to the url
	 * @uses apply_filters() Calls 'bb_get_view_url' with the view url,
	 *                        used view id
	 * @return string View url (or home url if the view was not found)
	 */
	function bb_get_view_url( $view = false ) {
		global $wp_rewrite;

		$view = bb_get_view_id( $view );
		if ( empty( $view ) )
			return home_url();

		// Pretty permalinks
		if ( $wp_rewrite->using_permalinks() ) {
			$url = $wp_rewrite->root . bb_get_view_slug() . '/' . $view;
			$url = home_url( user_trailingslashit( $url ) );

		// Unpretty permalinks
		} else {
			$url = add_query_arg( array( 'bb_view' => $view ), home_url( '/' ) );
		}

		return apply_filters( 'bb_get_view_link', $url, $view );
	}

/** Query *********************************************************************/

/**
 * Check the passed parameter against the current _bb_query_name
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_query_name() Get the query var '_bb_query_name'
 * @return bool True if match, false if not
 */
function bb_is_query_name( $name = '' )  {
	return (bool) ( bb_get_query_name() == $name );
}

/**
 * Get the '_bb_query_name' setting
 *
 * @since Barebones (1.0)
 *
 * @uses get_query_var() To get the query var '_bb_query_name'
 * @return string To return the query var value
 */
function bb_get_query_name()  {
	return get_query_var( '_bb_query_name' );
}

/**
 * Set the '_bb_query_name' setting to $name
 *
 * @since Barebones (1.0)
 *
 * @param string $name What to set the query var to
 * @uses set_query_var() To set the query var '_bb_query_name'
 */
function bb_set_query_name( $name = '' )  {
	set_query_var( '_bb_query_name', $name );
}

/**
 * Used to clear the '_bb_query_name' setting
 *
 * @since Barebones (1.0)
 *
 * @uses bb_set_query_name() To set the query var '_bb_query_name' value to ''
 */
function bb_reset_query_name() {
	bb_set_query_name();
}

/** Breadcrumbs ***************************************************************/

/**
 * Output the page title as a breadcrumb
 *
 * @since Barebones (1.0)
 *
 * @param string $sep Separator. Defaults to '&larr;'
 * @param bool $current_page Include the current item
 * @param bool $root Include the root page if one exists
 * @uses bb_get_breadcrumb() To get the breadcrumb
 */
function bb_title_breadcrumb( $args = array() ) {
	echo bb_get_breadcrumb( $args );
}

/**
 * Output a breadcrumb
 *
 * @since Barebones (1.0)
 *
 * @param string $sep Separator. Defaults to '&larr;'
 * @param bool $current_page Include the current item
 * @param bool $root Include the root page if one exists
 * @uses bb_get_breadcrumb() To get the breadcrumb
 */
function bb_breadcrumb( $args = array() ) {
	echo bb_get_breadcrumb( $args );
}
	/**
	 * Return a breadcrumb ( forum -> topic -> reply )
	 *
	 * @since Barebones (1.0)
	 *
	 * @param string $sep Separator. Defaults to '&larr;'
	 * @param bool $current_page Include the current item
	 * @param bool $root Include the root page if one exists
	 *
	 * @uses get_post() To get the post
	 * @uses bb_get_forum_permalink() To get the forum link
	 * @uses bb_get_topic_permalink() To get the topic link
	 * @uses bb_get_reply_permalink() To get the reply link
	 * @uses get_permalink() To get the permalink
	 * @uses bb_get_forum_post_type() To get the forum post type
	 * @uses bb_get_topic_post_type() To get the topic post type
	 * @uses bb_get_reply_post_type() To get the reply post type
	 * @uses bb_get_forum_title() To get the forum title
	 * @uses bb_get_topic_title() To get the topic title
	 * @uses bb_get_reply_title() To get the reply title
	 * @uses get_the_title() To get the title
	 * @uses apply_filters() Calls 'bb_get_breadcrumb' with the crumbs
	 * @return string Breadcrumbs
	 */
	function bb_get_breadcrumb( $args = array() ) {

		// Turn off breadcrumbs
		if ( apply_filters( 'bb_no_breadcrumb', is_front_page() ) )
			return;

		// Define variables
		$front_id         = $root_id                                 = 0;
		$ancestors        = $crumbs           = $tag_data            = array();
		$pre_root_text    = $pre_front_text   = $pre_current_text    = '';
		$pre_include_root = $pre_include_home = $pre_include_current = true;

		/** Home Text *********************************************************/

		// No custom home text
		if ( empty( $args['home_text'] ) ) {

			$front_id = get_option( 'page_on_front' );

			// Set home text to page title
			if ( !empty( $front_id ) ) {
				$pre_front_text = get_the_title( $front_id );

			// Default to 'Home'
			} else {
				$pre_front_text = __( 'Home', 'barebones' );
			}
		}

		/** Root Text *********************************************************/

		// No custom root text
		if ( empty( $args['root_text'] ) ) {
			$page = bb_get_page_by_path( bb_get_root_slug() );
			if ( !empty( $page ) ) {
				$root_id = $page->ID;
			}
			$pre_root_text = bb_get_forum_archive_title();
		}

		/** Includes **********************************************************/

		// Root slug is also the front page
		if ( !empty( $front_id ) && ( $front_id == $root_id ) ) {
			$pre_include_root = false;
		}

		// Don't show root if viewing forum archive
		if ( bb_is_forum_archive() ) {
			$pre_include_root = false;
		}

		// Don't show root if viewing page in place of forum archive
		if ( !empty( $root_id ) && ( ( is_single() || is_page() ) && ( $root_id == get_the_ID() ) ) ) {
			$pre_include_root = false;
		}

		/** Current Text ******************************************************/

		// Search page
		if ( bb_is_search() ) {
			$pre_current_text = bb_get_search_title();

		// Forum archive
		} elseif ( bb_is_forum_archive() ) {
			$pre_current_text = bb_get_forum_archive_title();

		// Topic archive
		} elseif ( bb_is_topic_archive() ) {
			$pre_current_text = bb_get_topic_archive_title();

		// View
		} elseif ( bb_is_single_view() ) {
			$pre_current_text = bb_get_view_title();

		// Single Forum
		} elseif ( bb_is_single_forum() ) {
			$pre_current_text = bb_get_forum_title();

		// Single Topic
		} elseif ( bb_is_single_topic() ) {
			$pre_current_text = bb_get_topic_title();

		// Single Topic
		} elseif ( bb_is_single_reply() ) {
			$pre_current_text = bb_get_reply_title();

		// Topic Tag (or theme compat topic tag)
		} elseif ( bb_is_topic_tag() || ( get_query_var( 'bb_topic_tag' ) && !bb_is_topic_tag_edit() ) ) {

			// Always include the tag name
			$tag_data[] = bb_get_topic_tag_name();

			// If capable, include a link to edit the tag
			if ( current_user_can( 'manage_topic_tags' ) ) {
				$tag_data[] = '<a href="' . bb_get_topic_tag_edit_link() . '" class="bb-edit-topic-tag-link">' . __( '(Edit)', 'barebones' ) . '</a>';
			}

			// Implode the results of the tag data
			$pre_current_text = sprintf( __( 'Topic Tag: %s', 'barebones' ), implode( ' ', $tag_data ) );

		// Edit Topic Tag
		} elseif ( bb_is_topic_tag_edit() ) {
			$pre_current_text = __( 'Edit', 'barebones' );

		// Single
		} else {
			$pre_current_text = get_the_title();
		}

		/** Parse Args ********************************************************/

		// Parse args
		$r = bb_parse_args( $args, array(

			// HTML
			'before'          => '<div class="bb-breadcrumb"><p>',
			'after'           => '</p></div>',

			// Separator
			'sep'             => is_rtl() ? __( '&lsaquo;', 'barebones' ) : __( '&rsaquo;', 'barebones' ),
			'pad_sep'         => 1,
			'sep_before'      => '<span class="bb-breadcrumb-sep">',
			'sep_after'       => '</span>',

			// Crumbs
			'crumb_before'    => '',
			'crumb_after'     => '',

			// Home
			'include_home'    => $pre_include_home,
			'home_text'       => $pre_front_text,

			// Forum root
			'include_root'    => $pre_include_root,
			'root_text'       => $pre_root_text,

			// Current
			'include_current' => $pre_include_current,
			'current_text'    => $pre_current_text,
			'current_before'  => '<span class="bb-breadcrumb-current">',
			'current_after'   => '</span>',
		), 'get_breadcrumb' );

		/** Ancestors *********************************************************/

		// Get post ancestors
		if ( is_singular() || bb_is_forum_edit() || bb_is_topic_edit() || bb_is_reply_edit() ) {
			$ancestors = array_reverse( (array) get_post_ancestors( get_the_ID() ) );
		}

		// Do we want to include a link to home?
		if ( !empty( $r['include_home'] ) || empty( $r['home_text'] ) ) {
			$crumbs[] = '<a href="' . trailingslashit( home_url() ) . '" class="bb-breadcrumb-home">' . $r['home_text'] . '</a>';
		}

		// Do we want to include a link to the forum root?
		if ( !empty( $r['include_root'] ) || empty( $r['root_text'] ) ) {

			// Page exists at root slug path, so use its permalink
			$page = bb_get_page_by_path( bb_get_root_slug() );
			if ( !empty( $page ) ) {
				$root_url = get_permalink( $page->ID );

			// Use the root slug
			} else {
				$root_url = get_post_type_archive_link( bb_get_forum_post_type() );
			}

			// Add the breadcrumb
			$crumbs[] = '<a href="' . $root_url . '" class="bb-breadcrumb-root">' . $r['root_text'] . '</a>';
		}

		// Ancestors exist
		if ( !empty( $ancestors ) ) {

			// Loop through parents
			foreach( (array) $ancestors as $parent_id ) {

				// Parents
				$parent = get_post( $parent_id );

				// Skip parent if empty or error
				if ( empty( $parent ) || is_wp_error( $parent ) )
					continue;

				// Switch through post_type to ensure correct filters are applied
				switch ( $parent->post_type ) {

					// Forum
					case bb_get_forum_post_type() :
						$crumbs[] = '<a href="' . bb_get_forum_permalink( $parent->ID ) . '" class="bb-breadcrumb-forum">' . bb_get_forum_title( $parent->ID ) . '</a>';
						break;

					// Topic
					case bb_get_topic_post_type() :
						$crumbs[] = '<a href="' . bb_get_topic_permalink( $parent->ID ) . '" class="bb-breadcrumb-topic">' . bb_get_topic_title( $parent->ID ) . '</a>';
						break;

					// Reply (Note: not in most themes)
					case bb_get_reply_post_type() :
						$crumbs[] = '<a href="' . bb_get_reply_permalink( $parent->ID ) . '" class="bb-breadcrumb-reply">' . bb_get_reply_title( $parent->ID ) . '</a>';
						break;

					// WordPress Post/Page/Other
					default :
						$crumbs[] = '<a href="' . get_permalink( $parent->ID ) . '" class="bb-breadcrumb-item">' . get_the_title( $parent->ID ) . '</a>';
						break;
				}
			}

		// Edit topic tag
		} elseif ( bb_is_topic_tag_edit() ) {
			$crumbs[] = '<a href="' . get_term_link( bb_get_topic_tag_id(), bb_get_topic_tag_tax_id() ) . '" class="bb-breadcrumb-topic-tag">' . sprintf( __( 'Topic Tag: %s', 'barebones' ), bb_get_topic_tag_name() ) . '</a>';

		// Search
		} elseif ( bb_is_search() && bb_get_search_terms() ) {
			$crumbs[] = '<a href="' . home_url( bb_get_search_slug() ) . '" class="bb-breadcrumb-search">' . __( 'Search', 'barebones' ) . '</a>';
		}

		/** Current ***********************************************************/

		// Add current page to breadcrumb
		if ( !empty( $r['include_current'] ) || empty( $r['pre_current_text'] ) ) {
			$crumbs[] = $r['current_before'] . $r['current_text'] . $r['current_after'];
		}

		/** Separator *********************************************************/

		// Wrap the separator in before/after before padding and filter
		if ( ! empty( $r['sep'] ) ) {
			$sep = $r['sep_before'] . $r['sep'] . $r['sep_after'];
		}

		// Pad the separator
		if ( !empty( $r['pad_sep'] ) ) {
			if ( function_exists( 'mb_strlen' ) ) {
				$sep = str_pad( $sep, mb_strlen( $sep ) + ( (int) $r['pad_sep'] * 2 ), ' ', STR_PAD_BOTH );
			} else {
				$sep = str_pad( $sep, strlen( $sep ) + ( (int) $r['pad_sep'] * 2 ), ' ', STR_PAD_BOTH );
			}
		}

		/** Finish Up *********************************************************/

		// Filter the separator and breadcrumb
		$sep    = apply_filters( 'bb_breadcrumb_separator', $sep    );
		$crumbs = apply_filters( 'bb_breadcrumbs',          $crumbs );

		// Build the trail
		$trail  = !empty( $crumbs ) ? ( $r['before'] . $r['crumb_before'] . implode( $sep . $r['crumb_after'] . $r['crumb_before'] , $crumbs ) . $r['crumb_after'] . $r['after'] ) : '';

		return apply_filters( 'bb_get_breadcrumb', $trail, $crumbs, $r );
	}

/** Topic Tags ***************************************************************/

/**
 * Output all of the allowed tags in HTML format with attributes.
 *
 * This is useful for displaying in the post area, which elements and
 * attributes are supported. As well as any plugins which want to display it.
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_allowed_tags()
 */
function bb_allowed_tags() {
	echo bb_get_allowed_tags();
}
	/**
	 * Display all of the allowed tags in HTML format with attributes.
	 *
	 * This is useful for displaying in the post area, which elements and
	 * attributes are supported. As well as any plugins which want to display it.
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses bb_kses_allowed_tags() To get the allowed tags
	 * @uses apply_filters() Calls 'bb_allowed_tags' with the tags
	 * @return string HTML allowed tags entity encoded.
	 */
	function bb_get_allowed_tags() {

		$allowed = '';

		foreach ( (array) bb_kses_allowed_tags() as $tag => $attributes ) {
			$allowed .= '<' . $tag;
			if ( 0 < count( $attributes ) ) {
				foreach ( array_keys( $attributes ) as $attribute ) {
					$allowed .= ' ' . $attribute . '=""';
				}
			}
			$allowed .= '> ';
		}

		return apply_filters( 'bb_get_allowed_tags', htmlentities( $allowed ) );
	}

/** Errors & Messages *********************************************************/

/**
 * Display possible errors & messages inside a template file
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Error Barebones::errors::get_error_codes() To get the error codes
 * @uses WP_Error Barebones::errors::get_error_data() To get the error data
 * @uses WP_Error Barebones::errors::get_error_messages() To get the error
 *                                                       messages
 * @uses is_wp_error() To check if it's a {@link WP_Error}
 */
function bb_template_notices() {

	// Bail if no notices or errors
	if ( !bb_has_errors() )
		return;

	// Define local variable(s)
	$errors = $messages = array();

	// Get barebones
	$bbp = barebones();

	// Loop through notices
	foreach ( $bb->errors->get_error_codes() as $code ) {

		// Get notice severity
		$severity = $bb->errors->get_error_data( $code );

		// Loop through notices and separate errors from messages
		foreach ( $bb->errors->get_error_messages( $code ) as $error ) {
			if ( 'message' == $severity ) {
				$messages[] = $error;
			} else {
				$errors[]   = $error;
			}
		}
	}

	// Display errors first...
	if ( !empty( $errors ) ) : ?>

		<div class="bb-template-notice error">
			<p>
				<?php echo implode( "</p>\n<p>", $errors ); ?>
			</p>
		</div>

	<?php endif;

	// ...and messages last
	if ( !empty( $messages ) ) : ?>

		<div class="bb-template-notice">
			<p>
				<?php echo implode( "</p>\n<p>", $messages ); ?>
			</p>
		</div>

	<?php endif;
}

/** Login/logout/register/lost pass *******************************************/

/**
 * Output the logout link
 *
 * @since Barebones (1.0)
 *
 * @param string $redirect_to Redirect to url
 * @uses bb_get_logout_link() To get the logout link
 */
function bb_logout_link( $redirect_to = '' ) {
	echo bb_get_logout_link( $redirect_to );
}
	/**
	 * Return the logout link
	 *
	 * @since Barebones (1.0)
	 *
	 * @param string $redirect_to Redirect to url
	 * @uses wp_logout_url() To get the logout url
	 * @uses apply_filters() Calls 'bb_get_logout_link' with the logout link and
	 *                        redirect to url
	 * @return string The logout link
	 */
	function bb_get_logout_link( $redirect_to = '' ) {
		return apply_filters( 'bb_get_logout_link', '<a href="' . wp_logout_url( $redirect_to ) . '" class="button logout-link">' . __( 'Log Out', 'barebones' ) . '</a>', $redirect_to );
	}

/** Title *********************************************************************/

/**
 * Custom page title for barebones pages
 *
 * @since Barebones (1.0)
 *
 * @param string $title Optional. The title (not used).
 * @param string $sep Optional, default is '&raquo;'. How to separate the
 *                     various items within the page title.
 * @param string $seplocation Optional. Direction to display title, 'right'.
 * @uses bb_is_single_user() To check if it's a user profile page
 * @uses bb_is_single_user_edit() To check if it's a user profile edit page
 * @uses bb_is_user_home() To check if the profile page is of the current user
 * @uses get_query_var() To get the user id
 * @uses get_userdata() To get the user data
 * @uses bb_is_single_forum() To check if it's a forum
 * @uses bb_get_forum_title() To get the forum title
 * @uses bb_is_single_topic() To check if it's a topic
 * @uses bb_get_topic_title() To get the topic title
 * @uses bb_is_single_reply() To check if it's a reply
 * @uses bb_get_reply_title() To get the reply title
 * @uses is_tax() To check if it's the tag page
 * @uses get_queried_object() To get the queried object
 * @uses bb_is_single_view() To check if it's a view
 * @uses bb_get_view_title() To get the view title
 * @uses apply_filters() Calls 'bb_raw_title' with the title
 * @uses apply_filters() Calls 'bb_profile_page_wp_title' with the title,
 *                        separator and separator location
 * @return string The tite
 */
function bb_title( $title = '', $sep = '&raquo;', $seplocation = '' ) {

	// Store original title to compare
	$_title = $title;

	/** Archives **************************************************************/

	// Forum Archive
	if ( bb_is_forum_archive() ) {
		$title = bb_get_forum_archive_title();

	// Topic Archive
	} elseif ( bb_is_topic_archive() ) {
		$title = bb_get_topic_archive_title();

	/** Singles ***************************************************************/

	// Forum page
	} elseif ( bb_is_single_forum() ) {
		$title = sprintf( __( 'Forum: %s', 'barebones' ), bb_get_forum_title() );

	// Topic page
	} elseif ( bb_is_single_topic() ) {
		$title = sprintf( __( 'Topic: %s', 'barebones' ), bb_get_topic_title() );

	// Replies
	} elseif ( bb_is_single_reply() ) {
		$title = bb_get_reply_title();

	// Topic tag page (or edit)
	} elseif ( bb_is_topic_tag() || bb_is_topic_tag_edit() || get_query_var( 'bb_topic_tag' ) ) {
		$term  = get_queried_object();
		$title = sprintf( __( 'Topic Tag: %s', 'barebones' ), $term->name );

	/** Users *****************************************************************/

	// Profile page
	} elseif ( bb_is_single_user() ) {

		// Current users profile
		if ( bb_is_user_home() ) {
			$title = __( 'Your Profile', 'barebones' );

		// Other users profile
		} else {
			$userdata = get_userdata( bb_get_user_id() );
			$title    = sprintf( __( '%s\'s Profile', 'barebones' ), $userdata->display_name );
		}

	// Profile edit page
	} elseif ( bb_is_single_user_edit() ) {

		// Current users profile
		if ( bb_is_user_home_edit() ) {
			$title = __( 'Edit Your Profile', 'barebones' );

		// Other users profile
		} else {
			$userdata = get_userdata( bb_get_user_id() );
			$title    = sprintf( __( 'Edit %s\'s Profile', 'barebones' ), $userdata->display_name );
		}

	/** Views *****************************************************************/

	// Views
	} elseif ( bb_is_single_view() ) {
		$title = sprintf( __( 'View: %s', 'barebones' ), bb_get_view_title() );

	/** Search ****************************************************************/

	// Search
	} elseif ( bb_is_search() ) {
		$title = bb_get_search_title();
	}

	// Filter the raw title
	$title = apply_filters( 'bb_raw_title', $title, $sep, $seplocation );

	// Compare new title with original title
	if ( $title == $_title )
		return $title;

	// Temporary separator, for accurate flipping, if necessary
	$t_sep  = '%WP_TITILE_SEP%';
	$prefix = '';

	if ( !empty( $title ) )
		$prefix = " $sep ";

	// sep on right, so reverse the order
	if ( 'right' == $seplocation ) {
		$title_array = array_reverse( explode( $t_sep, $title ) );
		$title       = implode( " $sep ", $title_array ) . $prefix;

	// sep on left, do not reverse
	} else {
		$title_array = explode( $t_sep, $title );
		$title       = $prefix . implode( " $sep ", $title_array );
	}

	// Filter and return
	return apply_filters( 'bb_title', $title, $sep, $seplocation );
}
