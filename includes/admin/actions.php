<?php

/**
 * barebones Admin Actions
 *
 * @package barebones
 * @subpackage Admin
 *
 * This file contains the actions that are used through-out barebones Admin. They
 * are consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional actions can currently be found
 *
 *  - barebones: In {@link barebones::setup_actions()} in barebones.php
 *  - Admin: More in {@link BB_Admin::setup_actions()} in admin.php
 *
 * @see bbp-core-actions.php
 * @see bbp-core-filters.php
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Attach barebones to WordPress
 *
 * barebones uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when barebones is
 * installed and activated, without needing to do complicated guesswork.
 *
 * For more information on how this works, see the 'Plugin Dependency' section
 * near the bottom of this file.
 *
 *           v--WordPress Actions       v--barebones Sub-actions
 */
add_action( 'admin_menu',              'bb_admin_menu'                    );
add_action( 'admin_init',              'bb_admin_init'                    );
add_action( 'admin_head',              'bb_admin_head'                    );
add_action( 'admin_notices',           'bb_admin_notices'                 );
add_action( 'custom_menu_order',       'bb_admin_custom_menu_order'       );
add_action( 'menu_order',              'bb_admin_menu_order'              );
add_action( 'wpmu_new_blog',           'bb_new_site',               10, 6 );

// Hook on to admin_init
add_action( 'bb_admin_init', 'bb_admin_forums'                );
add_action( 'bb_admin_init', 'bb_admin_topics'                );
add_action( 'bb_admin_init', 'bb_admin_replies'               );
add_action( 'bb_admin_init', 'bb_setup_updater',          999 );
add_action( 'bb_admin_init', 'bb_register_importers'          );
add_action( 'bb_admin_init', 'bb_register_admin_style'        );
add_action( 'bb_admin_init', 'bb_register_admin_settings'     );
add_action( 'bb_admin_init', 'bb_do_activation_redirect', 1   );

// Initialize the admin area
add_action( 'bb_init', 'bb_admin' );

// Reset the menu order
add_action( 'bb_admin_menu', 'bb_admin_separator' );

// Activation
add_action( 'bb_activation', 'bb_delete_rewrite_rules'    );

// Deactivation
add_action( 'bb_deactivation', 'bb_remove_caps'          );
add_action( 'bb_deactivation', 'bb_delete_rewrite_rules' );

// New Site
add_action( 'bb_new_site', 'bb_create_initial_content', 8 );

// Contextual Helpers
add_action( 'load-settings_page_barebones', 'bb_admin_settings_help' );

// Handle submission of Tools pages
add_action( 'load-tools_page_bbp-repair', 'bb_admin_repair_handler' );
add_action( 'load-tools_page_bbp-reset',  'bb_admin_reset_handler'  );

// Add sample permalink filter
add_filter( 'post_type_link', 'bb_filter_sample_permalink', 10, 4 );

/**
 * When a new site is created in a multisite installation, run the activation
 * routine on that site
 *
 * @since barebones (1.0)
 *
 * @param int $blog_id
 * @param int $user_id
 * @param string $domain
 * @param string $path
 * @param int $site_id
 * @param array() $meta
 */
function bb_new_site( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	// Bail if plugin is not network activated
	if ( ! is_plugin_active_for_network( barebones()->basename ) )
		return;

	// Switch to the new blog
	switch_to_blog( $blog_id );

	// Do the barebones activation routine
	do_action( 'bb_new_site', $blog_id, $user_id, $domain, $path, $site_id, $meta );

	// restore original blog
	restore_current_blog();
}

/** Sub-Actions ***************************************************************/

/**
 * Piggy back admin_init action
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_admin_init'
 */
function bb_admin_init() {
	do_action( 'bb_admin_init' );
}

/**
 * Piggy back admin_menu action
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_admin_menu'
 */
function bb_admin_menu() {
	do_action( 'bb_admin_menu' );
}

/**
 * Piggy back admin_head action
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_admin_head'
 */
function bb_admin_head() {
	do_action( 'bb_admin_head' );
}

/**
 * Piggy back admin_notices action
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_admin_notices'
 */
function bb_admin_notices() {
	do_action( 'bb_admin_notices' );
}

/**
 * Dedicated action to register barebones importers
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_admin_notices'
 */
function bb_register_importers() {
	do_action( 'bb_register_importers' );
}

/**
 * Dedicated action to register admin styles
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_admin_notices'
 */
function bb_register_admin_style() {
	do_action( 'bb_register_admin_style' );
}

/**
 * Dedicated action to register admin settings
 *
 * @since barebones (1.0)
 * @uses do_action() Calls 'bb_register_admin_settings'
 */
function bb_register_admin_settings() {
	do_action( 'bb_register_admin_settings' );
}
