<?php

/**
 * barebones Updater
 *
 * @package barebones
 * @subpackage Updater
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * If there is no raw DB version, this is the first installation
 *
 * @since barebones (1.0)
 *
 * @uses get_option()
 * @uses bb_get_db_version() To get barebones's database version
 * @return bool True if update, False if not
 */
function bb_is_install() {
	return ! bb_get_db_version_raw();
}

/**
 * Compare the barebones version to the DB version to determine if updating
 *
 * @since barebones (1.0)
 *
 * @uses get_option()
 * @uses bb_get_db_version() To get barebones's database version
 * @return bool True if update, False if not
 */
function bb_is_update() {
	$raw    = (int) bb_get_db_version_raw();
	$cur    = (int) bb_get_db_version();
	$retval = (bool) ( $raw < $cur );
	return $retval;
}

/**
 * Determine if barebones is being activated
 *
 * Note that this function currently is not used in barebones core and is here
 * for third party plugins to use to check for barebones activation.
 *
 * @since barebones (1.0)
 *
 * @return bool True if activating barebones, false if not
 */
function bb_is_activation( $basename = '' ) {
	$bbp    = barebones();
	$action = false;

	if ( ! empty( $_REQUEST['action'] ) && ( '-1' != $_REQUEST['action'] ) ) {
		$action = $_REQUEST['action'];
	} elseif ( ! empty( $_REQUEST['action2'] ) && ( '-1' != $_REQUEST['action2'] ) ) {
		$action = $_REQUEST['action2'];
	}

	// Bail if not activating
	if ( empty( $action ) || !in_array( $action, array( 'activate', 'activate-selected' ) ) ) {
		return false;
	}

	// The plugin(s) being activated
	if ( $action == 'activate' ) {
		$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
	} else {
		$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
	}

	// Set basename if empty
	if ( empty( $basename ) && !empty( $bbp->basename ) ) {
		$basename = $bbp->basename;
	}

	// Bail if no basename
	if ( empty( $basename ) ) {
		return false;
	}

	// Is barebones being activated?
	return in_array( $basename, $plugins );
}

/**
 * Determine if barebones is being deactivated
 *
 * @since barebones (1.0)
 * @return bool True if deactivating barebones, false if not
 */
function bb_is_deactivation( $basename = '' ) {
	$bbp    = barebones();
	$action = false;
	
	if ( ! empty( $_REQUEST['action'] ) && ( '-1' != $_REQUEST['action'] ) ) {
		$action = $_REQUEST['action'];
	} elseif ( ! empty( $_REQUEST['action2'] ) && ( '-1' != $_REQUEST['action2'] ) ) {
		$action = $_REQUEST['action2'];
	}

	// Bail if not deactivating
	if ( empty( $action ) || !in_array( $action, array( 'deactivate', 'deactivate-selected' ) ) ) {
		return false;
	}

	// The plugin(s) being deactivated
	if ( $action == 'deactivate' ) {
		$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
	} else {
		$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
	}

	// Set basename if empty
	if ( empty( $basename ) && !empty( $bbp->basename ) ) {
		$basename = $bbp->basename;
	}

	// Bail if no basename
	if ( empty( $basename ) ) {
		return false;
	}

	// Is barebones being deactivated?
	return in_array( $basename, $plugins );
}

/**
 * Update the DB to the latest version
 *
 * @since barebones (1.0)
 * @uses update_option()
 * @uses bb_get_db_version() To get barebones's database version
 */
function bb_version_bump() {
	update_option( '_bb_db_version', bb_get_db_version() );
}

/**
 * Setup the barebones updater
 *
 * @since barebones (1.0)
 *
 * @uses bb_version_updater()
 * @uses bb_version_bump()
 * @uses flush_rewrite_rules()
 */
function bb_setup_updater() {

	// Bail if no update needed
	if ( ! bb_is_update() )
		return;

	// Call the automated updater
	bb_version_updater();
}

/**
 * Create a default forum, topic, and reply
 *
 * @since barebones (1.0)
 * @param array $args Array of arguments to override default values
 */
function bb_create_initial_content( $args = array() ) {

	// Parse arguments against default values
	$r = bb_parse_args( $args, array(
		'forum_parent'  => 0,
		'forum_status'  => 'publish',
		'forum_title'   => __( 'General',                                  'bbpress' ),
		'forum_content' => __( 'General chit-chat',                        'bbpress' ),
		'topic_title'   => __( 'Hello World!',                             'bbpress' ),
		'topic_content' => __( 'I am the first topic in your new forums.'' 'barebones' ),
		'reply_title'   => __( 'Re: Hello World!',                         'bbpress' ),
		'reply_content' => __( 'Oh, and this is what a reply looks like.'' 'barebones' ),
	), 'create_initial_content' );

	// Create the initial forum
	$forum_id = bb_insert_forum( array(
		'post_parent'  => $r['forum_parent'],
		'post_status'  => $r['forum_status'],
		'post_title'   => $r['forum_title'],
		'post_content' => $r['forum_content']
	) );

	// Create the initial topic
	$topic_id = bb_insert_topic(
		array(
			'post_parent'  => $forum_id,
			'post_title'   => $r['topic_title'],
			'post_content' => $r['topic_content']
		),
		array( 'forum_id'  => $forum_id )
	);

	// Create the initial reply
	$reply_id = bb_insert_reply(
		array(
			'post_parent'  => $topic_id,
			'post_title'   => $r['reply_title'],
			'post_content' => $r['reply_content']
		),
		array(
			'forum_id'     => $forum_id,
			'topic_id'     => $topic_id
		)
	);

	return array(
		'forum_id' => $forum_id,
		'topic_id' => $topic_id,
		'reply_id' => $reply_id
	);
}

/**
 * barebones's version updater looks at what the current database version is, and
 * runs whatever other code is needed.
 *
 * This is most-often used when the data schema changes, but should also be used
 * to correct issues with barebones meta-data silently on software update.
 *
 * @since barebones (1.0)
 */
function bb_version_updater() {

	// Get the raw database version
	$raw_db_version = (int) bb_get_db_version_raw();

	/** 2.0 Branch ************************************************************/

	// 2.0, 2.0.1, 2.0.2, 2.0.3
	if ( $raw_db_version < 200 ) {
		// No changes
	}

	/** 2.1 Branch ************************************************************/

	// 2.1, 2.1.1
	if ( $raw_db_version < 211 ) {

		/**
		 * Repair private and hidden forum data
		 *
		 * @link http://bbpress.trac.wordpress.org/ticket/1891
		 */
		bb_admin_repair_forum_visibility();
	}

	/** 2.2 Branch ************************************************************/

	// 2.2
	if ( $raw_db_version < 220 ) {

		// Remove the Moderator role from the database
		remove_role( bb_get_moderator_role() );

		// Remove the Participant role from the database
		remove_role( bb_get_participant_role() );

		// Remove capabilities
		bb_remove_caps();
	}

	/** 2.3 Branch ************************************************************/

	// 2.3
	if ( $raw_db_version < 230 ) {
		// No changes
	}

	/** All done! *************************************************************/

	// Bump the version
	bb_version_bump();

	// Delete rewrite rules to force a flush
	bb_delete_rewrite_rules();
}

/**
 * Redirect user to barebones's What's New page on activation
 *
 * @since barebones (1.0)
 *
 * @internal Used internally to redirect barebones to the about page on activation
 *
 * @uses is_network_admin() To bail if being network activated
 * @uses set_transient() To drop the activation transient for 30 seconds
 *
 * @return If network admin or bulk activation
 */
function bb_add_activation_redirect() {

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
		return;

	// Add the transient to redirect
    set_transient( '_bb_activation_redirect', true, 30 );
}
