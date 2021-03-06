<?php

/**
 * Barebones Template Functions
 *
 * This file contains functions necessary to mirror the WordPress core template
 * loading process. Many of those functions are not filterable, and even then
 * would not be robust enough to predict where barebones templates might exist.
 *
 * @package Barebones
 * @subpackage TemplateFunctions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Adds barebones theme support to any active WordPress theme
 *
 * @since Barebones (1.0)
 *
 * @param string $slug
 * @param string $name Optional. Default null
 * @uses bb_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 */
function bb_get_template_part( $slug, $name = null ) {

	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';

	// Allow template parst to be filtered
	$templates = apply_filters( 'bb_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return bb_locate_template( $templates, true, false );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the child theme before parent theme so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * @since Barebones (1.0)
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 *                            Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function bb_locate_template( $template_names, $load = false, $require_once = true ) {

	// No file found yet
	$located            = false;
	$template_locations = bb_get_template_stack();

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name  = ltrim( $template_name, '/' );

		// Loop through template stack
		foreach ( (array) $template_locations as $template_location ) {

			// Continue if $template_location is empty
			if ( empty( $template_location ) ) {
				continue;
			}

			// Check child theme first
			if ( file_exists( trailingslashit( $template_location ) . $template_name ) ) {
				$located = trailingslashit( $template_location ) . $template_name;
				break 2;
			}
		}
	}

	/**
	 * This action exists only to follow the standard barebones coding convention,
	 * and should not be used to short-circuit any part of the template locator.
	 *
	 * If you want to override a specific template part, please either filter
	 * 'bb_get_template_part' or add a new location to the template stack.
	 */
	do_action( 'bb_locate_template', $located, $template_name, $template_names, $template_locations, $load, $require_once );

	// Maybe load the template if one was located
	if ( ( true == $load ) && !empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * This is really cool. This function registers a new template stack location,
 * using WordPress's built in filters API.
 *
 * This allows for templates to live in places beyond just the parent/child
 * relationship, to allow for custom template locations. Used in conjunction
 * with bb_locate_template(), this allows for easy template overrides.
 *
 * @since Barebones (1.0)
 *
 * @param string $location Callback function that returns the 
 * @param int $priority
 */
function bb_register_template_stack( $location_callback = '', $priority = 10 ) {

	// Bail if no location, or function does not exist
	if ( empty( $location_callback ) || ! function_exists( $location_callback ) )
		return false;

	// Add location callback to template stack
	return add_filter( 'bb_template_stack', $location_callback, (int) $priority );
}

/**
 * Deregisters a previously registered template stack location.
 *
 * @since Barebones (1.0)
 *
 * @param string $location Callback function that returns the
 * @param int $priority
 * @see bb_register_template_stack()
 */
function bb_deregister_template_stack( $location_callback = '', $priority = 10 ) {

	// Bail if no location, or function does not exist
	if ( empty( $location_callback ) || ! function_exists( $location_callback ) )
		return false;

	// Remove location callback to template stack
	return remove_filter( 'bb_template_stack', $location_callback, (int) $priority );
}

/**
 * Call the functions added to the 'bb_template_stack' filter hook, and return
 * an array of the template locations.
 *
 * @see bb_register_template_stack()
 *
 * @since Barebones (1.0)
 *
 * @global array $wp_filter Stores all of the filters
 * @global array $merged_filters Merges the filter hooks using this function.
 * @global array $wp_current_filter stores the list of current filters with the current one last
 *
 * @return array The filtered value after all hooked functions are applied to it.
 */
function bb_get_template_stack() {
	global $wp_filter, $merged_filters, $wp_current_filter;

	// Setup some default variables
	$tag  = 'bb_template_stack';
	$args = $stack = array();

	// Add 'bb_template_stack' to the current filter array
	$wp_current_filter[] = $tag;

	// Sort
	if ( ! isset( $merged_filters[ $tag ] ) ) {
		ksort( $wp_filter[$tag] );
		$merged_filters[ $tag ] = true;
	}

	// Ensure we're always at the beginning of the filter array
	reset( $wp_filter[ $tag ] );

	// Loop through 'bb_template_stack' filters, and call callback functions
	do {
		foreach( (array) current( $wp_filter[$tag] ) as $the_ ) {
			if ( ! is_null( $the_['function'] ) ) {
				$args[1] = $stack;
				$stack[] = call_user_func_array( $the_['function'], array_slice( $args, 1, (int) $the_['accepted_args'] ) );
			}
		}
	} while ( next( $wp_filter[$tag] ) !== false );

	// Remove 'bb_template_stack' from the current filter array
	array_pop( $wp_current_filter );

	// Remove empties and duplicates
	$stack = array_unique( array_filter( $stack ) );

	return (array) apply_filters( 'bb_get_template_stack', $stack ) ;
}

/**
 * Retrieve path to a template
 *
 * Used to quickly retrieve the path of a template without including the file
 * extension. It will also check the parent theme and theme-compat theme with
 * the use of {@link bb_locate_template()}. Allows for more generic template
 * locations without the use of the other get_*_template() functions.
 *
 * @since Barebones (1.0)
 *
 * @param string $type Filename without extension.
 * @param array $templates An optional list of template candidates
 * @uses bb_set_theme_compat_templates()
 * @uses bb_locate_template()
 * @uses bb_set_theme_compat_template()
 * @return string Full path to file.
 */
function bb_get_query_template( $type, $templates = array() ) {
	$type = preg_replace( '|[^a-z0-9-]+|', '', $type );

	if ( empty( $templates ) )
		$templates = array( "{$type}.php" );

	// Filter possible templates, try to match one, and set any barebones theme
	// compat properties so they can be cross-checked later.
	$templates = apply_filters( "bb_get_{$type}_template", $templates );
	$templates = bb_set_theme_compat_templates( $templates );
	$template  = bb_locate_template( $templates );
	$template  = bb_set_theme_compat_template( $template );

	return apply_filters( "bb_{$type}_template", $template );
}

/**
 * Get the possible subdirectories to check for templates in
 *
 * @since Barebones (1.0)
 * @param array $templates Templates we are looking for
 * @return array Possible subfolders to look in
 */
function bb_get_template_locations( $templates = array() ) {
	$locations = array(
		'barebones',
		'forums',
		''
	);
	return apply_filters( 'bb_get_template_locations', $locations, $templates );
}

/**
 * Add template locations to template files being searched for
 *
 * @since Barebones (1.0)
 *
 * @param array $templates
 * @return array() 
 */
function bb_add_template_stack_locations( $stacks = array() ) {
	$retval = array();

	// Get alternate locations
	$locations = bb_get_template_locations();

	// Loop through locations and stacks and combine
	foreach ( (array) $stacks as $stack )
		foreach ( (array) $locations as $custom_location )
			$retval[] = untrailingslashit( trailingslashit( $stack ) . $custom_location );

	return apply_filters( 'bb_add_template_stack_locations', array_unique( $retval ), $stacks );
}

/**
 * Add checks for barebones conditions to parse_query action
 *
 * If it's a user page, WP_Query::bb_is_single_user is set to true.
 * If it's a user edit page, WP_Query::bb_is_single_user_edit is set to true
 * and the the 'wp-admin/includes/user.php' file is included.
 * In addition, on user/user edit pages, WP_Query::home is set to false & query
 * vars 'bb_user_id' with the displayed user id and 'author_name' with the
 * displayed user's nicename are added.
 *
 * If it's a forum edit, WP_Query::bb_is_forum_edit is set to true
 * If it's a topic edit, WP_Query::bb_is_topic_edit is set to true
 * If it's a reply edit, WP_Query::bb_is_reply_edit is set to true.
 *
 * If it's a view page, WP_Query::bb_is_view is set to true
 * If it's a search page, WP_Query::bb_is_search is set to true
 *
 * @since Barebones (1.0)
 *
 * @param WP_Query $posts_query
 *
 * @uses get_query_var() To get {@link WP_Query} query var
 * @uses is_email() To check if the string is an email
 * @uses get_user_by() To try to get the user by email and nicename
 * @uses get_userdata() to get the user data
 * @uses current_user_can() To check if the current user can edit the user
 * @uses is_user_member_of_blog() To check if user profile page exists
 * @uses WP_Query::set_404() To set a 404 status
 * @uses apply_filters() Calls 'enable_edit_any_user_configuration' with true
 * @uses bb_get_view_query_args() To get the view query args
 * @uses bb_get_forum_post_type() To get the forum post type
 * @uses bb_get_topic_post_type() To get the topic post type
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses remove_action() To remove the auto save post revision action
 */
function bb_parse_query( $posts_query ) {

	// Bail if $posts_query is not the main loop
	if ( ! $posts_query->is_main_query() )
		return;

	// Bail if filters are suppressed on this query
	if ( true == $posts_query->get( 'suppress_filters' ) )
		return;

	// Bail if in admin
	if ( is_admin() )
		return;

	// Get query variables
	$bb_view = $posts_query->get( bb_get_view_rewrite_id() );
	$bb_user = $posts_query->get( bb_get_user_rewrite_id() );
	$is_edit  = $posts_query->get( bb_get_edit_rewrite_id() );

	// It is a user page - We'll also check if it is user edit
	if ( !empty( $bb_user ) ) {

		// Not a user_id so try email and slug
		if ( get_option( 'permalink_structure' ) || ! is_numeric( $bb_user ) ) {

			// Email was passed
			if ( is_email( $bb_user ) ) {
				$bb_user = get_user_by( 'email', $bb_user );

			// Try nicename
			} else {
				$bb_user = get_user_by( 'slug', $bb_user );
			}

			// If we were successful, set to ID
			if ( is_object( $bb_user ) ) {
				$bb_user = $bb_user->ID;
			}
		}

		// Cast as int, just in case
		$bb_user = (int) $bb_user;

		// 404 and bail if user does not have a profile
		if ( ! bb_user_has_profile( $bb_user ) ) {
			$posts_query->set_404();
			return;
		}

		/** User Exists *******************************************************/

		$is_favs    = $posts_query->get( bb_get_user_favorites_rewrite_id()     );
		$is_subs    = $posts_query->get( bb_get_user_subscriptions_rewrite_id() );
		$is_topics  = $posts_query->get( bb_get_user_topics_rewrite_id()        );
		$is_replies = $posts_query->get( bb_get_user_replies_rewrite_id()       );

		// View or edit?
		if ( !empty( $is_edit ) ) {

			// We are editing a profile
			$posts_query->bb_is_single_user_edit = true;

			// Load the core WordPress contact methods
			if ( !function_exists( '_wp_get_user_contactmethods' ) ) {
				include_once( ABSPATH . 'wp-includes/registration.php' );
			}

			// Load the edit_user functions
			if ( !function_exists( 'edit_user' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/user.php' );
			}

			// Load the grant/revoke super admin functions
			if ( is_multisite() && !function_exists( 'revoke_super_admin' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/ms.php' );
			}

			// Editing a user
			$posts_query->bb_is_edit = true;

		// User favorites
		} elseif ( ! empty( $is_favs ) ) {
			$posts_query->bb_is_single_user_favs = true;

		// User subscriptions
		} elseif ( ! empty( $is_subs ) ) {
			$posts_query->bb_is_single_user_subs = true;

		// User topics
		} elseif ( ! empty( $is_topics ) ) {
			$posts_query->bb_is_single_user_topics = true;

		// User topics
		} elseif ( ! empty( $is_replies ) ) {
			$posts_query->bb_is_single_user_replies = true;

		// User profile
		} else {
			$posts_query->bb_is_single_user_profile = true;
		}

		// Looking at a single user
		$posts_query->bb_is_single_user = true;

		// Make sure 404 is not set
		$posts_query->is_404  = false;

		// Correct is_home variable
		$posts_query->is_home = false;

		// Get the user data
		$user = get_userdata( $bb_user );

		// User is looking at their own profile
		if ( get_current_user_id() == $user->ID ) {
			$posts_query->bb_is_single_user_home = true;
		}

		// Set bb_user_id for future reference
		$posts_query->set( 'bb_user_id', $user->ID );

		// Set author_name as current user's nicename to get correct posts
		$posts_query->set( 'author_name', $user->user_nicename );

		// Set the displayed user global to this user
		barebones()->displayed_user = $user;

	// View Page
	} elseif ( !empty( $bb_view ) ) {

		// Check if the view exists by checking if there are query args are set
		$view_args = bb_get_view_query_args( $bb_view );

		// Bail if view args is false (view isn't registered)
		if ( false === $view_args ) {
			$posts_query->set_404();
			return;
		}

		// Correct is_home variable
		$posts_query->is_home     = false;

		// We are in a custom topic view
		$posts_query->bb_is_view = true;

	// Search Page
	} elseif ( isset( $posts_query->query_vars[ bb_get_search_rewrite_id() ] ) ) {

		// Check if there are search query args set
		$search_terms = bb_get_search_terms();
		if ( !empty( $search_terms ) )
			$posts_query->bb_search_terms = $search_terms;

		// Correct is_home variable
		$posts_query->is_home = false;

		// We are in a search query
		$posts_query->bb_is_search = true;

	// Forum/Topic/Reply Edit Page
	} elseif ( !empty( $is_edit ) ) {

		// Get the post type from the main query loop
		$post_type = $posts_query->get( 'post_type' );
		
		// Check which post_type we are editing, if any
		if ( !empty( $post_type ) ) {
			switch( $post_type ) {

				// We are editing a forum
				case bb_get_forum_post_type() :
					$posts_query->bb_is_forum_edit = true;
					$posts_query->bb_is_edit       = true;
					break;

				// We are editing a topic
				case bb_get_topic_post_type() :
					$posts_query->bb_is_topic_edit = true;
					$posts_query->bb_is_edit       = true;
					break;

				// We are editing a reply
				case bb_get_reply_post_type() :
					$posts_query->bb_is_reply_edit = true;
					$posts_query->bb_is_edit       = true;
					break;
			}

		// We are editing a topic tag
		} elseif ( bb_is_topic_tag() ) {
			$posts_query->bb_is_topic_tag_edit = true;
			$posts_query->bb_is_edit           = true;
		}

		// We save post revisions on our own
		remove_action( 'pre_post_update', 'wp_save_post_revision' );

	// Topic tag page
	} elseif ( bb_is_topic_tag() ) {
		$posts_query->set( 'bb_topic_tag',  get_query_var( 'term' )   );
		$posts_query->set( 'post_type',      bb_get_topic_post_type() );
		$posts_query->set( 'posts_per_page', bb_get_topics_per_page() );
	}
}
