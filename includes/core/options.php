<?php

/**
 * bbPress Options
 *
 * @package bbPress
 * @subpackage Options
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the default site options and their values.
 * 
 * These option
 *
 * @since barebones (1.0)
 * @return array Filtered option names and values
 */
function bb_get_default_options() {

	// Default options
	return apply_filters( 'bb_get_default_options', array(

		/** DB Version ********************************************************/

		'_bb_db_version'           => bbpress()->db_version,

		/** Settings **********************************************************/

		'_bb_edit_lock'            => 5,                          // Lock post editing after 5 minutes
		'_bb_throttle_time'        => 10,                         // Throttle post time to 10 seconds
		'_bb_enable_favorites'     => 1,                          // Favorites
		'_bb_enable_subscriptions' => 1,                          // Subscriptions
		'_bb_allow_topic_tags'     => 1,                          // Topic Tags
		'_bb_allow_anonymous'      => 0,                          // Allow anonymous posting
		'_bb_allow_global_access'  => 1,                          // Users from all sites can post
		'_bb_use_wp_editor'        => 1,                          // Use the WordPress editor if available
		'_bb_use_autoembed'        => 0,                          // Allow oEmbed in topics and replies
		'_bb_theme_package_id'     => 'default',                  // The ID for the current theme package.
		'_bb_default_role'         => bb_get_participant_role(), // Default forums role

		/** Per Page **********************************************************/

		'_bb_topics_per_page'      => 15,          // Topics per page
		'_bb_replies_per_page'     => 15,          // Replies per page
		'_bb_forums_per_page'      => 50,          // Forums per page
		'_bb_topics_per_rss_page'  => 25,          // Topics per RSS page
		'_bb_replies_per_rss_page' => 25,          // Replies per RSS page

		/** Page For **********************************************************/

		'_bb_page_for_forums'      => 0,           // Page for forums
		'_bb_page_for_topics'      => 0,           // Page for forums
		'_bb_page_for_login'       => 0,           // Page for login
		'_bb_page_for_register'    => 0,           // Page for register
		'_bb_page_for_lost_pass'   => 0,           // Page for lost-pass

		/** Archive Slugs *****************************************************/

		'_bb_root_slug'            => 'forums',    // Forum archive slug
		'_bb_topic_archive_slug'   => 'topics',    // Topic archive slug

		/** Single Slugs ******************************************************/

		'_bb_include_root'         => 1,           // Include forum-archive before single slugs
		'_bb_forum_slug'           => 'forum',     // Forum slug
		'_bb_topic_slug'           => 'topic',     // Topic slug
		'_bb_reply_slug'           => 'reply',     // Reply slug
		'_bb_topic_tag_slug'       => 'topic-tag', // Topic tag slug

		/** User Slugs ********************************************************/

		'_bb_user_slug'            => 'users',         // User profile slug
		'_bb_user_favs_slug'       => 'favorites',     // User favorites slug
		'_bb_user_subs_slug'       => 'subscriptions', // User subscriptions slug

		/** Other Slugs *******************************************************/

		'_bb_view_slug'            => 'view',      // View slug
		'_bb_search_slug'          => 'search',    // Search slug

		/** Topics ************************************************************/

		'_bb_title_max_length'     => 80,          // Title Max Length
		'_bb_super_sticky_topics'  => '',          // Super stickies

		/** Forums ************************************************************/

		'_bb_private_forums'       => '',          // Private forums
		'_bb_hidden_forums'        => '',          // Hidden forums

		/** BuddyPress ********************************************************/

		'_bb_enable_group_forums'  => 1,           // Enable BuddyPress Group Extension
		'_bb_group_forums_root_id' => 0,           // Group Forums parent forum id

		/** Akismet ***********************************************************/

		'_bb_enable_akismet'       => 1            // Users from all sites can post

	) );
}

/**
 * Add default options
 *
 * Hooked to bb_activate, it is only called once when bbPress is activated.
 * This is non-destructive, so existing settings will not be overridden.
 *
 * @since barebones (1.0)
 * @uses bb_get_default_options() To get default options
 * @uses add_option() Adds default options
 * @uses do_action() Calls 'bb_add_options'
 */
function bb_add_options() {

	// Add default options
	foreach ( bb_get_default_options() as $key => $value )
		add_option( $key, $value );

	// Allow previously activated plugins to append their own options.
	do_action( 'bb_add_options' );
}

/**
 * Delete default options
 *
 * Hooked to bb_uninstall, it is only called once when bbPress is uninstalled.
 * This is destructive, so existing settings will be destroyed.
 *
 * @since barebones (1.0)
 * @uses bb_get_default_options() To get default options
 * @uses delete_option() Removes default options
 * @uses do_action() Calls 'bb_delete_options'
 */
function bb_delete_options() {

	// Add default options
	foreach ( array_keys( bb_get_default_options() ) as $key )
		delete_option( $key );

	// Allow previously activated plugins to append their own options.
	do_action( 'bb_delete_options' );
}

/**
 * Add filters to each bbPress option and allow them to be overloaded from
 * inside the $bbp->options array.
 *
 * @since barebones (1.0)
 * @uses bb_get_default_options() To get default options
 * @uses add_filter() To add filters to 'pre_option_{$key}'
 * @uses do_action() Calls 'bb_add_option_filters'
 */
function bb_setup_option_filters() {

	// Add filters to each bbPress option
	foreach ( array_keys( bb_get_default_options() ) as $key )
		add_filter( 'pre_option_' . $key, 'bb_pre_get_option' );

	// Allow previously activated plugins to append their own options.
	do_action( 'bb_setup_option_filters' );
}

/**
 * Filter default options and allow them to be overloaded from inside the
 * $bbp->options array.
 *
 * @since barebones (1.0)
 * @param bool $value Optional. Default value false
 * @return mixed false if not overloaded, mixed if set
 */
function bb_pre_get_option( $value = '' ) {

	// Remove the filter prefix
	$option = str_replace( 'pre_option_', '', current_filter() );

	// Check the options global for preset value
	if ( isset( bbpress()->options[$option] ) )
		$value = bbpress()->options[$option];

	// Always return a value, even if false
	return $value;
}

/** Active? *******************************************************************/

/**
 * Checks if favorites feature is enabled.
 *
 * @since barebones (1.0)
 * @param $default bool Optional.Default value true
 * @uses get_option() To get the favorites option
 * @return bool Is favorites enabled or not
 */
function bb_is_favorites_active( $default = 1 ) {
	return (bool) apply_filters( 'bb_is_favorites_active', (bool) get_option( '_bb_enable_favorites', $default ) );
}

/**
 * Checks if subscription feature is enabled.
 *
 * @since barebones (1.0)
 * @param $default bool Optional.Default value true
 * @uses get_option() To get the subscriptions option
 * @return bool Is subscription enabled or not
 */
function bb_is_subscriptions_active( $default = 1 ) {
	return (bool) apply_filters( 'bb_is_subscriptions_active', (bool) get_option( '_bb_enable_subscriptions', $default ) );
}

/**
 * Are topic tags allowed
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the allow tags
 * @return bool Are tags allowed?
 */
function bb_allow_topic_tags( $default = 1 ) {
	return (bool) apply_filters( 'bb_allow_topic_tags', (bool) get_option( '_bb_allow_topic_tags', $default ) );
}

/**
 * Are topic and reply revisions allowed
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the allow revisions
 * @return bool Are revisions allowed?
 */
function bb_allow_revisions( $default = 1 ) {
	return (bool) apply_filters( 'bb_allow_revisions', (bool) get_option( '_bb_allow_revisions', $default ) );
}

/**
 * Is the anonymous posting allowed?
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value
 * @uses get_option() To get the allow anonymous option
 * @return bool Is anonymous posting allowed?
 */
function bb_allow_anonymous( $default = 0 ) {
	return apply_filters( 'bb_allow_anonymous', (bool) get_option( '_bb_allow_anonymous', $default ) );
}

/**
 * Is this forum available to all users on all sites in this installation?
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value false
 * @uses get_option() To get the global access option
 * @return bool Is global access allowed?
 */
function bb_allow_global_access( $default = 1 ) {
	return (bool) apply_filters( 'bb_allow_global_access', (bool) get_option( '_bb_allow_global_access', $default ) );
}

/**
 * Is this forum available to all users on all sites in this installation?
 *
 * @since barebones (1.0)
 * @param $default string Optional. Default value empty
 * @uses get_option() To get the default forums role option
 * @return string The default forums user role
 */
function bb_get_default_role( $default = 'bb_participant' ) {
	return apply_filters( 'bb_get_default_role', get_option( '_bb_default_role', $default ) );
}

/**
 * Use the WordPress editor if available
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the WP editor option
 * @return bool Use WP editor?
 */
function bb_use_wp_editor( $default = 1 ) {
	return (bool) apply_filters( 'bb_use_wp_editor', (bool) get_option( '_bb_use_wp_editor', $default ) );
}

/**
 * Use WordPress's oEmbed API
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the oEmbed option
 * @return bool Use oEmbed?
 */
function bb_use_autoembed( $default = 1 ) {
	return (bool) apply_filters( 'bb_use_autoembed', (bool) get_option( '_bb_use_autoembed', $default ) );
}

/**
 * Get the current theme package ID
 *
 * @since barebones (1.0)
 * @param $default string Optional. Default value 'default'
 * @uses get_option() To get the subtheme option
 * @return string ID of the subtheme
 */
function bb_get_theme_package_id( $default = 'default' ) {
	return apply_filters( 'bb_get_theme_package_id', get_option( '_bb_theme_package_id', $default ) );
}

/**
 * Output the maximum length of a title
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value 80
 */
function bb_title_max_length( $default = 80 ) {
	echo bb_get_title_max_length( $default );
}
	/**
	 * Return the maximum length of a title
	 *
	 * @since barebones (1.0)
	 * @param $default bool Optional. Default value 80
	 * @uses get_option() To get the maximum title length
	 * @return int Is anonymous posting allowed?
	 */
	function bb_get_title_max_length( $default = 80 ) {
		return (int) apply_filters( 'bb_get_title_max_length', (int) get_option( '_bb_title_max_length', $default ) );
	}

/**
 * Output the grop forums root parent forum id
 *
 * @since barebones (1.0)
 * @param $default int Optional. Default value
 */
function bb_group_forums_root_id( $default = 0 ) {
	echo bb_get_group_forums_root_id( $default );
}
	/**
	 * Return the grop forums root parent forum id
	 *
	 * @since barebones (1.0)
	 * @param $default bool Optional. Default value 0
	 * @uses get_option() To get the root group forum ID
	 * @return int The post ID for the root forum
	 */
	function bb_get_group_forums_root_id( $default = 0 ) {
		return (int) apply_filters( 'bb_get_group_forums_root_id', (int) get_option( '_bb_group_forums_root_id', $default ) );
	}

/**
 * Checks if BuddyPress Group Forums are enabled
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the group forums option
 * @return bool Is group forums enabled or not
 */
function bb_is_group_forums_active( $default = 1 ) {
	return (bool) apply_filters( 'bb_is_group_forums_active', (bool) get_option( '_bb_enable_group_forums', $default ) );
}

/**
 * Checks if Akismet is enabled
 *
 * @since barebones (1.0)
 * @param $default bool Optional. Default value true
 * @uses get_option() To get the Akismet option
 * @return bool Is Akismet enabled or not
 */
function bb_is_akismet_active( $default = 1 ) {
	return (bool) apply_filters( 'bb_is_akismet_active', (bool) get_option( '_bb_enable_akismet', $default ) );
}

/** Slugs *********************************************************************/

/**
 * Return the root slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_root_slug( $default = 'forums' ) {
	return apply_filters( 'bb_get_root_slug', get_option( '_bb_root_slug', $default ) );
}

/**
 * Are we including the root slug in front of forum pages?
 *
 * @since barebones (1.0)
 * @return bool
 */
function bb_include_root_slug( $default = 1 ) {
	return (bool) apply_filters( 'bb_include_root_slug', (bool) get_option( '_bb_include_root', $default ) );
}

/**
 * Maybe return the root slug, based on whether or not it's included in the url
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_maybe_get_root_slug() {
	$retval = '';

	if ( bb_get_root_slug() && bb_include_root_slug() )
		$retval = trailingslashit( bb_get_root_slug() );

	return apply_filters( 'bb_maybe_get_root_slug', $retval );
}

/**
 * Return the single forum slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_forum_slug( $default = 'forum' ) {;
	return apply_filters( 'bb_get_root_slug', bb_maybe_get_root_slug() . get_option( '_bb_forum_slug', $default ) );
}

/**
 * Return the topic archive slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_topic_archive_slug( $default = 'topics' ) {
	return apply_filters( 'bb_get_topic_archive_slug', get_option( '_bb_topic_archive_slug', $default ) );
}

/**
 * Return the single topic slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_topic_slug( $default = 'topic' ) {
	return apply_filters( 'bb_get_topic_slug', bb_maybe_get_root_slug() . get_option( '_bb_topic_slug', $default ) );
}

/**
 * Return the topic-tag taxonomy slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_topic_tag_tax_slug( $default = 'topic-tag' ) {
	return apply_filters( 'bb_get_topic_tag_tax_slug', bb_maybe_get_root_slug() . get_option( '_bb_topic_tag_slug', $default ) );
}

/**
 * Return the single reply slug (used mostly for editing)
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_reply_slug( $default = 'reply' ) {
	return apply_filters( 'bb_get_reply_slug', bb_maybe_get_root_slug() . get_option( '_bb_reply_slug', $default ) );
}

/**
 * Return the single user slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_user_slug( $default = 'user' ) {
	return apply_filters( 'bb_get_user_slug', bb_maybe_get_root_slug() . get_option( '_bb_user_slug', $default ) );
}

/**
 * Return the single user favorites slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_user_favorites_slug( $default = 'favorites' ) {
	return apply_filters( 'bb_get_user_favorites_slug', get_option( '_bb_user_favs_slug', $default ) );
}

/**
 * Return the single user subscriptions slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_user_subscriptions_slug( $default = 'subscriptions' ) {
	return apply_filters( 'bb_get_user_subscriptions_slug', get_option( '_bb_user_subs_slug', $default ) );
}

/**
 * Return the topic view slug
 *
 * @since barebones (1.0)
 * @return string
 */
function bb_get_view_slug( $default = 'view' ) {
	return apply_filters( 'bb_get_view_slug', bb_maybe_get_root_slug() . get_option( '_bb_view_slug', $default ) );
}

/**
 * Return the search slug
 *
 * @since barebones (1.0)
 *
 * @return string
 */
function bb_get_search_slug( $default = 'search' ) {
	return apply_filters( 'bb_get_search_slug', bb_maybe_get_root_slug() . get_option( '_bb_search_slug', $default ) );
}

/** Legacy ********************************************************************/

/**
 * Checks if there is a previous BuddyPress Forum configuration
 *
 * @since barebones (1.0)
 * @param $default string Optional. Default empty string
 * @uses get_option() To get the old bb-config.php location
 * @return string The location of the bb-config.php file, if any
 */
function bb_get_config_location( $default = '' ) {
	return apply_filters( 'bb_get_config_location', get_option( 'bb-config-location', $default ) );
}
