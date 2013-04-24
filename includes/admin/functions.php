<?php

/**
 * barebones Admin Functions
 *
 * @package barebones
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Admin Menus ***************************************************************/

/**
 * Add a separator to the WordPress admin menus
 *
 * @since barebones (1.0)
 */
function bb_admin_separator() {

	// Caps necessary where a separator is necessary
	$caps = array(
		'bb_forums_admin',
		'bb_topics_admin',
		'bb_replies_admin',
	);

	// Loop through caps, and look for a reason to show the separator
	foreach ( $caps as $cap ) {
		if ( current_user_can( $cap ) ) {
			barebones()->admin->show_separator = true;
			break;
		}
	}

	// Bail if no separator
	if ( false === barebones()->admin->show_separator ) {
		return;
	}

	global $menu;

	$menu[] = array( '', 'read', 'separator-bbpress', '', 'wp-menu-separator bbpress' );
}

/**
 * Tell WordPress we have a custom menu order
 *
 * @since barebones (1.0)
 *
 * @param bool $menu_order Menu order
 * @return mixed True if separator, false if not
 */
function bb_admin_custom_menu_order( $menu_order = false ) {
	if ( false === barebones()->admin->show_separator )
		return $menu_order;

	return true;
}

/**
 * Move our custom separator above our custom post types
 *
 * @since barebones (1.0)
 *
 * @param array $menu_order Menu Order
 * @uses bb_get_forum_post_type() To get the forum post type
 * @return array Modified menu order
 */
function bb_admin_menu_order( $menu_order ) {

	// Bail if user cannot see any top level barebones menus
	if ( empty( $menu_order ) || ( false === barebones()->admin->show_separator ) )
		return $menu_order;

	// Initialize our custom order array
	$bb_menu_order = array();

	// Menu values
	$second_sep   = 'separator2';
	$custom_menus = array(
		'separator-bbpress',                               // Separator
		'edit.php?post_type=' . bb_get_forum_post_type(), // Forums
		'edit.php?post_type=' . bb_get_topic_post_type(), // Topics
		'edit.php?post_type=' . bb_get_reply_post_type()  // Replies
	);

	// Loop through menu order and do some rearranging
	foreach ( $menu_order as $item ) {

		// Position barebones menus above appearance
		if ( $second_sep == $item ) {

			// Add our custom menus
			foreach( $custom_menus as $custom_menu ) {
				if ( array_search( $custom_menu, $menu_order ) ) {
					$bb_menu_order[] = $custom_menu;
				}
			}

			// Add the appearance separator
			$bb_menu_order[] = $second_sep;

		// Skip our menu items
		} elseif ( ! in_array( $item, $custom_menus ) ) {
			$bb_menu_order[] = $item;
		}
	}

	// Return our custom order
	return $bb_menu_order;
}

/**
 * Filter sample permalinks so that certain languages display properly.
 *
 * @since barebones (1.0)
 *
 * @param string $post_link Custom post type permalink
 * @param object $_post Post data object
 * @param bool $leavename Optional, defaults to false. Whether to keep post name or page name.
 * @param bool $sample Optional, defaults to false. Is it a sample permalink.
 *
 * @uses is_admin() To make sure we're on an admin page
 * @uses bb_is_custom_post_type() To get the forum post type
 *
 * @return string The custom post type permalink
 */
function bb_filter_sample_permalink( $post_link, $_post, $leavename = false, $sample = false ) {

	// Bail if not on an admin page and not getting a sample permalink
	if ( !empty( $sample ) && is_admin() && bb_is_custom_post_type() )
		return urldecode( $post_link );

	// Return post link
	return $post_link;
}

/**
 * Uninstall all barebones options and capabilities from a specific site.
 *
 * @since barebones (1.0)
 * @param type $site_id
 */
function bb_do_uninstall( $site_id = 0 ) {
	if ( empty( $site_id ) )
		$site_id = get_current_blog_id();

	switch_to_blog( $site_id );
	bb_delete_options();
	bb_remove_caps();
	flush_rewrite_rules();
	restore_current_blog();
}

/**
 * Redirect user to barebones's What's New page on activation
 *
 * @since barebones (1.0)
 *
 * @internal Used internally to redirect barebones to the about page on activation
 *
 * @uses get_transient() To see if transient to redirect exists
 * @uses delete_transient() To delete the transient if it exists
 * @uses is_network_admin() To bail if being network activated
 * @uses wp_safe_redirect() To redirect
 * @uses add_query_arg() To help build the URL to redirect to
 * @uses admin_url() To get the admin URL to index.php
 *
 * @return If no transient, or in network admin, or is bulk activation
 */
function bb_do_activation_redirect() {

	// Bail if no activation redirect
    if ( ! get_transient( '_bb_activation_redirect' ) )
		return;

	// Delete the redirect transient
	delete_transient( '_bb_activation_redirect' );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
		return;

	// Redirect to barebones about page
	wp_safe_redirect( add_query_arg( array( 'page' => 'bbp-about' ), admin_url( 'index.php' ) ) );
}

/**
 * This tells WP to highlight the Tools > Forums menu item,
 * regardless of which actual barebones Tools screen we are on.
 *
 * The conditional prevents the override when the user is viewing settings or
 * any third-party plugins.
 *
 * @since barebones (1.0)
 * @global string $plugin_page
 * @global array $submenu_file
 */
function bb_tools_modify_menu_highlight() {
	global $plugin_page, $submenu_file;

	// This tweaks the Tools subnav menu to only show one barebones menu item
	if ( ! in_array( $plugin_page, array( 'bbp-settings' ) ) )
		$submenu_file = 'bbp-repair';
}

/**
 * Output the tabs in the admin area
 *
 * @since barebones (1.0)
 * @param string $active_tab Name of the tab that is active
 */
function bb_tools_admin_tabs( $active_tab = '' ) {
	echo bb_get_tools_admin_tabs( $active_tab );
}

	/**
	 * Output the tabs in the admin area
	 *
	 * @since barebones (1.0)
	 * @param string $active_tab Name of the tab that is active
	 */
	function bb_get_tools_admin_tabs( $active_tab = '' ) {

		// Declare local variables
		$tabs_html    = '';
		$idle_class   = 'nav-tab';
		$active_class = 'nav-tab nav-tab-active';

		// Setup core admin tabs
		$tabs = apply_filters( 'bb_tools_admin_tabs', array(
			'0' => array(
				'href' => get_admin_url( '', add_query_arg( array( 'page' => 'bbp-repair'    ), 'tools.php' ) ),
				'name' => __( 'Repair Forums'' 'barebones' )
			),
			'1' => array(
				'href' => get_admin_url( '', add_query_arg( array( 'page' => 'bbp-converter' ), 'tools.php' ) ),
				'name' => __( 'Import Forums'' 'barebones' )
			),
			'2' => array(
				'href' => get_admin_url( '', add_query_arg( array( 'page' => 'bbp-reset'     ), 'tools.php' ) ),
				'name' => __( 'Reset Forums'' 'barebones' )
			)
		) );

		// Loop through tabs and build navigation
		foreach( $tabs as $tab_id => $tab_data ) {
			$is_current = (bool) ( $tab_data['name'] == $active_tab );
			$tab_class  = $is_current ? $active_class : $idle_class;
			$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
		}

		// Output the tabs
		return $tabs_html;
	}
