<?php

/**
 * Barebones Template Loader
 *
 * @package Barebones
 * @subpackage TemplateLoader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Possibly intercept the template being loaded
 *
 * Listens to the 'template_include' filter and waits for any barebones specific
 * template condition to be met. If one is met and the template file exists,
 * it will be used; otherwise 
 *
 * Note that the _edit() checks are ahead of their counterparts, to prevent them
 * from being stomped on accident.
 *
 * @since Barebones (1.0)
 *
 * @param string $template
 *
 * @uses bb_is_single_user() To check if page is single user
 * @uses bb_get_single_user_template() To get user template
 * @uses bb_is_single_user_edit() To check if page is single user edit
 * @uses bb_get_single_user_edit_template() To get user edit template
 * @uses bb_is_single_view() To check if page is single view
 * @uses bb_get_single_view_template() To get view template
 * @uses bb_is_search() To check if page is search
 * @uses bb_get_search_template() To get search template
 * @uses bb_is_forum_edit() To check if page is forum edit
 * @uses bb_get_forum_edit_template() To get forum edit template
 * @uses bb_is_topic_merge() To check if page is topic merge
 * @uses bb_get_topic_merge_template() To get topic merge template
 * @uses bb_is_topic_split() To check if page is topic split
 * @uses bb_get_topic_split_template() To get topic split template
 * @uses bb_is_topic_edit() To check if page is topic edit
 * @uses bb_get_topic_edit_template() To get topic edit template
 * @uses bb_is_reply_move() To check if page is reply move
 * @uses bb_get_reply_move_template() To get reply move template
 * @uses bb_is_reply_edit() To check if page is reply edit
 * @uses bb_get_reply_edit_template() To get reply edit template
 * @uses bb_set_theme_compat_template() To set the global theme compat template
 *
 * @return string The path to the template file that is being used
 */
function bb_template_include_theme_supports( $template = '' ) {

	// Editing a user
	if     ( bb_is_single_user_edit() && ( $new_template = bb_get_single_user_edit_template() ) ) :

	// User favorites
	elseif ( bb_is_favorites()        && ( $new_template = bb_get_favorites_template()        ) ) :

	// User favorites
	elseif ( bb_is_subscriptions()    && ( $new_template = bb_get_subscriptions_template()    ) ) :

	// Viewing a user
	elseif ( bb_is_single_user()      && ( $new_template = bb_get_single_user_template()      ) ) :

	// Single View
	elseif ( bb_is_single_view()      && ( $new_template = bb_get_single_view_template()      ) ) :

	// Search
	elseif ( bb_is_search()           && ( $new_template = bb_get_search_template()           ) ) :

	// Forum edit
	elseif ( bb_is_forum_edit()       && ( $new_template = bb_get_forum_edit_template()       ) ) :

	// Single Forum
	elseif ( bb_is_single_forum()     && ( $new_template = bb_get_single_forum_template()     ) ) :

	// Forum Archive
	elseif ( bb_is_forum_archive()    && ( $new_template = bb_get_forum_archive_template()    ) ) :

	// Topic merge
	elseif ( bb_is_topic_merge()      && ( $new_template = bb_get_topic_merge_template()      ) ) :

	// Topic split
	elseif ( bb_is_topic_split()      && ( $new_template = bb_get_topic_split_template()      ) ) :

	// Topic edit
	elseif ( bb_is_topic_edit()       && ( $new_template = bb_get_topic_edit_template()       ) ) :

	// Single Topic
	elseif ( bb_is_single_topic()     && ( $new_template = bb_get_single_topic_template()     ) ) :

	// Topic Archive
	elseif ( bb_is_topic_archive()    && ( $new_template = bb_get_topic_archive_template()    ) ) :

	// Reply move
	elseif ( bb_is_reply_move()       && ( $new_template = bb_get_reply_move_template()       ) ) :

	// Editing a reply
	elseif ( bb_is_reply_edit()       && ( $new_template = bb_get_reply_edit_template()       ) ) :

	// Single Reply
	elseif ( bb_is_single_reply()     && ( $new_template = bb_get_single_reply_template()     ) ) :

	// Editing a topic tag
	elseif ( bb_is_topic_tag_edit()   && ( $new_template = bb_get_topic_tag_edit_template()   ) ) :

	// Viewing a topic tag
	elseif ( bb_is_topic_tag()        && ( $new_template = bb_get_topic_tag_template()        ) ) :
	endif;

	// barebones template file exists
	if ( !empty( $new_template ) ) {

		// Override the WordPress template with a barebones one
		$template = $new_template;

		// @see: bb_template_include_theme_compat()
		barebones()->theme_compat->barebones_template = true;
	}

	return apply_filters( 'bb_template_include_theme_supports', $template );
}

/** Custom Functions **********************************************************/

/**
 * Attempt to load a custom barebones functions file, similar to each themes
 * functions.php file.
 *
 * @since Barebones (1.0)
 *
 * @global string $pagenow
 * @uses bb_locate_template()
 */
function bb_load_theme_functions() {
	global $pagenow;

	// If barebones is being deactivated, do not load any more files
	if ( bb_is_deactivation() )
		return;

	if ( ! defined( 'WP_INSTALLING' ) || ( !empty( $pagenow ) && ( 'wp-activate.php' !== $pagenow ) ) ) {
		bb_locate_template( 'barebones-functions.php', true );
	}
}

/** Individual Templates ******************************************************/

/**
 * Get the user profile template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_displayed_user_id()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_single_user_template() {
	$nicename  = bb_get_displayed_user_field( 'user_nicename' );
	$user_id   = bb_get_displayed_user_id();
	$templates = array(
		'single-user-' . $nicename . '.php', // Single User nicename
		'single-user-' . $user_id  . '.php', // Single User ID
		'single-user.php',                   // Single User
		'user.php',                          // User
	);
	return bb_get_query_template( 'profile', $templates );
}

/**
 * Get the user profile edit template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_displayed_user_id()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_single_user_edit_template() {
	$nicename  = bb_get_displayed_user_field( 'user_nicename' );
	$user_id   = bb_get_displayed_user_id();
	$templates = array(
		'single-user-edit-' . $nicename . '.php', // Single User Edit nicename
		'single-user-edit-' . $user_id  . '.php', // Single User Edit ID
		'single-user-edit.php',                   // Single User Edit
		'user-edit.php',                          // User Edit
		'user.php',                               // User
	);
	return bb_get_query_template( 'profile_edit', $templates );
}

/**
 * Get the user favorites template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_displayed_user_id()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_favorites_template() {
	$nicename  = bb_get_displayed_user_field( 'user_nicename' );
	$user_id   = bb_get_displayed_user_id();
	$templates = array(
		'single-user-favorites-' . $nicename . '.php', // Single User Favs nicename
		'single-user-favorites-' . $user_id  . '.php', // Single User Favs ID
		'favorites-' . $nicename  . '.php',            // Favorites nicename
		'favorites-' . $user_id   . '.php',            // Favorites ID
		'favorites.php',                               // Favorites
		'user.php',                                    // User
	);
	return bb_get_query_template( 'favorites', $templates );
}

/**
 * Get the user subscriptions template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_displayed_user_id()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_subscriptions_template() {
	$nicename  = bb_get_displayed_user_field( 'user_nicename' );
	$user_id   = bb_get_displayed_user_id();
	$templates = array(
		'single-user-subscriptions-' . $nicename . '.php', // Single User Subs nicename
		'single-user-subscriptions-' . $user_id  . '.php', // Single User Subs ID
		'subscriptions-' . $nicename  . '.php',            // Subscriptions nicename
		'subscriptions-' . $user_id   . '.php',            // Subscriptions ID
		'subscriptions.php',                               // Subscriptions
		'user.php',                                        // User
	);
	return bb_get_query_template( 'subscriptions', $templates );
}

/**
 * Get the view template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_view_id()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_single_view_template() {
	$view_id   = bb_get_view_id();
	$templates = array(
		'single-view-' . $view_id . '.php', // Single View ID
		'view-'        . $view_id . '.php', // View ID
		'single-view.php',                  // Single View
		'view.php',                         // View
	);
	return bb_get_query_template( 'single_view', $templates );
}

/**
 * Get the search template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_search_template() {
	$templates = array(
		'page-forum-search.php', // Single Search
		'forum-search.php',      // Search
	);
	return bb_get_query_template( 'single_search', $templates );
}

/**
 * Get the single forum template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_forum_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_single_forum_template() {
	$templates = array(
		'single-' . bb_get_forum_post_type() . '.php' // Single Forum
	);
	return bb_get_query_template( 'single_forum', $templates );
}

/**
 * Get the forum archive template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_forum_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_forum_archive_template() {
	$templates = array(
		'archive-' . bb_get_forum_post_type() . '.php' // Forum Archive
	);
	return bb_get_query_template( 'forum_archive', $templates );
}

/**
 * Get the forum edit template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_forum_edit_template() {
	$templates = array(
		'single-' . bb_get_forum_post_type() . '-edit.php' // Single Forum Edit
	);
	return bb_get_query_template( 'forum_edit', $templates );
}

/**
 * Get the single topic template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_single_topic_template() {
	$templates = array(
		'single-' . bb_get_topic_post_type() . '.php'
	);
	return bb_get_query_template( 'single_topic', $templates );
}

/**
 * Get the topic archive template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_topic_archive_template() {
	$templates = array(
		'archive-' . bb_get_topic_post_type() . '.php' // Topic Archive
	);
	return bb_get_query_template( 'topic_archive', $templates );
}

/**
 * Get the topic edit template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_topic_edit_template() {
	$templates = array(
		'single-' . bb_get_topic_post_type() . '-edit.php' // Single Topic Edit
	);
	return bb_get_query_template( 'topic_edit', $templates );
}

/**
 * Get the topic split template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_topic_split_template() {
	$templates = array(
		'single-' . bb_get_topic_post_type() . '-split.php', // Topic Split
	);
	return bb_get_query_template( 'topic_split', $templates );
}

/**
 * Get the topic merge template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_topic_merge_template() {
	$templates = array(
		'single-' . bb_get_topic_post_type() . '-merge.php', // Topic Merge
	);
	return bb_get_query_template( 'topic_merge', $templates );
}

/**
 * Get the single reply template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_reply_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_single_reply_template() {
	$templates = array(
		'single-' . bb_get_reply_post_type() . '.php'
	);
	return bb_get_query_template( 'single_reply', $templates );
}

/**
 * Get the reply edit template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_reply_post_type()
 * @uses bb_get_query_template()
* @return string Path to template file
 */
function bb_get_reply_edit_template() {
	$templates = array(
		'single-' . bb_get_reply_post_type() . '-edit.php' // Single Reply Edit
	);
	return bb_get_query_template( 'reply_edit', $templates );
}

/**
 * Get the reply move template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_reply_post_type()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_reply_move_template() {
	$templates = array(
		'single-' . bb_get_reply_post_type() . '-move.php', // Reply move
	);
	return bb_get_query_template( 'reply_move', $templates );
}

/**
 * Get the topic template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_tag_tax_id()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_topic_tag_template() {
	$tt_slug   = bb_get_topic_tag_slug();
	$tt_id     = bb_get_topic_tag_tax_id();
	$templates = array(
		'taxonomy-' . $tt_slug . '.php', // Single Topic Tag slug
		'taxonomy-' . $tt_id   . '.php', // Single Topic Tag ID
	);
	return bb_get_query_template( 'topic_tag', $templates );
}

/**
 * Get the topic edit template
 *
 * @since Barebones (1.0)
 *
 * @uses bb_get_topic_tag_tax_id()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_topic_tag_edit_template() {
	$tt_slug   = bb_get_topic_tag_slug();
	$tt_id     = bb_get_topic_tag_tax_id();
	$templates = array(
		'taxonomy-' . $tt_slug . '-edit.php', // Single Topic Tag Edit slug
		'taxonomy-' . $tt_id   . '-edit.php'  // Single Topic Tag Edit ID
	);
	return bb_get_query_template( 'topic_tag_edit', $templates );
}

/**
 * Get the templates to use as the endpoint for barebones template parts
 *
 * @since Barebones (1.0)
 *
 * @uses apply_filters()
 * @uses bb_set_theme_compat_templates()
 * @uses bb_get_query_template()
 * @return string Path to template file
 */
function bb_get_theme_compat_templates() {
	$templates = array(
		'plugin-barebones.php',
		'barebones.php',
		'forums.php',
		'forum.php',
		'generic.php',
		'page.php',
		'single.php',
		'index.php'
	);
	return bb_get_query_template( 'barebones', $templates );
}
