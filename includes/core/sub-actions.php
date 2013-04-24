<?php

/**
 * Plugin Dependency
 *
 * The purpose of the following hooks is to mimic the behavior of something
 * called 'plugin dependency' which enables a plugin to have plugins of their
 * own in a safe and reliable way.
 *
 * We do this in bbPress by mirroring existing WordPress hookss in many places
 * allowing dependant plugins to hook into the bbPress specific ones, thus
 * guaranteeing proper code execution only when bbPress is active.
 *
 * The following functions are wrappers for hookss, allowing them to be
 * manually called and/or piggy-backed on top of other hooks if needed.
 *
 * @todo use anonymous functions when PHP minimun requirement allows (5.3)
 */

/** Activation Actions ********************************************************/

/**
 * Runs on bbPress activation
 *
 * @since barebones (1.0)
 * @uses register_uninstall_hook() To register our own uninstall hook
 * @uses do_action() Calls 'bb_activation' hook
 */
function bb_activation() {
	do_action( 'bb_activation' );
}

/**
 * Runs on bbPress deactivation
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_deactivation' hook
 */
function bb_deactivation() {
	do_action( 'bb_deactivation' );
}

/**
 * Runs when uninstalling bbPress
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_uninstall' hook
 */
function bb_uninstall() {
	do_action( 'bb_uninstall' );
}

/** Main Actions **************************************************************/

/**
 * Main action responsible for constants, globals, and includes
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_loaded'
 */
function bb_loaded() {
	do_action( 'bb_loaded' );
}

/**
 * Setup constants
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_constants'
 */
function bb_constants() {
	do_action( 'bb_constants' );
}

/**
 * Setup globals BEFORE includes
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_boot_strap_globals'
 */
function bb_boot_strap_globals() {
	do_action( 'bb_boot_strap_globals' );
}

/**
 * Include files
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_includes'
 */
function bb_includes() {
	do_action( 'bb_includes' );
}

/**
 * Setup globals AFTER includes
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_setup_globals'
 */
function bb_setup_globals() {
	do_action( 'bb_setup_globals' );
}

/**
 * Register any objects before anything is initialized
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_register'
 */
function bb_register() {
	do_action( 'bb_register' );
}

/**
 * Initialize any code after everything has been loaded
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_init'
 */
function bb_init() {
	do_action( 'bb_init' );
}

/**
 * Initialize widgets
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_widgets_init'
 */
function bb_widgets_init() {
	do_action( 'bb_widgets_init' );
}

/**
 * Setup the currently logged-in user
 *
 * @since barebones (1.0)
 * @uses did_action() To make sure the user isn't loaded out of order
 * @uses do_action() Calls 'bb_setup_current_user'
 */
function bb_setup_current_user() {

	// If the current user is being setup before the "init" action has fired,
	// strange (and difficult to debug) role/capability issues will occur.
	if ( ! did_action( 'after_setup_theme' ) ) {
		_doing_it_wrong( __FUNCTION__, __( 'The current user is being initialized without using $wp->init().'' 'barebones' ), '2.3' );
	}

	do_action( 'bb_setup_current_user' );
}

/** Supplemental Actions ******************************************************/

/**
 * Load translations for current language
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_load_textdomain'
 */
function bb_load_textdomain() {
	do_action( 'bb_load_textdomain' );
}

/**
 * Setup the post types
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_register_post_type'
 */
function bb_register_post_types() {
	do_action( 'bb_register_post_types' );
}

/**
 * Setup the post statuses
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_register_post_statuses'
 */
function bb_register_post_statuses() {
	do_action( 'bb_register_post_statuses' );
}

/**
 * Register the built in bbPress taxonomies
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_register_taxonomies'
 */
function bb_register_taxonomies() {
	do_action( 'bb_register_taxonomies' );
}

/**
 * Register the default bbPress views
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_register_views'
 */
function bb_register_views() {
	do_action( 'bb_register_views' );
}

/**
 * Register the default bbPress shortcodes
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_register_shortcodes'
 */
function bb_register_shortcodes() {
	do_action( 'bb_register_shortcodes' );
}

/**
 * Enqueue bbPress specific CSS and JS
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_enqueue_scripts'
 */
function bb_enqueue_scripts() {
	do_action( 'bb_enqueue_scripts' );
}

/**
 * Add the bbPress-specific rewrite tags
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_add_rewrite_tags'
 */
function bb_add_rewrite_tags() {
	do_action( 'bb_add_rewrite_tags' );
}

/**
 * Add the bbPress-specific login forum action
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_login_form_login'
 */
function bb_login_form_login() {
	do_action( 'bb_login_form_login' );
}

/** User Actions **************************************************************/

/**
 * The main action for hooking into when a user account is updated
 *
 * @since barebones (1.0)
 *
 * @param int $user_id ID of user being edited
 * @param array $old_user_data The old, unmodified user data
 * @uses do_action() Calls 'bb_profile_update'
 */
function bb_profile_update( $user_id = 0, $old_user_data = array() ) {
	do_action( 'bb_profile_update', $user_id, $old_user_data );
}

/**
 * The main action for hooking into a user being registered
 *
 * @since barebones (1.0)
 * @param int $user_id ID of user being edited
 * @uses do_action() Calls 'bb_user_register'
 */
function bb_user_register( $user_id = 0 ) {
	do_action( 'bb_user_register', $user_id );
}

/** Final Action **************************************************************/

/**
 * barebones has loaded and initialized everything, and is okay to go
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_ready'
 */
function bb_ready() {
	do_action( 'bb_ready' );
}

/** Theme Permissions *********************************************************/

/**
 * The main action used for redirecting bbPress theme actions that are not
 * permitted by the current_user
 *
 * @since barebones (1.0)
 * @uses do_action()
 */
function bb_template_redirect() {
	do_action( 'bb_template_redirect' );
}

/** Theme Helpers *************************************************************/

/**
 * The main action used for executing code before the theme has been setup
 *
 * @since barebones (1.0)
 * @uses do_action()
 */
function bb_register_theme_packages() {
	do_action( 'bb_register_theme_packages' );
}

/**
 * The main action used for executing code before the theme has been setup
 *
 * @since barebones (1.0)
 * @uses do_action()
 */
function bb_setup_theme() {
	do_action( 'bb_setup_theme' );
}

/**
 * The main action used for executing code after the theme has been setup
 *
 * @since barebones (1.0)
 * @uses do_action()
 */
function bb_after_setup_theme() {
	do_action( 'bb_after_setup_theme' );
}

/**
 * The main action used for handling theme-side POST requests
 *
 * @since barebones (1.0)
 * @uses do_action()
 */
function bb_post_request() {

	// Bail if not a POST action
	if ( ! bb_is_post_request() )
		return;

	// Bail if no action
	if ( empty( $_POST['action'] ) )
		return;

	do_action( 'bb_post_request', $_POST['action'] );
}

/**
 * The main action used for handling theme-side GET requests
 *
 * @since barebones (1.0)
 * @uses do_action()
 */
function bb_get_request() {

	// Bail if not a POST action
	if ( ! bb_is_get_request() )
		return;

	// Bail if no action
	if ( empty( $_GET['action'] ) )
		return;

	do_action( 'bb_get_request', $_GET['action'] );
}

/** Filters *******************************************************************/

/**
 * Filter the plugin locale and domain.
 *
 * @since barebones (1.0)
 *
 * @param string $locale
 * @param string $domain
 */
function bb_plugin_locale( $locale = '', $domain = '' ) {
	return apply_filters( 'bb_plugin_locale', $locale, $domain );
}

/**
 * Piggy back filter for WordPress's 'request' filter
 *
 * @since barebones (1.0)
 * @param array $query_vars
 * @return array
 */
function bb_request( $query_vars = array() ) {
	return apply_filters( 'bb_request', $query_vars );
}

/**
 * The main filter used for theme compatibility and displaying custom bbPress
 * theme files.
 *
 * @since barebones (1.0)
 * @uses apply_filters()
 * @param string $template
 * @return string Template file to use
 */
function bb_template_include( $template = '' ) {
	return apply_filters( 'bb_template_include', $template );
}

/**
 * Generate bbPress-specific rewrite rules
 *
 * @since barebones (1.0)
 * @param WP_Rewrite $wp_rewrite
 * @uses do_action() Calls 'bb_generate_rewrite_rules' with {@link WP_Rewrite}
 */
function bb_generate_rewrite_rules( $wp_rewrite ) {
	do_action_ref_array( 'bb_generate_rewrite_rules', array( &$wp_rewrite ) );
}

/**
 * Filter the allowed themes list for bbPress specific themes
 *
 * @since barebones (1.0)
 * @uses apply_filters() Calls 'bb_allowed_themes' with the allowed themes list
 */
function bb_allowed_themes( $themes ) {
	return apply_filters( 'bb_allowed_themes', $themes );
}

/**
 * Maps forum/topic/reply caps to built in WordPress caps
 *
 * @since barebones (1.0)
 *
 * @param array $caps Capabilities for meta capability
 * @param string $cap Capability name
 * @param int $user_id User id
 * @param mixed $args Arguments
 */
function bb_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
	return apply_filters( 'bb_map_meta_caps', $caps, $cap, $user_id, $args );
}
