<?php

/**
 * bbPress Topic Functions
 *
 * @package bbPress
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Insert ********************************************************************/

/**
 * A wrapper for wp_insert_post() that also includes the necessary meta values
 * for the topic to function properly.
 *
 * @since barebones (1.0)
 *
 * @uses bb_parse_args()
 * @uses bb_get_topic_post_type()
 * @uses wp_insert_post()
 * @uses update_post_meta()
 *
 * @param array $topic_data Forum post data
 * @param arrap $topic_meta Forum meta data
 */
function bb_insert_topic( $topic_data = array(), $topic_meta = array() ) {

	// Parse arguments against default values
	$topic_data = bb_parse_args( $topic_data, array(
		'post_parent'    => 0, // forum ID
		'post_status'    => bb_get_public_status_id(),
		'post_type'      => bb_get_topic_post_type(),
		'post_author'    => bb_get_current_user_id(),
		'post_password'  => '',
		'post_content'   => '',
		'post_title'     => '',
		'comment_status' => 'closed',
		'menu_order'     => 0,
	), 'insert_topic' );

	// Insert topic
	$topic_id   = wp_insert_post( $topic_data );

	// Bail if no topic was added
	if ( empty( $topic_id ) )
		return false;

	// Parse arguments against default values
	$topic_meta = bb_parse_args( $topic_meta, array(
		'author_ip'          => bb_current_author_ip(),
		'forum_id'           => 0,
		'topic_id'           => $topic_id,
		'voice_count'        => 1,
		'reply_count'        => 0,
		'reply_count_hidden' => 0,
		'last_reply_id'      => 0,
		'last_active_id'     => $topic_id,
		'last_active_time'   => get_post_field( 'post_date', $topic_id, 'db' ),
	), 'insert_topic_meta' );

	// Insert topic meta
	foreach ( $topic_meta as $meta_key => $meta_value )
		update_post_meta( $topic_id, '_bb_' . $meta_key, $meta_value );

	// Update the forum
	$forum_id = bb_get_topic_forum_id( $topic_id );
	if ( !empty( $forum_id ) )
		bb_update_forum( array( 'forum_id' => $forum_id ) );

	// Return new topic ID
	return $topic_id;
}

/** Post Form Handlers ********************************************************/

/**
 * Handles the front end topic submission
 *
 * @param string $action The requested action to compare this function to
 * @uses bb_add_error() To add an error message
 * @uses bb_verify_nonce_request() To verify the nonce and check the referer
 * @uses bb_is_anonymous() To check if an anonymous post is being made
 * @uses current_user_can() To check if the current user can publish topic
 * @uses bb_get_current_user_id() To get the current user id
 * @uses bb_filter_anonymous_post_data() To filter anonymous data
 * @uses bb_set_current_anonymous_user_data() To set the anonymous user cookies
 * @uses is_wp_error() To check if the value retrieved is a {@link WP_Error}
 * @uses esc_attr() For sanitization
 * @uses bb_is_forum_category() To check if the forum is a category
 * @uses bb_is_forum_closed() To check if the forum is closed
 * @uses bb_is_forum_private() To check if the forum is private
 * @uses bb_check_for_flood() To check for flooding
 * @uses bb_check_for_duplicate() To check for duplicates
 * @uses bb_get_topic_post_type() To get the topic post type
 * @uses remove_filter() To remove the custom kses filters if needed
 * @uses apply_filters() Calls 'bb_new_topic_pre_title' with the content
 * @uses apply_filters() Calls 'bb_new_topic_pre_content' with the content
 * @uses bbPress::errors::get_error_codes() To get the {@link WP_Error} errors
 * @uses wp_insert_post() To insert the topic
 * @uses do_action() Calls 'bb_new_topic' with the topic id, forum id,
 *                    anonymous data and reply author
 * @uses bb_stick_topic() To stick or super stick the topic
 * @uses bb_unstick_topic() To unstick the topic
 * @uses bb_get_topic_permalink() To get the topic permalink
 * @uses wp_safe_redirect() To redirect to the topic link
 * @uses bbPress::errors::get_error_messages() To get the {@link WP_Error} error
 *                                              messages
 */
function bb_new_topic_handler( $action = '' ) {

	// Bail if action is not bbp-new-topic
	if ( 'bbp-new-topic' !== $action )
		return;

	// Nonce check
	if ( ! bb_verify_nonce_request( 'bbp-new-topic' ) ) {
		bb_add_error( 'bb_new_topic_nonce', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bbpress' ) );
		return;
	}

	// Define local variable(s)
	$view_all = false;
	$forum_id = $topic_author = $anonymous_data = 0;
	$topic_title = $topic_content = '';
	$terms = array( bb_get_topic_tag_tax_id() => array() );

	/** Topic Author **********************************************************/

	// User is anonymous
	if ( bb_is_anonymous() ) {

		// Filter anonymous data
		$anonymous_data = bb_filter_anonymous_post_data();

		// Anonymous data checks out, so set cookies, etc...
		if ( !empty( $anonymous_data ) && is_array( $anonymous_data ) ) {
			bb_set_current_anonymous_user_data( $anonymous_data );
		}

	// User is logged in
	} else {

		// User cannot create topics
		if ( !current_user_can( 'publish_topics' ) ) {
			bb_add_error( 'bb_topic_permissions', __( '<strong>ERROR</strong>: You do not have permission to create new topics.', 'bbpress' ) );
			return;
		}

		// Topic author is current user
		$topic_author = bb_get_current_user_id();
	}

	// Remove the custom kses filters from title and content for capable users and if the nonce is verified
	if ( current_user_can( 'unfiltered_html' ) && !empty( $_POST['_bb_unfiltered_html_topic'] ) && wp_create_nonce( 'bbp-unfiltered-html-topic_new' ) == $_POST['_bb_unfiltered_html_topic'] ) {
		remove_filter( 'bb_new_topic_pre_title',   'wp_filter_kses'  );
		remove_filter( 'bb_new_topic_pre_content', 'bb_filter_kses' );
	}

	/** Topic Title ***********************************************************/

	if ( !empty( $_POST['bb_topic_title'] ) )
		$topic_title = esc_attr( strip_tags( $_POST['bb_topic_title'] ) );

	// Filter and sanitize
	$topic_title = apply_filters( 'bb_new_topic_pre_title', $topic_title );

	// No topic title
	if ( empty( $topic_title ) )
		bb_add_error( 'bb_topic_title', __( '<strong>ERROR</strong>: Your topic needs a title.', 'bbpress' ) );

	/** Topic Content *********************************************************/

	if ( !empty( $_POST['bb_topic_content'] ) )
		$topic_content = $_POST['bb_topic_content'];

	// Filter and sanitize
	$topic_content = apply_filters( 'bb_new_topic_pre_content', $topic_content );

	// No topic content
	if ( empty( $topic_content ) )
		bb_add_error( 'bb_topic_content', __( '<strong>ERROR</strong>: Your topic cannot be empty.', 'bbpress' ) );

	/** Topic Forum ***********************************************************/

	// Forum id was not passed
	if ( empty( $_POST['bb_forum_id'] ) ) {
		bb_add_error( 'bb_topic_forum_id', __( '<strong>ERROR</strong>: Forum ID is missing.', 'bbpress' ) );

	// Forum id was passed
	} elseif ( is_numeric( $_POST['bb_forum_id'] ) ) {
		$forum_id = (int) $_POST['bb_forum_id'];
	}

	// Forum exists
	if ( !empty( $forum_id ) ) {

		// Forum is a category
		if ( bb_is_forum_category( $forum_id ) ) {
			bb_add_error( 'bb_edit_topic_forum_category', __( '<strong>ERROR</strong>: This forum is a category. No topics can be created in this forum.', 'bbpress' ) );

		// Forum is not a category
		} else {

			// Forum is closed and user cannot access
			if ( bb_is_forum_closed( $forum_id ) && !current_user_can( 'edit_forum', $forum_id ) ) {
				bb_add_error( 'bb_edit_topic_forum_closed', __( '<strong>ERROR</strong>: This forum has been closed to new topics.', 'bbpress' ) );
			}

			// Forum is private and user cannot access
			if ( bb_is_forum_private( $forum_id ) ) {
				if ( !current_user_can( 'read_private_forums' ) ) {
					bb_add_error( 'bb_edit_topic_forum_private', __( '<strong>ERROR</strong>: This forum is private and you do not have the capability to read or create new topics in it.', 'bbpress' ) );
				}

			// Forum is hidden and user cannot access
			} elseif ( bb_is_forum_hidden( $forum_id ) ) {
				if ( !current_user_can( 'read_hidden_forums' ) ) {
					bb_add_error( 'bb_edit_topic_forum_hidden', __( '<strong>ERROR</strong>: This forum is hidden and you do not have the capability to read or create new topics in it.', 'bbpress' ) );
				}
			}
		}
	}

	/** Topic Flooding ********************************************************/

	if ( !bb_check_for_flood( $anonymous_data, $topic_author ) )
		bb_add_error( 'bb_topic_flood', __( '<strong>ERROR</strong>: Slow down; you move too fast.', 'bbpress' ) );

	/** Topic Duplicate *******************************************************/

	if ( !bb_check_for_duplicate( array( 'post_type' => bb_get_topic_post_type(), 'post_author' => $topic_author, 'post_content' => $topic_content, 'anonymous_data' => $anonymous_data ) ) )
		bb_add_error( 'bb_topic_duplicate', __( '<strong>ERROR</strong>: Duplicate topic detected; it looks as though you&#8217;ve already said that!', 'bbpress' ) );

	/** Topic Blacklist *******************************************************/

	if ( !bb_check_for_blacklist( $anonymous_data, $topic_author, $topic_title, $topic_content ) )
		bb_add_error( 'bb_topic_blacklist', __( '<strong>ERROR</strong>: Your topic cannot be created at this time.', 'bbpress' ) );

	/** Topic Status **********************************************************/

	// Maybe put into moderation
	if ( !bb_check_for_moderation( $anonymous_data, $topic_author, $topic_title, $topic_content ) ) {
		$topic_status = bb_get_pending_status_id();

	// Default to published
	} else {
		$topic_status = bb_get_public_status_id();
	}

	/** Topic Tags ************************************************************/

	if ( bb_allow_topic_tags() && !empty( $_POST['bb_topic_tags'] ) ) {

		// Escape tag input
		$terms = esc_attr( strip_tags( $_POST['bb_topic_tags'] ) );

		// Explode by comma
		if ( strstr( $terms, ',' ) ) {
			$terms = explode( ',', $terms );
		}

		// Add topic tag ID as main key
		$terms = array( bb_get_topic_tag_tax_id() => $terms );
	}

	/** Additional Actions (Before Save) **************************************/

	do_action( 'bb_new_topic_pre_extras', $forum_id );

	// Bail if errors
	if ( bb_has_errors() )
		return;

	/** No Errors *************************************************************/

	// Add the content of the form to $topic_data as an array.
	// Just in time manipulation of topic data before being created
	$topic_data = apply_filters( 'bb_new_topic_pre_insert', array(
		'post_author'    => $topic_author,
		'post_title'     => $topic_title,
		'post_content'   => $topic_content,
		'post_status'    => $topic_status,
		'post_parent'    => $forum_id,
		'post_type'      => bb_get_topic_post_type(),
		'tax_input'      => $terms,
		'comment_status' => 'closed'
	) );

	// Insert topic
	$topic_id = wp_insert_post( $topic_data );

	/** No Errors *************************************************************/

	if ( !empty( $topic_id ) && !is_wp_error( $topic_id ) ) {

		/** Trash Check *******************************************************/

		// If the forum is trash, or the topic_status is switched to
		// trash, trash it properly
		if ( ( get_post_field( 'post_status', $forum_id ) == bb_get_trash_status_id() ) || ( $topic_data['post_status'] == bb_get_trash_status_id() ) ) {

			// Trash the reply
			wp_trash_post( $topic_id );

			// Force view=all
			$view_all = true;
		}

		/** Spam Check ********************************************************/

		// If reply or topic are spam, officially spam this reply
		if ( $topic_data['post_status'] == bb_get_spam_status_id() ) {
			add_post_meta( $topic_id, '_bb_spam_meta_status', bb_get_public_status_id() );

			// Force view=all
			$view_all = true;
		}

		/** Update counts, etc... *********************************************/

		do_action( 'bb_new_topic', $topic_id, $forum_id, $anonymous_data, $topic_author );

		/** Stickies **********************************************************/

		// Sticky check after 'bb_new_topic' action so forum ID meta is set
		if ( !empty( $_POST['bb_stick_topic'] ) && in_array( $_POST['bb_stick_topic'], array( 'stick', 'super', 'unstick' ) ) ) {

			// What's the haps?
			switch ( $_POST['bb_stick_topic'] ) {

				// Sticky in this forum
				case 'stick'   :
					bb_stick_topic( $topic_id );
					break;

				// Super sticky in all forums
				case 'super'   :
					bb_stick_topic( $topic_id, true );
					break;

				// We can avoid this as it is a new topic
				case 'unstick' :
				default        :
					break;
			}
		}

		/** Additional Actions (After Save) ***********************************/

		do_action( 'bb_new_topic_post_extras', $topic_id );

		/** Redirect **********************************************************/

		// Redirect to
		$redirect_to = bb_get_redirect_to();

		// Get the topic URL
		$redirect_url = bb_get_topic_permalink( $topic_id, $redirect_to );

		// Add view all?
		if ( bb_get_view_all() || !empty( $view_all ) ) {

			// User can moderate, so redirect to topic with view all set
			if ( current_user_can( 'moderate' ) ) {
				$redirect_url = bb_add_view_all( $redirect_url );

			// User cannot moderate, so redirect to forum
			} else {
				$redirect_url = bb_get_forum_permalink( $forum_id );
			}
		}

		// Allow to be filtered
		$redirect_url = apply_filters( 'bb_new_topic_redirect_to', $redirect_url, $redirect_to, $topic_id );

		/** Successful Save ***************************************************/

		// Redirect back to new topic
		wp_safe_redirect( $redirect_url );

		// For good measure
		exit();

	// Errors
	} else {
		$append_error = ( is_wp_error( $topic_id ) && $topic_id->get_error_message() ) ? $topic_id->get_error_message() . ' ' : '';
		bb_add_error( 'bb_topic_error', __( '<strong>ERROR</strong>: The following problem(s) have been found with your topic:' . $append_error, 'bbpress' ) );
	}
}

/**
 * Handles the front end edit topic submission
 *
 * @param string $action The requested action to compare this function to
 * @uses bb_add_error() To add an error message
 * @uses bb_get_topic() To get the topic
 * @uses bb_verify_nonce_request() To verify the nonce and check the request
 * @uses bb_is_topic_anonymous() To check if topic is by an anonymous user
 * @uses current_user_can() To check if the current user can edit the topic
 * @uses bb_filter_anonymous_post_data() To filter anonymous data
 * @uses is_wp_error() To check if the value retrieved is a {@link WP_Error}
 * @uses esc_attr() For sanitization
 * @uses bb_is_forum_category() To check if the forum is a category
 * @uses bb_is_forum_closed() To check if the forum is closed
 * @uses bb_is_forum_private() To check if the forum is private
 * @uses remove_filter() To remove the custom kses filters if needed
 * @uses apply_filters() Calls 'bb_edit_topic_pre_title' with the title and
 *                        topic id
 * @uses apply_filters() Calls 'bb_edit_topic_pre_content' with the content
 *                        and topic id
 * @uses bbPress::errors::get_error_codes() To get the {@link WP_Error} errors
 * @uses wp_save_post_revision() To save a topic revision
 * @uses bb_update_topic_revision_log() To update the topic revision log
 * @uses bb_stick_topic() To stick or super stick the topic
 * @uses bb_unstick_topic() To unstick the topic
 * @uses wp_update_post() To update the topic
 * @uses do_action() Calls 'bb_edit_topic' with the topic id, forum id,
 *                    anonymous data and reply author
 * @uses bb_move_topic_handler() To handle movement of a topic from one forum
 *                                 to another
 * @uses bb_get_topic_permalink() To get the topic permalink
 * @uses wp_safe_redirect() To redirect to the topic link
 * @uses bbPress::errors::get_error_messages() To get the {@link WP_Error} error
 *                                              messages
 */
function bb_edit_topic_handler( $action = '' ) {

	// Bail if action is not bbp-edit-topic
	if ( 'bbp-edit-topic' !== $action )
		return;

	// Define local variable(s)
	$revisions_removed = false;
	$topic = $topic_id = $topic_author = $forum_id = $anonymous_data = 0;
	$topic_title = $topic_content = $topic_edit_reason = '';

	/** Topic *****************************************************************/

	// Topic id was not passed
	if ( empty( $_POST['bb_topic_id'] ) ) {
		bb_add_error( 'bb_edit_topic_id', __( '<strong>ERROR</strong>: Topic ID not found.', 'bbpress' ) );
		return;

	// Topic id was passed
	} elseif ( is_numeric( $_POST['bb_topic_id'] ) ) {
		$topic_id = (int) $_POST['bb_topic_id'];
		$topic    = bb_get_topic( $topic_id );
	}

	// Topic does not exist
	if ( empty( $topic ) ) {
		bb_add_error( 'bb_edit_topic_not_found', __( '<strong>ERROR</strong>: The topic you want to edit was not found.', 'bbpress' ) );
		return;

	// Topic exists
	} else {

		// Check users ability to create new topic
		if ( ! bb_is_topic_anonymous( $topic_id ) ) {

			// User cannot edit this topic
			if ( !current_user_can( 'edit_topic', $topic_id ) ) {
				bb_add_error( 'bb_edit_topic_permissions', __( '<strong>ERROR</strong>: You do not have permission to edit that topic.', 'bbpress' ) );
			}

			// Set topic author
			$topic_author = bb_get_topic_author_id( $topic_id );

		// It is an anonymous post
		} else {

			// Filter anonymous data
			$anonymous_data = bb_filter_anonymous_post_data( array(), true );
		}
	}

	// Nonce check
	if ( ! bb_verify_nonce_request( 'bbp-edit-topic_' . $topic_id ) ) {
		bb_add_error( 'bb_edit_topic_nonce', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bbpress' ) );
		return;
	}

	// Remove the custom kses filters from title and content for capable users and if the nonce is verified
	if ( current_user_can( 'unfiltered_html' ) && !empty( $_POST['_bb_unfiltered_html_topic'] ) && ( wp_create_nonce( 'bbp-unfiltered-html-topic_' . $topic_id ) == $_POST['_bb_unfiltered_html_topic'] ) ) {
		remove_filter( 'bb_edit_topic_pre_title',   'wp_filter_kses'  );
		remove_filter( 'bb_edit_topic_pre_content', 'bb_filter_kses' );
	}

	/** Topic Forum ***********************************************************/

	// Forum id was not passed
	if ( empty( $_POST['bb_forum_id'] ) ) {
		bb_add_error( 'bb_topic_forum_id', __( '<strong>ERROR</strong>: Forum ID is missing.', 'bbpress' ) );

	// Forum id was passed
	} elseif ( is_numeric( $_POST['bb_forum_id'] ) ) {
		$forum_id = (int) $_POST['bb_forum_id'];
	}

	// Current forum this topic is in
	$current_forum_id = bb_get_topic_forum_id( $topic_id );

	// Forum exists
	if ( !empty( $forum_id ) && ( $forum_id !== $current_forum_id ) ) {

		// Forum is a category
		if ( bb_is_forum_category( $forum_id ) ) {
			bb_add_error( 'bb_edit_topic_forum_category', __( '<strong>ERROR</strong>: This forum is a category. No topics can be created in it.', 'bbpress' ) );

		// Forum is not a category
		} else {

			// Forum is closed and user cannot access
			if ( bb_is_forum_closed( $forum_id ) && !current_user_can( 'edit_forum', $forum_id ) ) {
				bb_add_error( 'bb_edit_topic_forum_closed', __( '<strong>ERROR</strong>: This forum has been closed to new topics.', 'bbpress' ) );
			}

			// Forum is private and user cannot access
			if ( bb_is_forum_private( $forum_id ) ) {
				if ( !current_user_can( 'read_private_forums' ) ) {
					bb_add_error( 'bb_edit_topic_forum_private', __( '<strong>ERROR</strong>: This forum is private and you do not have the capability to read or create new topics in it.', 'bbpress' ) );
				}

			// Forum is hidden and user cannot access
			} elseif ( bb_is_forum_hidden( $forum_id ) ) {
				if ( !current_user_can( 'read_hidden_forums' ) ) {
					bb_add_error( 'bb_edit_topic_forum_hidden', __( '<strong>ERROR</strong>: This forum is hidden and you do not have the capability to read or create new topics in it.', 'bbpress' ) );
				}
			}
		}
	}

	/** Topic Title ***********************************************************/

	if ( !empty( $_POST['bb_topic_title'] ) )
		$topic_title = esc_attr( strip_tags( $_POST['bb_topic_title'] ) );

	// Filter and sanitize
	$topic_title = apply_filters( 'bb_edit_topic_pre_title', $topic_title, $topic_id );

	// No topic title
	if ( empty( $topic_title ) )
		bb_add_error( 'bb_edit_topic_title', __( '<strong>ERROR</strong>: Your topic needs a title.', 'bbpress' ) );

	/** Topic Content *********************************************************/

	if ( !empty( $_POST['bb_topic_content'] ) )
		$topic_content = $_POST['bb_topic_content'];

	// Filter and sanitize
	$topic_content = apply_filters( 'bb_edit_topic_pre_content', $topic_content, $topic_id );

	// No topic content
	if ( empty( $topic_content ) )
		bb_add_error( 'bb_edit_topic_content', __( '<strong>ERROR</strong>: Your topic cannot be empty.', 'bbpress' ) );

	/** Topic Blacklist *******************************************************/

	if ( !bb_check_for_blacklist( $anonymous_data, $topic_author, $topic_title, $topic_content ) )
		bb_add_error( 'bb_topic_blacklist', __( '<strong>ERROR</strong>: Your topic cannot be edited at this time.', 'bbpress' ) );

	/** Topic Status **********************************************************/

	// Maybe put into moderation
	if ( !bb_check_for_moderation( $anonymous_data, $topic_author, $topic_title, $topic_content ) ) {

		// Set post status to pending if public or closed
		if ( in_array( $topic->post_status, array( bb_get_public_status_id(), bb_get_closed_status_id() ) ) ) {
			$topic_status = bb_get_pending_status_id();
		}

	// Use existing post_status
	} else {
		$topic_status = $topic->post_status;
	}

	/** Topic Tags ************************************************************/

	// Either replace terms
	if ( bb_allow_topic_tags() && current_user_can( 'assign_topic_tags' ) && ! empty( $_POST['bb_topic_tags'] ) ) {

		// Escape tag input
		$terms = esc_attr( strip_tags( $_POST['bb_topic_tags'] ) );

		// Explode by comma
		if ( strstr( $terms, ',' ) )
			$terms = explode( ',', $terms );

		// Add topic tag ID as main key
		$terms = array( bb_get_topic_tag_tax_id() => $terms );

	// ...or remove them.
	} elseif ( isset( $_POST['bb_topic_tags'] ) ) {
		$terms = array( bb_get_topic_tag_tax_id() => array() );

	// Existing terms
	} else {
		$terms = array( bb_get_topic_tag_tax_id() => explode( ',', bb_get_topic_tag_names( $topic_id, ',' ) ) );
	}

	/** Additional Actions (Before Save) **************************************/

	do_action( 'bb_edit_topic_pre_extras', $topic_id );

	// Bail if errors
	if ( bb_has_errors() )
		return;

	/** No Errors *************************************************************/

	// Add the content of the form to $topic_data as an array
	// Just in time manipulation of topic data before being edited
	$topic_data = apply_filters( 'bb_edit_topic_pre_insert', array(
		'ID'           => $topic_id,
		'post_title'   => $topic_title,
		'post_content' => $topic_content,
		'post_status'  => $topic_status,
		'post_parent'  => $forum_id,
		'post_author'  => $topic_author,
		'post_type'    => bb_get_topic_post_type(),
		'tax_input'    => $terms,
	) );

	// Toggle revisions to avoid duplicates
	if ( post_type_supports( bb_get_topic_post_type(), 'revisions' ) ) {
		$revisions_removed = true;
		remove_post_type_support( bb_get_topic_post_type(), 'revisions' );
	}

	// Insert topic
	$topic_id = wp_update_post( $topic_data );

	// Toggle revisions back on
	if ( true === $revisions_removed ) {
		$revisions_removed = false;
		add_post_type_support( bb_get_topic_post_type(), 'revisions' );
	}

	/** No Errors *************************************************************/

	if ( !empty( $topic_id ) && !is_wp_error( $topic_id ) ) {

		// Update counts, etc...
		do_action( 'bb_edit_topic', $topic_id, $forum_id, $anonymous_data, $topic_author , true /* Is edit */ );

		/** Revisions *********************************************************/

		// Revision Reason
		if ( !empty( $_POST['bb_topic_edit_reason'] ) ) {
			$topic_edit_reason = esc_attr( strip_tags( $_POST['bb_topic_edit_reason'] ) );
		}

		// Update revision log
		if ( !empty( $_POST['bb_log_topic_edit'] ) && ( 1 == $_POST['bb_log_topic_edit'] ) )  {
			$revision_id = wp_save_post_revision( $topic_id );
			if ( ! empty( $revision_id ) ) {
				bb_update_topic_revision_log( array(
					'topic_id'    => $topic_id,
					'revision_id' => $revision_id,
					'author_id'   => bb_get_current_user_id(),
					'reason'      => $topic_edit_reason
				) );
			}
		}

		/** Move Topic ********************************************************/

		// If the new forum id is not equal to the old forum id, run the
		// bb_move_topic action and pass the topic's forum id as the
		// first arg and topic id as the second to update counts.
		if ( $forum_id != $topic->post_parent ) {
			bb_move_topic_handler( $topic_id, $topic->post_parent, $forum_id );
		}

		/** Stickies **********************************************************/

		if ( !empty( $_POST['bb_stick_topic'] ) && in_array( $_POST['bb_stick_topic'], array( 'stick', 'super', 'unstick' ) ) ) {

			// What's the dilly?
			switch ( $_POST['bb_stick_topic'] ) {

				// Sticky in forum
				case 'stick'   :
					bb_stick_topic( $topic_id );
					break;

				// Sticky in all forums
				case 'super'   :
					bb_stick_topic( $topic_id, true );
					break;

				// Normal
				case 'unstick' :
				default        :
					bb_unstick_topic( $topic_id );
					break;
			}
		}

		/** Additional Actions (After Save) ***********************************/

		do_action( 'bb_edit_topic_post_extras', $topic_id );

		/** Redirect **********************************************************/

		// Redirect to
		$redirect_to = bb_get_redirect_to();

		// View all?
		$view_all = bb_get_view_all();

		// Get the topic URL
		$topic_url = bb_get_topic_permalink( $topic_id, $redirect_to );

		// Add view all?
		if ( !empty( $view_all ) )
			$topic_url = bb_add_view_all( $topic_url );

		// Allow to be filtered
		$topic_url = apply_filters( 'bb_edit_topic_redirect_to', $topic_url, $view_all, $redirect_to );

		/** Successful Edit ***************************************************/

		// Redirect back to new topic
		wp_safe_redirect( $topic_url );

		// For good measure
		exit();

	/** Errors ****************************************************************/

	} else {
		$append_error = ( is_wp_error( $topic_id ) && $topic_id->get_error_message() ) ? $topic_id->get_error_message() . ' ' : '';
		bb_add_error( 'bb_topic_error', __( '<strong>ERROR</strong>: The following problem(s) have been found with your topic:' . $append_error . 'Please try again.', 'bbpress' ) );
	}
}

/**
 * Handle all the extra meta stuff from posting a new topic
 *
 * @param int $topic_id Optional. Topic id
 * @param int $forum_id Optional. Forum id
 * @param bool|array $anonymous_data Optional logged-out user data.
 * @param int $author_id Author id
 * @param bool $is_edit Optional. Is the post being edited? Defaults to false.
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_forum_id() To get the forum id
 * @uses bb_get_current_user_id() To get the current user id
 * @yses bb_get_topic_forum_id() To get the topic forum id
 * @uses update_post_meta() To update the topic metas
 * @uses set_transient() To update the flood check transient for the ip
 * @uses bb_update_user_last_posted() To update the users last posted time
 * @uses bb_is_subscriptions_active() To check if the subscriptions feature is
 *                                      activated or not
 * @uses bb_is_user_subscribed() To check if the user is subscribed
 * @uses bb_remove_user_subscription() To remove the user's subscription
 * @uses bb_add_user_subscription() To add the user's subscription
 * @uses bb_update_topic_forum_id() To update the topic's forum id
 * @uses bb_update_topic_topic_id() To update the topic's topic id
 * @uses bb_update_topic_last_reply_id() To update the last reply id topic meta
 * @uses bb_update_topic_last_active_id() To update the topic last active id
 * @uses bb_update_topic_last_active_time() To update the last active topic meta
 * @uses bb_update_topic_reply_count() To update the topic reply count
 * @uses bb_update_topic_reply_count_hidden() To udpate the topic hidden reply count
 * @uses bb_update_topic_voice_count() To update the topic voice count
 * @uses bb_update_topic_walker() To udpate the topic's ancestors
 */
function bb_update_topic( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {

	// Validate the ID's passed from 'bb_new_topic' action
	$topic_id = bb_get_topic_id( $topic_id );
	$forum_id = bb_get_forum_id( $forum_id );

	// Bail if there is no topic
	if ( empty( $topic_id ) )
		return;

	// Check author_id
	if ( empty( $author_id ) )
		$author_id = bb_get_current_user_id();

	// Check forum_id
	if ( empty( $forum_id ) )
		$forum_id = bb_get_topic_forum_id( $topic_id );

	// If anonymous post, store name, email, website and ip in post_meta.
	// It expects anonymous_data to be sanitized.
	// Check bb_filter_anonymous_post_data() for sanitization.
	if ( !empty( $anonymous_data ) && is_array( $anonymous_data ) ) {

		// Parse arguments against default values
		$r = bb_parse_args( $anonymous_data, array(
			'bb_anonymous_name'    => '',
			'bb_anonymous_email'   => '',
			'bb_anonymous_website' => '',
		), 'update_topic' );

		// Update all anonymous metas
		foreach( $r as $anon_key => $anon_value ) {
			update_post_meta( $topic_id, '_' . $anon_key, (string) $anon_value, false );
		}

		// Set transient for throttle check (only on new, not edit)
		if ( empty( $is_edit ) ) {
			set_transient( '_bb_' . bb_current_author_ip() . '_last_posted', time() );
		}

	} else {
		if ( empty( $is_edit ) && !current_user_can( 'throttle' ) ) {
			bb_update_user_last_posted( $author_id );
		}
	}

	// Handle Subscription Checkbox
	if ( bb_is_subscriptions_active() && !empty( $author_id ) ) {
		$subscribed = bb_is_user_subscribed( $author_id, $topic_id );
		$subscheck  = ( !empty( $_POST['bb_topic_subscription'] ) && ( 'bb_subscribe' == $_POST['bb_topic_subscription'] ) ) ? true : false;

		// Subscribed and unsubscribing
		if ( true == $subscribed && false == $subscheck ) {
			bb_remove_user_subscription( $author_id, $topic_id );

		// Subscribing
		} elseif ( false == $subscribed && true == $subscheck ) {
			bb_add_user_subscription( $author_id, $topic_id );
		}
	}

	// Forum topic meta
	bb_update_topic_forum_id( $topic_id, $forum_id );
	bb_update_topic_topic_id( $topic_id, $topic_id );

	// Update associated topic values if this is a new topic
	if ( empty( $is_edit ) ) {

		// Update poster IP if not editing
		update_post_meta( $topic_id, '_bb_author_ip', bb_current_author_ip(), false );

		// Last active time
		$last_active = current_time( 'mysql' );

		// Reply topic meta
		bb_update_topic_last_reply_id      ( $topic_id, 0            );
		bb_update_topic_last_active_id     ( $topic_id, $topic_id    );
		bb_update_topic_last_active_time   ( $topic_id, $last_active );
		bb_update_topic_reply_count        ( $topic_id, 0            );
		bb_update_topic_reply_count_hidden ( $topic_id, 0            );
		bb_update_topic_voice_count        ( $topic_id               );

		// Walk up ancestors and do the dirty work
		bb_update_topic_walker( $topic_id, $last_active, $forum_id, 0, false );
	}
}

/**
 * Walks up the post_parent tree from the current topic_id, and updates the
 * counts of forums above it. This calls a few internal functions that all run
 * manual queries against the database to get their results. As such, this
 * function can be costly to run but is necessary to keep everything accurate.
 *
 * @since barebones (1.0)
 * @param int $topic_id Topic id
 * @param string $last_active_time Optional. Last active time
 * @param int $forum_id Optional. Forum id
 * @param int $reply_id Optional. Reply id
 * @param bool $refresh Reset all the previous parameters? Defaults to true.
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_topic_forum_id() To get the topic forum id
 * @uses get_post_ancestors() To get the topic's ancestors
 * @uses bb_is_forum() To check if the ancestor is a forum
 * @uses bb_update_forum() To update the forum
 */
function bb_update_topic_walker( $topic_id, $last_active_time = '', $forum_id = 0, $reply_id = 0, $refresh = true ) {

	// Validate topic_id
	$topic_id  = bb_get_topic_id( $topic_id );

	// Define local variable(s)
	$active_id = 0;

	// Topic was passed
	if ( !empty( $topic_id ) ) {

		// Get the forum ID if none was passed
		if ( empty( $forum_id )  ) {
			$forum_id = bb_get_topic_forum_id( $topic_id );
		}

		// Set the active_id based on topic_id/reply_id
		$active_id = empty( $reply_id ) ? $topic_id : $reply_id;
	}

	// Get topic ancestors
	$ancestors = array_values( array_unique( array_merge( array( $forum_id ), (array) get_post_ancestors( $topic_id ) ) ) );

	// Topic status
	$topic_status = get_post_status( $topic_id );

	// If we want a full refresh, unset any of the possibly passed variables
	if ( true == $refresh ) {
		$forum_id = $topic_id = $reply_id = $active_id = $last_active_time = 0;
		$topic_status = bb_get_public_status_id();
	}

	// Loop through ancestors
	if ( !empty( $ancestors ) ) {
		foreach ( $ancestors as $ancestor ) {

			// If ancestor is a forum, update counts
			if ( bb_is_forum( $ancestor ) ) {

				// Update the forum
				bb_update_forum( array(
					'forum_id'           => $ancestor,
					'last_topic_id'      => $topic_id,
					'last_reply_id'      => $reply_id,
					'last_active_id'     => $active_id,
					'last_active_time'   => 0,
					'last_active_status' => $topic_status
				) );
			}
		}
	}
}

/**
 * Handle the moving of a topic from one forum to another. This includes walking
 * up the old and new branches and updating the counts.
 *
 * @param int $topic_id Topic id
 * @param int $old_forum_id Old forum id
 * @param int $new_forum_id New forum id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_forum_id() To get the forum id
 * @uses bb_get_stickies() To get the old forums sticky topics
 * @uses delete_post_meta() To delete the forum sticky meta
 * @uses update_post_meta() To update the old forum sticky meta
 * @uses bb_stick_topic() To stick the topic in the new forum
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses bb_get_all_child_ids() To get the public child ids
 * @uses bb_update_reply_forum_id() To update the reply forum id
 * @uses bb_update_topic_forum_id() To update the topic forum id
 * @uses get_post_ancestors() To get the topic's ancestors
 * @uses bb_is_forum() To check if the ancestor is a forum
 * @uses bb_update_forum() To update the forum
 */
function bb_move_topic_handler( $topic_id, $old_forum_id, $new_forum_id ) {

	// Validate parameters
	$topic_id     = bb_get_topic_id( $topic_id     );
	$old_forum_id = bb_get_forum_id( $old_forum_id );
	$new_forum_id = bb_get_forum_id( $new_forum_id );

	// Update topic forum's ID
	bb_update_topic_forum_id( $topic_id, $new_forum_id );

	/** Stickies **************************************************************/

	// Get forum stickies
	$old_stickies = bb_get_stickies( $old_forum_id );

	// Only proceed if stickies are found
	if ( !empty( $old_stickies ) ) {

		// Define local variables
		$updated_stickies = array();

		// Loop through stickies of forum and add misses to the updated array
		foreach ( (array) $old_stickies as $sticky_topic_id ) {
			if ( $topic_id != $sticky_topic_id ) {
				$updated_stickies[] = $sticky_topic_id;
			}
		}

		// If stickies are different, update or delete them
		if ( $updated_stickies != $old_stickies ) {

			// No more stickies so delete the meta
			if ( empty( $updated_stickies ) ) {
				delete_post_meta( $old_forum_id, '_bb_sticky_topics' );

			// Still stickies so update the meta
			} else {
				update_post_meta( $old_forum_id, '_bb_sticky_topics', $updated_stickies );
			}

			// Topic was sticky, so restick in new forum
			bb_stick_topic( $topic_id );
		}
	}

	/** Topic Replies *********************************************************/

	// Get the topics replies
	$replies = bb_get_all_child_ids( $topic_id, bb_get_reply_post_type() );

	// Update the forum_id of all replies in the topic
	foreach ( $replies as $reply_id ) {
		bb_update_reply_forum_id( $reply_id, $new_forum_id );
	}

	/** Old forum_id **********************************************************/

	// Get topic ancestors
	$old_forum_ancestors = array_values( array_unique( array_merge( array( $old_forum_id ), (array) get_post_ancestors( $old_forum_id ) ) ) );

	// Loop through ancestors and update them
	if ( !empty( $old_forum_ancestors ) ) {
		foreach ( $old_forum_ancestors as $ancestor ) {
			if ( bb_is_forum( $ancestor ) ) {
				bb_update_forum( array(
					'forum_id' => $ancestor,
				) );
			}
		}
	}

	/** New forum_id **********************************************************/

	// Make sure we're not walking twice
	if ( !in_array( $new_forum_id, $old_forum_ancestors ) ) {

		// Get topic ancestors
		$new_forum_ancestors = array_values( array_unique( array_merge( array( $new_forum_id ), (array) get_post_ancestors( $new_forum_id ) ) ) );

		// Make sure we're not walking twice
		$new_forum_ancestors = array_diff( $new_forum_ancestors, $old_forum_ancestors );

		// Loop through ancestors and update them
		if ( !empty( $new_forum_ancestors ) ) {
			foreach ( $new_forum_ancestors as $ancestor ) {
				if ( bb_is_forum( $ancestor ) ) {
					bb_update_forum( array(
						'forum_id' => $ancestor,
					) );
				}
			}
		}
	}
}

/**
 * Merge topic handler
 *
 * Handles the front end merge topic submission
 *
 * @since barebones (1.0)
 *
 * @param string $action The requested action to compare this function to
 * @uses bb_add_error() To add an error message
 * @uses bb_get_topic() To get the topics
 * @uses bb_verify_nonce_request() To verify the nonce and check the request
 * @uses current_user_can() To check if the current user can edit the topics
 * @uses is_wp_error() To check if the value retrieved is a {@link WP_Error}
 * @uses do_action() Calls 'bb_merge_topic' with the destination and source
 *                    topic ids
 * @uses bb_get_topic_subscribers() To get the source topic subscribers
 * @uses bb_add_user_subscription() To add the user subscription
 * @uses bb_remove_user_subscription() To remove the user subscription
 * @uses bb_get_topic_favoriters() To get the source topic favoriters
 * @uses bb_add_user_favorite() To add the user favorite
 * @uses bb_remove_user_favorite() To remove the user favorite
 * @uses wp_get_post_terms() To get the source topic tags
 * @uses wp_set_post_terms() To set the topic tags
 * @uses wp_delete_object_term_relationships() To delete the topic tags
 * @uses bb_open_topic() To open the topic
 * @uses bb_unstick_topic() To unstick the topic
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses get_posts() To get the replies
 * @uses wp_update_post() To update the topic
 * @uses bb_update_reply_topic_id() To update the reply topic id
 * @uses bb_get_topic_forum_id() To get the topic forum id
 * @uses bb_update_reply_forum_id() To update the reply forum id
 * @uses do_action() Calls 'bb_merged_topic_reply' with the reply id and
 *                    destination topic id
 * @uses do_action() Calls 'bb_merged_topic' with the destination and source
 *                    topic ids and source topic's forum id
 * @uses bb_get_topic_permalink() To get the topic permalink
 * @uses wp_safe_redirect() To redirect to the topic link
 */
function bb_merge_topic_handler( $action = '' ) {

	// Bail if action is not bbp-merge-topic
	if ( 'bbp-merge-topic' !== $action )
		return;

	// Define local variable(s)
	$source_topic_id = $destination_topic_id = 0;
	$source_topic = $destination_topic = 0;
	$subscribers = $favoriters = $replies = array();

	/** Source Topic **********************************************************/

	// Topic id
	if ( empty( $_POST['bb_topic_id'] ) ) {
		bb_add_error( 'bb_merge_topic_source_id', __( '<strong>ERROR</strong>: Topic ID not found.', 'bbpress' ) );
	} else {
		$source_topic_id = (int) $_POST['bb_topic_id'];
	}

	// Nonce check
	if ( ! bb_verify_nonce_request( 'bbp-merge-topic_' . $source_topic_id ) ) {
		bb_add_error( 'bb_merge_topic_nonce', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bbpress' ) );
		return;

	// Source topic not found
	} elseif ( !$source_topic = bb_get_topic( $source_topic_id ) ) {
		bb_add_error( 'bb_merge_topic_source_not_found', __( '<strong>ERROR</strong>: The topic you want to merge was not found.', 'bbpress' ) );
		return;
	}

	// Cannot edit source topic
	if ( !current_user_can( 'edit_topic', $source_topic->ID ) ) {
		bb_add_error( 'bb_merge_topic_source_permission', __( '<strong>ERROR</strong>: You do not have the permissions to edit the source topic.', 'bbpress' ) );
		return;
	}

	/** Destination Topic *****************************************************/

	// Topic id
	if ( empty( $_POST['bb_destination_topic'] ) )
		bb_add_error( 'bb_merge_topic_destination_id', __( '<strong>ERROR</strong>: Destination topic ID not found.', 'bbpress' ) );
	else
		$destination_topic_id = (int) $_POST['bb_destination_topic'];

	// Destination topic not found
	if ( !$destination_topic = bb_get_topic( $destination_topic_id ) )
		bb_add_error( 'bb_merge_topic_destination_not_found', __( '<strong>ERROR</strong>: The topic you want to merge to was not found.', 'bbpress' ) );

	// Cannot edit destination topic
	if ( !current_user_can( 'edit_topic', $destination_topic->ID ) )
		bb_add_error( 'bb_merge_topic_destination_permission', __( '<strong>ERROR</strong>: You do not have the permissions to edit the destination topic.', 'bbpress' ) );

	// Bail if errors
	if ( bb_has_errors() )
		return;

	/** No Errors *************************************************************/

	// Update counts, etc...
	do_action( 'bb_merge_topic', $destination_topic->ID, $source_topic->ID );

	/** Date Check ************************************************************/

	// Check if the destination topic is older than the source topic
	if ( strtotime( $source_topic->post_date ) < strtotime( $destination_topic->post_date ) ) {

		// Set destination topic post_date to 1 second before source topic
		$destination_post_date = date( 'Y-m-d H:i:s', strtotime( $source_topic->post_date ) - 1 );

		$postarr = array(
			'ID'            => $destination_topic_id,
			'post_date'     => $destination_post_date,
			'post_date_gmt' => get_gmt_from_date( $destination_post_date )
		);

		// Update destination topic
		wp_update_post( $postarr );
	}

	/** Subscriptions *********************************************************/

	// Get subscribers from source topic
	$subscribers = bb_get_topic_subscribers( $source_topic->ID );

	// Remove the topic from everybody's subscriptions
	if ( !empty( $subscribers ) ) {

		// Loop through each user
		foreach ( (array) $subscribers as $subscriber ) {

			// Shift the subscriber if told to
			if ( !empty( $_POST['bb_topic_subscribers'] ) && ( 1 == $_POST['bb_topic_subscribers'] ) && bb_is_subscriptions_active() )
				bb_add_user_subscription( $subscriber, $destination_topic->ID );

			// Remove old subscription
			bb_remove_user_subscription( $subscriber, $source_topic->ID );
		}
	}

	/** Favorites *************************************************************/

	// Get favoriters from source topic
	$favoriters = bb_get_topic_favoriters( $source_topic->ID );

	// Remove the topic from everybody's favorites
	if ( !empty( $favoriters ) ) {

		// Loop through each user
		foreach ( (array) $favoriters as $favoriter ) {

			// Shift the favoriter if told to
			if ( !empty( $_POST['bb_topic_favoriters'] ) && 1 == $_POST['bb_topic_favoriters'] )
				bb_add_user_favorite( $favoriter, $destination_topic->ID );

			// Remove old favorite
			bb_remove_user_favorite( $favoriter, $source_topic->ID );
		}
	}

	/** Tags ******************************************************************/

	// Get the source topic tags
	$source_topic_tags = wp_get_post_terms( $source_topic->ID, bb_get_topic_tag_tax_id(), array( 'fields' => 'names' ) );

	// Tags to possibly merge
	if ( !empty( $source_topic_tags ) && !is_wp_error( $source_topic_tags ) ) {

		// Shift the tags if told to
		if ( !empty( $_POST['bb_topic_tags'] ) && ( 1 == $_POST['bb_topic_tags'] ) )
			wp_set_post_terms( $destination_topic->ID, $source_topic_tags, bb_get_topic_tag_tax_id(), true );

		// Delete the tags from the source topic
		wp_delete_object_term_relationships( $source_topic->ID, bb_get_topic_tag_tax_id() );
	}

	/** Source Topic **********************************************************/

	// Status
	bb_open_topic( $source_topic->ID );

	// Sticky
	bb_unstick_topic( $source_topic->ID );

	// Get the replies of the source topic
	$replies = (array) get_posts( array(
		'post_parent'    => $source_topic->ID,
		'post_type'      => bb_get_reply_post_type(),
		'posts_per_page' => -1,
		'order'          => 'ASC'
	) );

	// Prepend the source topic to its replies array for processing
	array_unshift( $replies, $source_topic );

	if ( !empty( $replies ) ) {

		/** Merge Replies *****************************************************/

		// Change the post_parent of each reply to the destination topic id
		foreach ( $replies as $reply ) {
			$postarr = array(
				'ID'          => $reply->ID,
				'post_title'  => sprintf( __( 'Reply To: %s', 'bbpress' ), $destination_topic->post_title ),
				'post_name'   => false,
				'post_type'   => bb_get_reply_post_type(),
				'post_parent' => $destination_topic->ID,
				'guid'        => ''
			);

			wp_update_post( $postarr );

			// Adjust reply meta values
			bb_update_reply_topic_id( $reply->ID, $destination_topic->ID                           );
			bb_update_reply_forum_id( $reply->ID, bb_get_topic_forum_id( $destination_topic->ID ) );

			// Do additional actions per merged reply
			do_action( 'bb_merged_topic_reply', $reply->ID, $destination_topic->ID );
		}
	}

	/** Successful Merge ******************************************************/

	// Update topic's last meta data
	bb_update_topic_last_reply_id   ( $destination_topic->ID );
	bb_update_topic_last_active_id  ( $destination_topic->ID );
	bb_update_topic_last_active_time( $destination_topic->ID );

	// Send the post parent of the source topic as it has been shifted
	// (possibly to a new forum) so we need to update the counts of the
	// old forum as well as the new one
	do_action( 'bb_merged_topic', $destination_topic->ID, $source_topic->ID, $source_topic->post_parent );

	// Redirect back to new topic
	wp_safe_redirect( bb_get_topic_permalink( $destination_topic->ID ) );

	// For good measure
	exit();
}

/**
 * Fix counts on topic merge
 *
 * When a topic is merged, update the counts of source and destination topic
 * and their forums.
 *
 * @since barebones (1.0)
 *
 * @param int $destination_topic_id Destination topic id
 * @param int $source_topic_id Source topic id
 * @param int $source_topic_forum Source topic's forum id
 * @uses bb_update_forum_topic_count() To update the forum topic counts
 * @uses bb_update_forum_reply_count() To update the forum reply counts
 * @uses bb_update_topic_reply_count() To update the topic reply counts
 * @uses bb_update_topic_voice_count() To update the topic voice counts
 * @uses bb_update_topic_reply_count_hidden() To update the topic hidden reply
 *                                              count
 * @uses do_action() Calls 'bb_merge_topic_count' with the destination topic
 *                    id, source topic id & source topic forum id
 */
function bb_merge_topic_count( $destination_topic_id, $source_topic_id, $source_topic_forum_id ) {

	/** Source Topic **********************************************************/

	// Forum Topic Counts
	bb_update_forum_topic_count( $source_topic_forum_id );

	// Forum Reply Counts
	bb_update_forum_reply_count( $source_topic_forum_id );

	/** Destination Topic *****************************************************/

	// Topic Reply Counts
	bb_update_topic_reply_count( $destination_topic_id );

	// Topic Hidden Reply Counts
	bb_update_topic_reply_count_hidden( $destination_topic_id );

	// Topic Voice Counts
	bb_update_topic_voice_count( $destination_topic_id );

	do_action( 'bb_merge_topic_count', $destination_topic_id, $source_topic_id, $source_topic_forum_id );
}

/**
 * Split topic handler
 *
 * Handles the front end split topic submission
 *
 * @since barebones (1.0)
 *
 * @param string $action The requested action to compare this function to
 * @uses bb_add_error() To add an error message
 * @uses bb_get_reply() To get the reply
 * @uses bb_get_topic() To get the topics
 * @uses bb_verify_nonce_request() To verify the nonce and check the request
 * @uses current_user_can() To check if the current user can edit the topics
 * @uses bb_get_topic_post_type() To get the topic post type
 * @uses is_wp_error() To check if the value retrieved is a {@link WP_Error}
 * @uses do_action() Calls 'bb_pre_split_topic' with the from reply id, source
 *                    and destination topic ids
 * @uses bb_get_topic_subscribers() To get the source topic subscribers
 * @uses bb_add_user_subscription() To add the user subscription
 * @uses bb_get_topic_favoriters() To get the source topic favoriters
 * @uses bb_add_user_favorite() To add the user favorite
 * @uses wp_get_post_terms() To get the source topic tags
 * @uses wp_set_post_terms() To set the topic tags
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses wpdb::prepare() To prepare our sql query
 * @uses wpdb::get_results() To execute the sql query and get results
 * @uses wp_update_post() To update the replies
 * @uses bb_update_reply_topic_id() To update the reply topic id
 * @uses bb_get_topic_forum_id() To get the topic forum id
 * @uses bb_update_reply_forum_id() To update the reply forum id
 * @uses do_action() Calls 'bb_split_topic_reply' with the reply id and
 *                    destination topic id
 * @uses bb_update_topic_last_reply_id() To update the topic last reply id
 * @uses bb_update_topic_last_active_time() To update the topic last active meta
 * @uses do_action() Calls 'bb_post_split_topic' with the destination and
 *                    source topic ids and source topic's forum id
 * @uses bb_get_topic_permalink() To get the topic permalink
 * @uses wp_safe_redirect() To redirect to the topic link
 */
function bb_split_topic_handler( $action = '' ) {

	// Bail if action is not 'bbp-split-topic'
	if ( 'bbp-split-topic' !== $action )
		return;

	global $wpdb;

	// Prevent debug notices
	$from_reply_id = $destination_topic_id = 0;
	$destination_topic_title = '';
	$destination_topic = $from_reply = $source_topic = '';
	$split_option = false;

	/** Split Reply ***********************************************************/

	if ( empty( $_POST['bb_reply_id'] ) )
		bb_add_error( 'bb_split_topic_reply_id', __( '<strong>ERROR</strong>: Reply ID to split the topic from not found!', 'bbpress' ) );
	else
		$from_reply_id = (int) $_POST['bb_reply_id'];

	$from_reply = bb_get_reply( $from_reply_id );

	// Reply exists
	if ( empty( $from_reply ) )
		bb_add_error( 'bb_split_topic_r_not_found', __( '<strong>ERROR</strong>: The reply you want to split from was not found.', 'bbpress' ) );

	/** Topic to Split ********************************************************/

	// Get the topic being split
	$source_topic = bb_get_topic( $from_reply->post_parent );

	// No topic
	if ( empty( $source_topic ) )
		bb_add_error( 'bb_split_topic_source_not_found', __( '<strong>ERROR</strong>: The topic you want to split was not found.', 'bbpress' ) );

	// Nonce check failed
	if ( ! bb_verify_nonce_request( 'bbp-split-topic_' . $source_topic->ID ) ) {
		bb_add_error( 'bb_split_topic_nonce', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bbpress' ) );
		return;
	}

	// Use cannot edit topic
	if ( !current_user_can( 'edit_topic', $source_topic->ID ) )
		bb_add_error( 'bb_split_topic_source_permission', __( '<strong>ERROR</strong>: You do not have the permissions to edit the source topic.', 'bbpress' ) );

	// How to Split
	if ( !empty( $_POST['bb_topic_split_option'] ) )
		$split_option = (string) trim( $_POST['bb_topic_split_option'] );

	// Invalid split option
	if ( empty( $split_option ) || !in_array( $split_option, array( 'existing', 'reply' ) ) ) {
		bb_add_error( 'bb_split_topic_option', __( '<strong>ERROR</strong>: You need to choose a valid split option.', 'bbpress' ) );

	// Valid Split Option
	} else {

		// What kind of split
		switch ( $split_option ) {

			// Into an existing topic
			case 'existing' :

				// Get destination topic id
				if ( empty( $_POST['bb_destination_topic'] ) )
					bb_add_error( 'bb_split_topic_destination_id', __( '<strong>ERROR</strong>: Destination topic ID not found!', 'bbpress' ) );
				else
					$destination_topic_id = (int) $_POST['bb_destination_topic'];

				// Get the destination topic
				$destination_topic = bb_get_topic( $destination_topic_id );

				// No destination topic
				if ( empty( $destination_topic ) )
					bb_add_error( 'bb_split_topic_destination_not_found', __( '<strong>ERROR</strong>: The topic you want to split to was not found!', 'bbpress' ) );

				// User cannot edit the destination topic
				if ( !current_user_can( 'edit_topic', $destination_topic->ID ) )
					bb_add_error( 'bb_split_topic_destination_permission', __( '<strong>ERROR</strong>: You do not have the permissions to edit the destination topic!', 'bbpress' ) );

				break;

			// Split at reply into a new topic
			case 'reply' :
			default :

				// User needs to be able to publish topics
				if ( current_user_can( 'publish_topics' ) ) {

					// Use the new title that was passed
					if ( !empty( $_POST['bb_topic_split_destination_title'] ) ) {
						$destination_topic_title = esc_attr( strip_tags( $_POST['bb_topic_split_destination_title'] ) );

					// Use the source topic title
					} else {
						$destination_topic_title = $source_topic->post_title;
					}

					// Setup the updated topic parameters
					$postarr = array(
						'ID'          => $from_reply->ID,
						'post_title'  => $destination_topic_title,
						'post_name'   => false,
						'post_type'   => bb_get_topic_post_type(),
						'post_parent' => $source_topic->post_parent,
						'guid'        => ''
					);

					// Update the topic
					$destination_topic_id = wp_update_post( $postarr );
					$destination_topic    = bb_get_topic( $destination_topic_id );

					// Make sure the new topic knows its a topic
					bb_update_topic_topic_id( $from_reply->ID );

					// Shouldn't happen
					if ( false == $destination_topic_id || is_wp_error( $destination_topic_id ) || empty( $destination_topic ) ) {
						bb_add_error( 'bb_split_topic_destination_reply', __( '<strong>ERROR</strong>: There was a problem converting the reply into the topic. Please try again.', 'bbpress' ) );
					}

				// User cannot publish posts
				} else {
					bb_add_error( 'bb_split_topic_destination_permission', __( '<strong>ERROR</strong>: You do not have the permissions to create new topics. The reply could not be converted into a topic.', 'bbpress' ) );
				}

				break;
		}
	}

	// Bail if there are errors
	if ( bb_has_errors() )
		return;

	/** No Errors - Do the Spit ***********************************************/

	// Update counts, etc...
	do_action( 'bb_pre_split_topic', $from_reply->ID, $source_topic->ID, $destination_topic->ID );

	/** Date Check ************************************************************/

	// Check if the destination topic is older than the from reply
	if ( strtotime( $from_reply->post_date ) < strtotime( $destination_topic->post_date ) ) {

		// Set destination topic post_date to 1 second before from reply
		$destination_post_date = date( 'Y-m-d H:i:s', strtotime( $from_reply->post_date ) - 1 );

		$postarr = array(
			'ID'            => $destination_topic_id,
			'post_date'     => $destination_post_date,
			'post_date_gmt' => get_gmt_from_date( $destination_post_date )
		);

		// Update destination topic
		wp_update_post( $postarr );
	}

	/** Subscriptions *********************************************************/

	// Copy the subscribers
	if ( !empty( $_POST['bb_topic_subscribers'] ) && 1 == $_POST['bb_topic_subscribers'] && bb_is_subscriptions_active() ) {

		// Get the subscribers
		$subscribers = bb_get_topic_subscribers( $source_topic->ID );

		if ( !empty( $subscribers ) ) {

			// Add subscribers to new topic
			foreach ( (array) $subscribers as $subscriber ) {
				bb_add_user_subscription( $subscriber, $destination_topic->ID );
			}
		}
	}

	/** Favorites *************************************************************/

	// Copy the favoriters if told to
	if ( !empty( $_POST['bb_topic_favoriters'] ) && 1 == $_POST['bb_topic_favoriters'] ) {

		// Get the favoriters
		$favoriters = bb_get_topic_favoriters( $source_topic->ID );

		if ( !empty( $favoriters ) ) {

			// Add the favoriters to new topic
			foreach ( (array) $favoriters as $favoriter ) {
				bb_add_user_favorite( $favoriter, $destination_topic->ID );
			}
		}
	}

	/** Tags ******************************************************************/

	// Copy the tags if told to
	if ( !empty( $_POST['bb_topic_tags'] ) && ( 1 == $_POST['bb_topic_tags'] ) ) {

		// Get the source topic tags
		$source_topic_tags = wp_get_post_terms( $source_topic->ID, bb_get_topic_tag_tax_id(), array( 'fields' => 'names' ) );

		if ( !empty( $source_topic_tags ) ) {
			wp_set_post_terms( $destination_topic->ID, $source_topic_tags, bb_get_topic_tag_tax_id(), true );
		}
	}

	/** Split Replies *********************************************************/

	// get_posts() is not used because it doesn't allow us to use '>='
	// comparision without a filter.
	$replies = (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_parent = %d AND {$wpdb->posts}.post_type = %s ORDER BY {$wpdb->posts}.post_date ASC", $from_reply->post_date, $source_topic->ID, bb_get_reply_post_type() ) );

	// Make sure there are replies to loop through
	if ( !empty( $replies ) && !is_wp_error( $replies ) ) {

		// Calculate starting point for reply positions
		switch ( $split_option ) {

			// Get topic reply count for existing topic
			case 'existing' :
				$reply_position = bb_get_topic_reply_count( $destination_topic->ID );
				break;

			// Account for new lead topic
			case 'reply'    :
				$reply_position = 1;
				break;
		}

		// Change the post_parent of each reply to the destination topic id
		foreach ( $replies as $reply ) {

			// Bump the reply position each iteration through the loop
			$reply_position++;

			// New reply data
			$postarr = array(
				'ID'            => $reply->ID,
				'post_title'    => sprintf( __( 'Reply To: %s', 'bbpress' ), $destination_topic->post_title ),
				'post_name'     => false, // will be automatically generated
				'post_parent'   => $destination_topic->ID,
				'post_position' => $reply_position,
				'guid'          => ''
			);

			// Update the reply
			wp_update_post( $postarr );

			// Adjust reply meta values
			bb_update_reply_topic_id( $reply->ID, $destination_topic->ID                           );
			bb_update_reply_forum_id( $reply->ID, bb_get_topic_forum_id( $destination_topic->ID ) );

			// Do additional actions per split reply
			do_action( 'bb_split_topic_reply', $reply->ID, $destination_topic->ID );
		}

		// Set the last reply ID and freshness
		$last_reply_id = $reply->ID;
		$freshness     = $reply->post_date;

	// Set the last reply ID and freshness to the from_reply
	} else {
		$last_reply_id = $from_reply->ID;
		$freshness     = $from_reply->post_date;
	}

	// It is a new topic and we need to set some default metas to make
	// the topic display in bb_has_topics() list
	if ( 'reply' == $split_option ) {
		bb_update_topic_last_reply_id   ( $destination_topic->ID, $last_reply_id );
		bb_update_topic_last_active_id  ( $destination_topic->ID, $last_reply_id );
		bb_update_topic_last_active_time( $destination_topic->ID, $freshness     );
	}

	// Update source topic ID last active
	bb_update_topic_last_reply_id   ( $source_topic->ID );
	bb_update_topic_last_active_id  ( $source_topic->ID );
	bb_update_topic_last_active_time( $source_topic->ID );

	/** Successful Split ******************************************************/

	// Update counts, etc...
	do_action( 'bb_post_split_topic', $from_reply->ID, $source_topic->ID, $destination_topic->ID );

	// Redirect back to the topic
	wp_safe_redirect( bb_get_topic_permalink( $destination_topic->ID ) );

	// For good measure
	exit();
}

/**
 * Fix counts on topic split
 *
 * When a topic is split, update the counts of source and destination topic
 * and their forums.
 *
 * @since barebones (1.0)
 *
 * @param int $from_reply_id From reply id
 * @param int $source_topic_id Source topic id
 * @param int $destination_topic_id Destination topic id
 * @uses bb_update_forum_topic_count() To update the forum topic counts
 * @uses bb_update_forum_reply_count() To update the forum reply counts
 * @uses bb_update_topic_reply_count() To update the topic reply counts
 * @uses bb_update_topic_voice_count() To update the topic voice counts
 * @uses bb_update_topic_reply_count_hidden() To update the topic hidden reply
 *                                              count
 * @uses do_action() Calls 'bb_split_topic_count' with the from reply id,
 *                    source topic id & destination topic id
 */
function bb_split_topic_count( $from_reply_id, $source_topic_id, $destination_topic_id ) {

	// Forum Topic Counts
	bb_update_forum_topic_count( bb_get_topic_forum_id( $destination_topic_id ) );

	// Forum Reply Counts
	bb_update_forum_reply_count( bb_get_topic_forum_id( $destination_topic_id ) );

	// Topic Reply Counts
	bb_update_topic_reply_count( $source_topic_id      );
	bb_update_topic_reply_count( $destination_topic_id );

	// Topic Hidden Reply Counts
	bb_update_topic_reply_count_hidden( $source_topic_id      );
	bb_update_topic_reply_count_hidden( $destination_topic_id );

	// Topic Voice Counts
	bb_update_topic_voice_count( $source_topic_id      );
	bb_update_topic_voice_count( $destination_topic_id );

	do_action( 'bb_split_topic_count', $from_reply_id, $source_topic_id, $destination_topic_id );
}

/**
 * Handles the front end tag management (renaming, merging, destroying)
 *
 * @since barebones (1.0)
 *
 * @param string $action The requested action to compare this function to
 * @uses bb_verify_nonce_request() To verify the nonce and check the request
 * @uses current_user_can() To check if the current user can edit/delete tags
 * @uses bb_add_error() To add an error message
 * @uses wp_update_term() To update the topic tag
 * @uses get_term_link() To get the topic tag url
 * @uses term_exists() To check if the topic tag already exists
 * @uses wp_insert_term() To insert a topic tag
 * @uses wp_delete_term() To delete the topic tag
 * @uses home_url() To get the blog's home page url
 * @uses do_action() Calls actions based on the actions with associated args
 * @uses is_wp_error() To check if the value retrieved is a {@link WP_Error}
 * @uses wp_safe_redirect() To redirect to the url
 */
function bb_edit_topic_tag_handler( $action = '' ) {

	// Bail if required POST actions aren't passed
	if ( empty( $_POST['tag-id'] ) )
		return;

	// Setup possible get actions
	$possible_actions = array(
		'bbp-update-topic-tag',
		'bbp-merge-topic-tag',
		'bbp-delete-topic-tag'
	);

	// Bail if actions aren't meant for this function
	if ( !in_array( $action, $possible_actions ) )
		return;

	// Setup vars
	$tag_id = (int) $_POST['tag-id'];
	$tag    = get_term( $tag_id, bb_get_topic_tag_tax_id() );

	// Tag does not exist
	if ( is_wp_error( $tag ) && $tag->get_error_message() ) {
		bb_add_error( 'bb_manage_topic_invalid_tag', sprintf( __( '<strong>ERROR</strong>: The following problem(s) have been found while getting the tag: %s', 'bbpress' ), $tag->get_error_message() ) );
		return;
	}

	// What action are we trying to perform?
	switch ( $action ) {

		// Update tag
		case 'bbp-update-topic-tag' :

			// Nonce check
			if ( ! bb_verify_nonce_request( 'update-tag_' . $tag_id ) ) {
				bb_add_error( 'bb_manage_topic_tag_update_nonce', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bbpress' ) );
				return;
			}

			// Can user edit topic tags?
			if ( !current_user_can( 'edit_topic_tags' ) ) {
				bb_add_error( 'bb_manage_topic_tag_update_permissions', __( '<strong>ERROR</strong>: You do not have the permissions to edit the topic tags.', 'bbpress' ) );
				return;
			}

			// No tag name was provided
			if ( empty( $_POST['tag-name'] ) || !$name = $_POST['tag-name'] ) {
				bb_add_error( 'bb_manage_topic_tag_update_name', __( '<strong>ERROR</strong>: You need to enter a tag name.', 'bbpress' ) );
				return;
			}

			// Attempt to update the tag
			$slug = !empty( $_POST['tag-slug'] ) ? $_POST['tag-slug'] : '';
			$tag  = wp_update_term( $tag_id, bb_get_topic_tag_tax_id(), array( 'name' => $name, 'slug' => $slug ) );

			// Cannot update tag
			if ( is_wp_error( $tag ) && $tag->get_error_message() ) {
				bb_add_error( 'bb_manage_topic_tag_update_error', sprintf( __( '<strong>ERROR</strong>: The following problem(s) have been found while updating the tag: %s', 'bbpress' ), $tag->get_error_message() ) );
				return;
			}

			// Redirect
			$redirect = get_term_link( $tag_id, bb_get_topic_tag_tax_id() );

			// Update counts, etc...
			do_action( 'bb_update_topic_tag', $tag_id, $tag, $name, $slug );

			break;

		// Merge two tags
		case 'bbp-merge-topic-tag'  :

			// Nonce check
			if ( ! bb_verify_nonce_request( 'merge-tag_' . $tag_id ) ) {
				bb_add_error( 'bb_manage_topic_tag_merge_nonce', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bbpress' ) );
				return;
			}

			// Can user edit topic tags?
			if ( !current_user_can( 'edit_topic_tags' ) ) {
				bb_add_error( 'bb_manage_topic_tag_merge_permissions', __( '<strong>ERROR</strong>: You do not have the permissions to edit the topic tags.', 'bbpress' ) );
				return;
			}

			// No tag name was provided
			if ( empty( $_POST['tag-existing-name'] ) || !$name = $_POST['tag-existing-name'] ) {
				bb_add_error( 'bb_manage_topic_tag_merge_name', __( '<strong>ERROR</strong>: You need to enter a tag name.', 'bbpress' ) );
				return;
			}

			// If term does not exist, create it
			if ( !$tag = term_exists( $name, bb_get_topic_tag_tax_id() ) )
				$tag = wp_insert_term( $name, bb_get_topic_tag_tax_id() );

			// Problem inserting the new term
			if ( is_wp_error( $tag ) && $tag->get_error_message() ) {
				bb_add_error( 'bb_manage_topic_tag_merge_error', sprintf( __( '<strong>ERROR</strong>: The following problem(s) have been found while merging the tags: %s', 'bbpress' ), $tag->get_error_message() ) );
				return;
			}

			// Merging in to...
			$to_tag = $tag['term_id'];

			// Attempting to merge a tag into itself
			if ( $tag_id == $to_tag ) {
				bb_add_error( 'bb_manage_topic_tag_merge_same', __( '<strong>ERROR</strong>: The tags which are being merged can not be the same.', 'bbpress' ) );
				return;
			}

			// Delete the old term
			$tag = wp_delete_term( $tag_id, bb_get_topic_tag_tax_id(), array( 'default' => $to_tag, 'force_default' => true ) );

			// Error merging the terms
			if ( is_wp_error( $tag ) && $tag->get_error_message() ) {
				bb_add_error( 'bb_manage_topic_tag_merge_error', sprintf( __( '<strong>ERROR</strong>: The following problem(s) have been found while merging the tags: %s', 'bbpress' ), $tag->get_error_message() ) );
				return;
			}

			// Redirect
			$redirect = get_term_link( (int) $to_tag, bb_get_topic_tag_tax_id() );

			// Update counts, etc...
			do_action( 'bb_merge_topic_tag', $tag_id, $to_tag, $tag );

			break;

		// Delete tag
		case 'bbp-delete-topic-tag' :

			// Nonce check
			if ( ! bb_verify_nonce_request( 'delete-tag_' . $tag_id ) ) {
				bb_add_error( 'bb_manage_topic_tag_delete_nonce', __( '<strong>ERROR</strong>: Are you sure you wanted to do that?', 'bbpress' ) );
				return;
			}

			// Can user delete topic tags?
			if ( !current_user_can( 'delete_topic_tags' ) ) {
				bb_add_error( 'bb_manage_topic_tag_delete_permissions', __( '<strong>ERROR</strong>: You do not have the permissions to delete the topic tags.', 'bbpress' ) );
				return;
			}

			// Attempt to delete term
			$tag = wp_delete_term( $tag_id, bb_get_topic_tag_tax_id() );

			// Error deleting term
			if ( is_wp_error( $tag ) && $tag->get_error_message() ) {
				bb_add_error( 'bb_manage_topic_tag_delete_error', sprintf( __( '<strong>ERROR</strong>: The following problem(s) have been found while deleting the tag: %s', 'bbpress' ), $tag->get_error_message() ) );
				return;
			}

			// We don't have any other place to go other than home! Or we may die because of the 404 disease
			$redirect = home_url();

			// Update counts, etc...
			do_action( 'bb_delete_topic_tag', $tag_id, $tag );

			break;
	}

	/** Successful Moderation *************************************************/

	// Redirect back
	$redirect = ( !empty( $redirect ) && !is_wp_error( $redirect ) ) ? $redirect : home_url();
	wp_safe_redirect( $redirect );

	// For good measure
	exit();
}

/** Stickies ******************************************************************/

/**
 * Return sticky topics of a forum
 *
 * @since barebones (1.0)
 *
 * @param int $forum_id Optional. If not passed, super stickies are returned.
 * @uses bb_get_super_stickies() To get the super stickies
 * @uses get_post_meta() To get the forum stickies
 * @uses apply_filters() Calls 'bb_get_stickies' with the stickies and forum id
 * @return array IDs of sticky topics of a forum or super stickies
 */
function bb_get_stickies( $forum_id = 0 ) {
	$stickies = empty( $forum_id ) ? bb_get_super_stickies() : get_post_meta( $forum_id, '_bb_sticky_topics', true );
	$stickies = ( empty( $stickies ) || !is_array( $stickies ) ) ? array() : $stickies;

	return apply_filters( 'bb_get_stickies', $stickies, (int) $forum_id );
}

/**
 * Return topics stuck to front page of the forums
 *
 * @since barebones (1.0)
 *
 * @uses get_option() To get super sticky topics
 * @uses apply_filters() Calls 'bb_get_super_stickies' with the stickies
 * @return array IDs of super sticky topics
 */
function bb_get_super_stickies() {
	$stickies = get_option( '_bb_super_sticky_topics', array() );
	$stickies = ( empty( $stickies ) || !is_array( $stickies ) ) ? array() : $stickies;

	return apply_filters( 'bb_get_super_stickies', $stickies );
}

/** Topics Actions ************************************************************/

/**
 * Handles the front end opening/closing, spamming/unspamming,
 * sticking/unsticking and trashing/untrashing/deleting of topics
 *
 * @since barebones (1.0)
 *
 * @param string $action The requested action to compare this function to
 * @uses bb_get_topic() To get the topic
 * @uses current_user_can() To check if the user is capable of editing or
 *                           deleting the topic
 * @uses bb_get_topic_post_type() To get the topic post type
 * @uses check_ajax_referer() To verify the nonce and check the referer
 * @uses bb_is_topic_open() To check if the topic is open
 * @uses bb_close_topic() To close the topic
 * @uses bb_open_topic() To open the topic
 * @uses bb_is_topic_sticky() To check if the topic is a sticky
 * @uses bb_unstick_topic() To unstick the topic
 * @uses bb_stick_topic() To stick the topic
 * @uses bb_is_topic_spam() To check if the topic is marked as spam
 * @uses bb_spam_topic() To make the topic as spam
 * @uses bb_unspam_topic() To unmark the topic as spam
 * @uses wp_trash_post() To trash the topic
 * @uses wp_untrash_post() To untrash the topic
 * @uses wp_delete_post() To delete the topic
 * @uses do_action() Calls 'bb_toggle_topic_handler' with success, post data
 *                    and action
 * @uses bb_get_forum_permalink() To get the forum link
 * @uses bb_get_topic_permalink() To get the topic link
 * @uses add_query_arg() To add args to the url
 * @uses wp_safe_redirect() To redirect to the topic
 * @uses bbPress::errors:add() To log the error messages
 */
function bb_toggle_topic_handler( $action = '' ) {

	// Bail if required GET actions aren't passed
	if ( empty( $_GET['topic_id'] ) )
		return;

	// Setup possible get actions
	$possible_actions = array(
		'bb_toggle_topic_close',
		'bb_toggle_topic_stick',
		'bb_toggle_topic_spam',
		'bb_toggle_topic_trash'
	);

	// Bail if actions aren't meant for this function
	if ( !in_array( $action, $possible_actions ) )
		return;

	$failure   = '';                         // Empty failure string
	$view_all  = false;                      // Assume not viewing all
	$topic_id  = (int) $_GET['topic_id'];    // What's the topic id?
	$success   = false;                      // Flag
	$post_data = array( 'ID' => $topic_id ); // Prelim array
	$redirect  = '';                         // Empty redirect URL

	// Make sure topic exists
	$topic = bb_get_topic( $topic_id );
	if ( empty( $topic ) )
		return;

	// What is the user doing here?
	if ( !current_user_can( 'edit_topic', $topic->ID ) || ( 'bb_toggle_topic_trash' == $action && !current_user_can( 'delete_topic', $topic->ID ) ) ) {
		bb_add_error( 'bb_toggle_topic_permission', __( '<strong>ERROR:</strong> You do not have the permission to do that.', 'bbpress' ) );
		return;
	}

	// What action are we trying to perform?
	switch ( $action ) {

		// Toggle open/close
		case 'bb_toggle_topic_close' :
			check_ajax_referer( 'close-topic_' . $topic_id );

			$is_open = bb_is_topic_open( $topic_id );
			$success = $is_open ? bb_close_topic( $topic_id ) : bb_open_topic( $topic_id );
			$failure = $is_open ? __( '<strong>ERROR</strong>: There was a problem closing the topic.', 'bbpress' ) : __( '<strong>ERROR</strong>: There was a problem opening the topic.', 'bbpress' );

			break;

		// Toggle sticky/super-sticky/unstick
		case 'bb_toggle_topic_stick' :
			check_ajax_referer( 'stick-topic_' . $topic_id );

			$is_sticky = bb_is_topic_sticky( $topic_id );
			$is_super  = ( empty( $is_sticky ) && !empty( $_GET['super'] ) && 1 == (int) $_GET['super'] ) ? true : false;
			$success   = $is_sticky ? bb_unstick_topic( $topic_id ) : bb_stick_topic( $topic_id, $is_super );
			$failure   = $is_sticky ? __( '<strong>ERROR</strong>: There was a problem unsticking the topic.', 'bbpress' ) : __( '<strong>ERROR</strong>: There was a problem sticking the topic.', 'bbpress' );

			break;

		// Toggle spam
		case 'bb_toggle_topic_spam' :
			check_ajax_referer( 'spam-topic_' . $topic_id );

			$is_spam  = bb_is_topic_spam( $topic_id );
			$success  = $is_spam ? bb_unspam_topic( $topic_id ) : bb_spam_topic( $topic_id );
			$failure  = $is_spam ? __( '<strong>ERROR</strong>: There was a problem unmarking the topic as spam.', 'bbpress' ) : __( '<strong>ERROR</strong>: There was a problem marking the topic as spam.', 'bbpress' );
			$view_all = !$is_spam;

			break;

		// Toggle trash
		case 'bb_toggle_topic_trash' :

			$sub_action = in_array( $_GET['sub_action'], array( 'trash', 'untrash', 'delete' ) ) ? $_GET['sub_action'] : false;

			if ( empty( $sub_action ) )
				break;

			switch ( $sub_action ) {
				case 'trash':
					check_ajax_referer( 'trash-' . bb_get_topic_post_type() . '_' . $topic_id );

					$view_all = true;
					$success  = wp_trash_post( $topic_id );
					$failure  = __( '<strong>ERROR</strong>: There was a problem trashing the topic.', 'bbpress' );

					break;

				case 'untrash':
					check_ajax_referer( 'untrash-' . bb_get_topic_post_type() . '_' . $topic_id );

					$success = wp_untrash_post( $topic_id );
					$failure = __( '<strong>ERROR</strong>: There was a problem untrashing the topic.', 'bbpress' );

					break;

				case 'delete':
					check_ajax_referer( 'delete-' . bb_get_topic_post_type() . '_' . $topic_id );

					$success = wp_delete_post( $topic_id );
					$failure = __( '<strong>ERROR</strong>: There was a problem deleting the topic.', 'bbpress' );

					break;
			}

			break;
	}

	// Do additional topic toggle actions
	do_action( 'bb_toggle_topic_handler', $success, $post_data, $action );

	// No errors
	if ( false != $success && !is_wp_error( $success ) ) {

		// Redirect back to the topic's forum
		if ( isset( $sub_action ) && ( 'delete' == $sub_action ) ) {
			$redirect = bb_get_forum_permalink( $success->post_parent );

		// Redirect back to the topic
		} else {

			// Get the redirect detination
			$permalink = bb_get_topic_permalink( $topic_id );
			$redirect  = bb_add_view_all( $permalink, $view_all );
		}

		wp_safe_redirect( $redirect );

		// For good measure
		exit();

	// Handle errors
	} else {
		bb_add_error( 'bb_toggle_topic', $failure );
	}
}

/** Favorites & Subscriptions *************************************************/

/**
 * Remove a deleted topic from all users' favorites
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Topic ID to remove
 * @uses bb_get_topic_favoriters() To get the topic's favoriters
 * @uses bb_remove_user_favorite() To remove the topic from user's favorites
 */
function bb_remove_topic_from_all_favorites( $topic_id = 0 ) {
	$topic_id = bb_get_topic_id( $topic_id );

	// Bail if no topic
	if ( empty( $topic_id ) )
		return;

	// Get users
	$users = (array) bb_get_topic_favoriters( $topic_id );

	// Users exist
	if ( !empty( $users ) ) {

		// Loop through users
		foreach ( $users as $user ) {

			// Remove each user
			bb_remove_user_favorite( $user, $topic_id );
		}
	}
}

/**
 * Remove a deleted topic from all users' subscriptions
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Topic ID to remove
 * @uses bb_is_subscriptions_active() To check if the subscriptions are active
 * @uses bb_get_topic_subscribers() To get the topic subscribers
 * @uses bb_remove_user_subscription() To remove the user subscription
 */
function bb_remove_topic_from_all_subscriptions( $topic_id = 0 ) {

	// Subscriptions are not active
	if ( !bb_is_subscriptions_active() )
		return;

	$topic_id = bb_get_topic_id( $topic_id );

	// Bail if no topic
	if ( empty( $topic_id ) )
		return;

	// Get users
	$users = (array) bb_get_topic_subscribers( $topic_id );

	// Users exist
	if ( !empty( $users ) ) {

		// Loop through users
		foreach ( $users as $user ) {

			// Remove each user
			bb_remove_user_subscription( $user, $topic_id );
		}
	}
}

/** Count Bumpers *************************************************************/

/**
 * Bump the total reply count of a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Forum id.
 * @param int $difference Optional. Default 1
 * @param bool $update_ancestors Optional. Default true
 * @uses bb_get_topic_id() To get the topic id
 * @uses update_post_meta() To update the topic's reply count meta
 * @uses apply_filters() Calls 'bb_bump_topic_reply_count' with the reply
 *                        count, topic id, and difference
 * @return int Forum reply count
 */
function bb_bump_topic_reply_count( $topic_id = 0, $difference = 1 ) {

	// Get counts
	$topic_id    = bb_get_topic_id( $topic_id );
	$reply_count = bb_get_topic_reply_count( $topic_id, true );
	$new_count   = (int) $reply_count + (int) $difference;

	// Update this topic id's reply count
	update_post_meta( $topic_id, '_bb_reply_count', (int) $new_count );

	return (int) apply_filters( 'bb_bump_topic_reply_count', (int) $new_count, $topic_id, (int) $difference );
}

/**
 * Bump the total hidden reply count of a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Forum id.
 * @param int $difference Optional. Default 1
 * @uses bb_get_topic_id() To get the topic id
 * @uses update_post_meta() To update the topic's reply count meta
 * @uses apply_filters() Calls 'bb_bump_topic_reply_count_hidden' with the
 *                        reply count, topic id, and difference
 * @return int Forum hidden reply count
 */
function bb_bump_topic_reply_count_hidden( $topic_id = 0, $difference = 1 ) {

	// Get counts
	$topic_id    = bb_get_topic_id( $topic_id );
	$reply_count = bb_get_topic_reply_count_hidden( $topic_id, true );
	$new_count   = (int) $reply_count + (int) $difference;

	// Update this topic id's hidder reply count
	update_post_meta( $topic_id, '_bb_reply_count_hidden', (int) $new_count );

	return (int) apply_filters( 'bb_bump_topic_reply_count_hidden', (int) $new_count, $topic_id, (int) $difference );
}

/** Topic Updaters ************************************************************/

/**
 * Update the topic's forum id
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @param int $forum_id Optional. Forum id
 * @uses bb_is_reply() TO check if the passed topic id is a reply
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses get_post_field() To get the post parent of the topic id
 * @uses bb_get_forum_id() To get the forum id
 * @uses update_post_meta() To update the topic forum id meta
 * @uses apply_filters() Calls 'bb_update_topic_forum_id' with the forum id
 *                        and topic id
 * @return int Forum id
 */
function bb_update_topic_forum_id( $topic_id = 0, $forum_id = 0 ) {

	// If it's a reply, then get the parent (topic id)
	if ( bb_is_reply( $topic_id ) ) {
		$topic_id = bb_get_reply_topic_id( $topic_id );
	} else {
		$topic_id = bb_get_topic_id( $topic_id );
	}

	if ( empty( $forum_id ) ) {
		$forum_id = get_post_field( 'post_parent', $topic_id );
	}

	update_post_meta( $topic_id, '_bb_forum_id', (int) $forum_id );

	return apply_filters( 'bb_update_topic_forum_id', (int) $forum_id, $topic_id );
}

/**
 * Update the topic's topic id
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @uses bb_get_topic_id() To get the topic id
 * @uses update_post_meta() To update the topic's topic id meta
 * @uses apply_filters() Calls 'bb_update_topic_topic_id' with the topic id
 * @return int Topic id
 */
function bb_update_topic_topic_id( $topic_id = 0 ) {
	$topic_id = bb_get_topic_id( $topic_id );

	update_post_meta( $topic_id, '_bb_topic_id', (int) $topic_id );

	return apply_filters( 'bb_update_topic_topic_id', (int) $topic_id );
}

/**
 * Adjust the total reply count of a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @param int $reply_count Optional. Set the reply count manually.
 * @uses bb_is_reply() To check if the passed topic id is a reply
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses bb_get_public_child_count() To get the reply count
 * @uses update_post_meta() To update the topic reply count meta
 * @uses apply_filters() Calls 'bb_update_topic_reply_count' with the reply
 *                        count and topic id
 * @return int Topic reply count
 */
function bb_update_topic_reply_count( $topic_id = 0, $reply_count = 0 ) {

	// If it's a reply, then get the parent (topic id)
	if ( bb_is_reply( $topic_id ) ) {
		$topic_id = bb_get_reply_topic_id( $topic_id );
	} else {
		$topic_id = bb_get_topic_id( $topic_id );
	}

	// Get replies of topic if not passed
	if ( empty( $reply_count ) ) {
		$reply_count = bb_get_public_child_count( $topic_id, bb_get_reply_post_type() );
	}

	update_post_meta( $topic_id, '_bb_reply_count', (int) $reply_count );

	return apply_filters( 'bb_update_topic_reply_count', (int) $reply_count, $topic_id );
}

/**
 * Adjust the total hidden reply count of a topic (hidden includes trashed and spammed replies)
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @param int $reply_count Optional. Set the reply count manually
 * @uses bb_is_reply() To check if the passed topic id is a reply
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses wpdb::prepare() To prepare our sql query
 * @uses wpdb::get_var() To execute our query and get the var back
 * @uses update_post_meta() To update the topic hidden reply count meta
 * @uses apply_filters() Calls 'bb_update_topic_reply_count_hidden' with the
 *                        hidden reply count and topic id
 * @return int Topic hidden reply count
 */
function bb_update_topic_reply_count_hidden( $topic_id = 0, $reply_count = 0 ) {
	global $wpdb;

	// If it's a reply, then get the parent (topic id)
	if ( bb_is_reply( $topic_id ) ) {
		$topic_id = bb_get_reply_topic_id( $topic_id );
	} else {
		$topic_id = bb_get_topic_id( $topic_id );
	}

	// Get replies of topic
	if ( empty( $reply_count ) ) {
		$reply_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_parent = %d AND post_status IN ( '" . join( '\',\'', array( bb_get_trash_status_id(), bb_get_spam_status_id() ) ) . "') AND post_type = '%s';", $topic_id, bb_get_reply_post_type() ) );
	}

	update_post_meta( $topic_id, '_bb_reply_count_hidden', (int) $reply_count );

	return apply_filters( 'bb_update_topic_reply_count_hidden', (int) $reply_count, $topic_id );
}

/**
 * Update the topic with the last active post ID
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @param int $active_id Optional. active id
 * @uses bb_is_reply() To check if the passed topic id is a reply
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses bb_get_public_child_last_id() To get the last public reply id
 * @uses bb_get_active_id() To get the active id
 * @uses update_post_meta() To update the topic last active id meta
 * @uses apply_filters() Calls 'bb_update_topic_last_active_id' with the active
 *                        id and topic id
 * @return int Active id
 */
function bb_update_topic_last_active_id( $topic_id = 0, $active_id = 0 ) {

	// If it's a reply, then get the parent (topic id)
	if ( bb_is_reply( $topic_id ) ) {
		$topic_id = bb_get_reply_topic_id( $topic_id );
	} else {
		$topic_id = bb_get_topic_id( $topic_id );
	}

	if ( empty( $active_id ) ) {
		$active_id = bb_get_public_child_last_id( $topic_id, bb_get_reply_post_type() );
	}

	// Adjust last_id's based on last_reply post_type
	if ( empty( $active_id ) || !bb_is_reply( $active_id ) ) {
		$active_id = $topic_id;
	}

	// Update only if published
	if ( bb_get_public_status_id() == get_post_status( $active_id ) ) {
		update_post_meta( $topic_id, '_bb_last_active_id', (int) $active_id );
	}

	return apply_filters( 'bb_update_topic_last_active_id', (int) $active_id, $topic_id );
}

/**
 * Update the topics last active date/time (aka freshness)
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id
 * @param string $new_time Optional. New time in mysql format
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses current_time() To get the current time
 * @uses update_post_meta() To update the topic last active meta
 * @return bool True on success, false on failure
 */
function bb_update_topic_last_active_time( $topic_id = 0, $new_time = '' ) {

	// If it's a reply, then get the parent (topic id)
	if ( bb_is_reply( $topic_id ) ) {
		$topic_id = bb_get_reply_topic_id( $topic_id );
	} else {
		$topic_id = bb_get_topic_id( $topic_id );
	}

	// Check time and use current if empty
	if ( empty( $new_time ) ) {
		$new_time = get_post_field( 'post_date', bb_get_public_child_last_id( $topic_id, bb_get_reply_post_type() ) );
	}

	// Update only if published
	if ( !empty( $new_time ) ) {
		update_post_meta( $topic_id, '_bb_last_active_time', $new_time );
	}

	return apply_filters( 'bb_update_topic_last_active_time', $new_time, $topic_id );
}

/**
 * Update the topic with the most recent reply ID
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @param int $reply_id Optional. Reply id
 * @uses bb_is_reply() To check if the passed topic id is a reply
 * @uses bb_get_reply_id() To get the reply id
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses bb_get_public_child_last_id() To get the last public reply id
 * @uses update_post_meta() To update the topic last reply id meta
 * @uses apply_filters() Calls 'bb_update_topic_last_reply_id' with the reply
 *                        id and topic id
 * @return int Reply id
 */
function bb_update_topic_last_reply_id( $topic_id = 0, $reply_id = 0 ) {

	// If it's a reply, then get the parent (topic id)
	if ( empty( $reply_id ) && bb_is_reply( $topic_id ) ) {
		$reply_id = bb_get_reply_id( $topic_id );
		$topic_id = bb_get_reply_topic_id( $reply_id );
	} else {
		$reply_id = bb_get_reply_id( $reply_id );
		$topic_id = bb_get_topic_id( $topic_id );
	}

	if ( empty( $reply_id ) ) {
		$reply_id = bb_get_public_child_last_id( $topic_id, bb_get_reply_post_type() );
	}

	// Adjust last_id's based on last_reply post_type
	if ( empty( $reply_id ) || !bb_is_reply( $reply_id ) ) {
		$reply_id = 0;
	}

	// Update if reply is published
	if ( bb_is_reply_published( $reply_id ) ) {
		update_post_meta( $topic_id, '_bb_last_reply_id', (int) $reply_id );
	}

	return apply_filters( 'bb_update_topic_last_reply_id', (int) $reply_id, $topic_id );
}

/**
 * Adjust the total voice count of a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @uses bb_is_reply() To check if the passed topic id is a reply
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses bb_get_topic_post_type() To get the topic post type
 * @uses wpdb::prepare() To prepare our sql query
 * @uses wpdb::get_col() To execute our query and get the column back
 * @uses update_post_meta() To update the topic voice count meta
 * @uses apply_filters() Calls 'bb_update_topic_voice_count' with the voice
 *                        count and topic id
 * @return int Voice count
 */
function bb_update_topic_voice_count( $topic_id = 0 ) {
	global $wpdb;

	// If it's a reply, then get the parent (topic id)
	if ( bb_is_reply( $topic_id ) ) {
		$topic_id = bb_get_reply_topic_id( $topic_id );
	} elseif ( bb_is_topic( $topic_id ) ) {
		$topic_id = bb_get_topic_id( $topic_id );
	} else {
		return;
	}

	// Query the DB to get voices in this topic
	$voices = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT( DISTINCT post_author ) FROM {$wpdb->posts} WHERE ( post_parent = %d AND post_status = '%s' AND post_type = '%s' ) OR ( ID = %d AND post_type = '%s' );", $topic_id, bb_get_public_status_id(), bb_get_reply_post_type(), $topic_id, bb_get_topic_post_type() ) );

	// If there's an error, make sure we have at least have 1 voice
	$voices = ( empty( $voices ) || is_wp_error( $voices ) ) ? 1 : $voices[0];

	// Update the voice count for this topic id
	update_post_meta( $topic_id, '_bb_voice_count', (int) $voices );

	return apply_filters( 'bb_update_topic_voice_count', (int) $voices, $topic_id );
}

/**
 * Adjust the total anonymous reply count of a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id to update
 * @uses bb_is_reply() To check if the passed topic id is a reply
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_get_reply_topic_id() To get the reply topic id
 * @uses bb_get_reply_post_type() To get the reply post type
 * @uses bb_get_topic_post_type() To get the topic post type
 * @uses wpdb::prepare() To prepare our sql query
 * @uses wpdb::get_col() To execute our query and get the column back
 * @uses update_post_meta() To update the topic anonymous reply count meta
 * @uses apply_filters() Calls 'bb_update_topic_anonymous_reply_count' with the
 *                        anonymous reply count and topic id
 * @return int Anonymous reply count
 */
function bb_update_topic_anonymous_reply_count( $topic_id = 0 ) {
	global $wpdb;

	// If it's a reply, then get the parent (topic id)
	if ( bb_is_reply( $topic_id ) ) {
		$topic_id = bb_get_reply_topic_id( $topic_id );
	} elseif ( bb_is_topic( $topic_id ) ) {
		$topic_id = bb_get_topic_id( $topic_id );
	} else {
		return;
	}

	$anonymous_replies = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( ID ) FROM {$wpdb->posts} WHERE ( post_parent = %d AND post_status = '%s' AND post_type = '%s' AND post_author = 0 ) OR ( ID = %d AND post_type = '%s' AND post_author = 0 );", $topic_id, bb_get_public_status_id(), bb_get_reply_post_type(), $topic_id, bb_get_topic_post_type() ) );

	update_post_meta( $topic_id, '_bb_anonymous_reply_count', (int) $anonymous_replies );

	return apply_filters( 'bb_update_topic_anonymous_reply_count', (int) $anonymous_replies, $topic_id );
}

/**
 * Update the revision log of the topic
 *
 * @since barebones (1.0)
 *
 * @param mixed $args Supports these args:
 *  - topic_id: Topic id
 *  - author_id: Author id
 *  - reason: Reason for editing
 *  - revision_id: Revision id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_format_revision_reason() To format the reason
 * @uses bb_get_topic_raw_revision_log() To get the raw topic revision log
 * @uses update_post_meta() To update the topic revision log meta
 * @return mixed False on failure, true on success
 */
function bb_update_topic_revision_log( $args = '' ) {

	// Parse arguments against default values
	$r = bb_parse_args( $args, array(
		'reason'      => '',
		'topic_id'    => 0,
		'author_id'   => 0,
		'revision_id' => 0
	), 'update_topic_revision_log' );

	// Populate the variables
	$r['reason']      = bb_format_revision_reason( $r['reason'] );
	$r['topic_id']    = bb_get_topic_id( $r['topic_id'] );
	$r['author_id']   = bb_get_user_id ( $r['author_id'], false, true );
	$r['revision_id'] = (int) $r['revision_id'];

	// Get the logs and append the new one to those
	$revision_log                      = bb_get_topic_raw_revision_log( $r['topic_id'] );
	$revision_log[ $r['revision_id'] ] = array( 'author' => $r['author_id'], 'reason' => $r['reason'] );

	// Finally, update
	return update_post_meta( $r['topic_id'], '_bb_revision_log', $revision_log );
}

/** Topic Actions *************************************************************/

/**
 * Closes a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Topic id
 * @uses get_post() To get the topic
 * @uses do_action() Calls 'bb_close_topic' with the topic id
 * @uses add_post_meta() To add the previous status to a meta
 * @uses wp_insert_post() To update the topic with the new status
 * @uses do_action() Calls 'bb_opened_topic' with the topic id
 * @return mixed False or {@link WP_Error} on failure, topic id on success
 */
function bb_close_topic( $topic_id = 0 ) {

	// Get topic
	if ( !$topic = get_post( $topic_id, ARRAY_A ) )
		return $topic;

	// Bail if already closed
	if ( bb_get_closed_status_id() == $topic['post_status'] )
		return false;

	// Execute pre close code
	do_action( 'bb_close_topic', $topic_id );

	// Add pre close status
	add_post_meta( $topic_id, '_bb_status', $topic['post_status'] );

	// Set closed status
	$topic['post_status'] = bb_get_closed_status_id();

	// No revisions
	remove_action( 'pre_post_update', 'wp_save_post_revision' );

	// Update topic
	$topic_id = wp_insert_post( $topic );

	// Execute post close code
	do_action( 'bb_closed_topic', $topic_id );

	// Return topic_id
	return $topic_id;
}

/**
 * Opens a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Topic id
 * @uses get_post() To get the topic
 * @uses do_action() Calls 'bb_open_topic' with the topic id
 * @uses get_post_meta() To get the previous status
 * @uses delete_post_meta() To delete the previous status meta
 * @uses wp_insert_post() To update the topic with the new status
 * @uses do_action() Calls 'bb_opened_topic' with the topic id
 * @return mixed False or {@link WP_Error} on failure, topic id on success
 */
function bb_open_topic( $topic_id = 0 ) {

	// Get topic
	if ( !$topic = get_post( $topic_id, ARRAY_A ) )
		return $topic;

	// Bail if already open
	if ( bb_get_closed_status_id() != $topic['post_status'])
		return false;

	// Execute pre open code
	do_action( 'bb_open_topic', $topic_id );

	// Get previous status
	$topic_status         = get_post_meta( $topic_id, '_bb_status', true );

	// Set previous status
	$topic['post_status'] = $topic_status;

	// Remove old status meta
	delete_post_meta( $topic_id, '_bb_status' );

	// No revisions
	remove_action( 'pre_post_update', 'wp_save_post_revision' );

	// Update topic
	$topic_id = wp_insert_post( $topic );

	// Execute post open code
	do_action( 'bb_opened_topic', $topic_id );

	// Return topic_id
	return $topic_id;
}

/**
 * Marks a topic as spam
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Topic id
 * @uses get_post() To get the topic
 * @uses do_action() Calls 'bb_spam_topic' with the topic id
 * @uses add_post_meta() To add the previous status to a meta
 * @uses wp_insert_post() To update the topic with the new status
 * @uses do_action() Calls 'bb_spammed_topic' with the topic id
 * @return mixed False or {@link WP_Error} on failure, topic id on success
 */
function bb_spam_topic( $topic_id = 0 ) {

	// Get the topic
	if ( !$topic = get_post( $topic_id, ARRAY_A ) )
		return $topic;

	// Bail if topic is spam
	if ( bb_get_spam_status_id() == $topic['post_status'] )
		return false;

	// Execute pre spam code
	do_action( 'bb_spam_topic', $topic_id );

	/** Trash Replies *********************************************************/

	// Topic is being spammed, so its replies are trashed
	$replies = new WP_Query( array(
		'suppress_filters' => true,
		'post_type'        => bb_get_reply_post_type(),
		'post_status'      => bb_get_public_status_id(),
		'post_parent'      => $topic_id,
		'posts_per_page'   => -1,
		'nopaging'         => true,
		'fields'           => 'id=>parent'
	) );

	if ( !empty( $replies->posts ) ) {

		// Prevent debug notices
		$pre_spammed_replies = array();

		// Loop through replies, trash them, and add them to array
		foreach ( $replies->posts as $reply ) {
			wp_trash_post( $reply->ID );
			$pre_spammed_replies[] = $reply->ID;
		}

		// Set a post_meta entry of the replies that were trashed by this action.
		// This is so we can possibly untrash them, without untrashing replies
		// that were purposefully trashed before.
		update_post_meta( $topic_id, '_bb_pre_spammed_replies', $pre_spammed_replies );

		// Reset the $post global
		wp_reset_postdata();
	}

	// Cleanup
	unset( $replies );

	/** Topic Tags ************************************************************/

	// Add the original post status as post meta for future restoration
	add_post_meta( $topic_id, '_bb_spam_meta_status', $topic['post_status'] );

	// Get topic tags
	$terms = get_the_terms( $topic_id, bb_get_topic_tag_tax_id() );

	// Define local variable(s)
	$term_names = array();

	// Topic has tags
	if ( !empty( $terms ) ) {

		// Loop through and collect term names
		foreach( $terms as $term ) {
			$term_names[] = $term->name;
		}

		// Topic terms have slugs
		if ( !empty( $term_names ) ) {

			// Add the original post status as post meta for future restoration
			add_post_meta( $topic_id, '_bb_spam_topic_tags', $term_names );

			// Empty the topic of its tags
			$topic['tax_input'] = array( bb_get_topic_tag_tax_id() => '' );
		}
	}

	// Set post status to spam
	$topic['post_status'] = bb_get_spam_status_id();

	// No revisions
	remove_action( 'pre_post_update', 'wp_save_post_revision' );

	// Update the topic
	$topic_id = wp_insert_post( $topic );

	// Execute post spam code
	do_action( 'bb_spammed_topic', $topic_id );

	// Return topic_id
	return $topic_id;
}

/**
 * Unspams a topic
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Topic id
 * @uses get_post() To get the topic
 * @uses do_action() Calls 'bb_unspam_topic' with the topic id
 * @uses get_post_meta() To get the previous status
 * @uses delete_post_meta() To delete the previous status meta
 * @uses wp_insert_post() To update the topic with the new status
 * @uses do_action() Calls 'bb_unspammed_topic' with the topic id
 * @return mixed False or {@link WP_Error} on failure, topic id on success
 */
function bb_unspam_topic( $topic_id = 0 ) {

	// Get the topic
	if ( !$topic = get_post( $topic_id, ARRAY_A ) )
		return $topic;

	// Bail if already not spam
	if ( bb_get_spam_status_id() != $topic['post_status'] )
		return false;

	// Execute pre unspam code
	do_action( 'bb_unspam_topic', $topic_id );

	/** Untrash Replies *******************************************************/

	// Get the replies that were not previously trashed
	$pre_spammed_replies = get_post_meta( $topic_id, '_bb_pre_spammed_replies', true );

	// There are replies to untrash
	if ( !empty( $pre_spammed_replies ) ) {

		// Maybe reverse the trashed replies array
		if ( is_array( $pre_spammed_replies ) )
			$pre_spammed_replies = array_reverse( $pre_spammed_replies );

		// Loop through replies
		foreach ( (array) $pre_spammed_replies as $reply ) {
			wp_untrash_post( $reply );
		}
	}

	/** Topic Tags ************************************************************/

	// Get pre-spam topic tags
	$terms = get_post_meta( $topic_id, '_bb_spam_topic_tags', true );

	// Topic had tags before it was spammed
	if ( !empty( $terms ) ) {

		// Set the tax_input of the topic
		$topic['tax_input'] = array( bb_get_topic_tag_tax_id() => $terms );

		// Delete pre-spam topic tag meta
		delete_post_meta( $topic_id, '_bb_spam_topic_tags' );
	}

	/** Topic Status **********************************************************/

	// Get pre spam status
	$topic_status = get_post_meta( $topic_id, '_bb_spam_meta_status', true );

	// If no previous status, default to publish
	if ( empty( $topic_status ) ) {
		$topic_status = bb_get_public_status_id();
	}

	// Set post status to pre spam
	$topic['post_status'] = $topic_status;

	// Delete pre spam meta
	delete_post_meta( $topic_id, '_bb_spam_meta_status' );

	// No revisions
	remove_action( 'pre_post_update', 'wp_save_post_revision' );

	// Update the topic
	$topic_id = wp_insert_post( $topic );

	// Execute post unspam code
	do_action( 'bb_unspammed_topic', $topic_id );

	// Return topic_id
	return $topic_id;
}

/**
 * Sticks a topic to a forum or front
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id
 * @param int $super Should we make the topic a super sticky?
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_unstick_topic() To unstick the topic
 * @uses bb_get_topic_forum_id() To get the topic forum id
 * @uses bb_get_stickies() To get the stickies
 * @uses do_action() 'bb_stick_topic' with topic id and bool super
 * @uses update_option() To update the super stickies option
 * @uses update_post_meta() To update the forum stickies meta
 * @uses do_action() Calls 'bb_sticked_topic' with the topic id, bool super
 *                    and success
 * @return bool True on success, false on failure
 */
function bb_stick_topic( $topic_id = 0, $super = false ) {
	$topic_id = bb_get_topic_id( $topic_id );

	// Bail if a topic is not a topic (prevents revisions as stickies)
	if ( ! bb_is_topic( $topic_id ) )
		return false;

	// We may have a super sticky to which we want to convert into a normal
	// sticky and vice versa; unstick the topic first to avoid any possible error.
	bb_unstick_topic( $topic_id );

	$forum_id = empty( $super ) ? bb_get_topic_forum_id( $topic_id ) : 0;
	$stickies = bb_get_stickies( $forum_id );

	do_action( 'bb_stick_topic', $topic_id, $super );

	if ( !is_array( $stickies ) ) {
		$stickies   = array( $topic_id );
	} else {
		$stickies[] = $topic_id;
	}

	// Pull out duplicates and empties
	$stickies = array_unique( array_filter( $stickies ) );

	// Unset incorrectly stuck revisions
	foreach ( (array) $stickies as $key => $id ) {
		if ( ! bb_is_topic( $id ) ) {
			unset( $stickies[$key] );
		}
	}

	// Reset keys
	$stickies = array_values( $stickies );
	$success  = !empty( $super ) ? update_option( '_bb_super_sticky_topics', $stickies ) : update_post_meta( $forum_id, '_bb_sticky_topics', $stickies );

	do_action( 'bb_sticked_topic', $topic_id, $super, $success );

	return (bool) $success;
}

/**
 * Unsticks a topic both from front and it's forum
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id Optional. Topic id
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_is_topic_super_sticky() To check if the topic is a super sticky
 * @uses bb_get_topic_forum_id() To get the topic forum id
 * @uses bb_get_stickies() To get the forum stickies
 * @uses do_action() Calls 'bb_unstick_topic' with the topic id
 * @uses delete_option() To delete the super stickies option
 * @uses update_option() To update the super stickies option
 * @uses delete_post_meta() To delete the forum stickies meta
 * @uses update_post_meta() To update the forum stickies meta
 * @uses do_action() Calls 'bb_unsticked_topic' with the topic id and success
 * @return bool Always true.
 */
function bb_unstick_topic( $topic_id = 0 ) {
	$topic_id = bb_get_topic_id( $topic_id );
	$super    = bb_is_topic_super_sticky( $topic_id );
	$forum_id = empty( $super ) ? bb_get_topic_forum_id( $topic_id ) : 0;
	$stickies = bb_get_stickies( $forum_id );
	$offset   = array_search( $topic_id, $stickies );

	do_action( 'bb_unstick_topic', $topic_id );

	if ( empty( $stickies ) ) {
		$success = true;
	} elseif ( !in_array( $topic_id, $stickies ) ) {
		$success = true;
	} elseif ( false === $offset ) {
		$success = true;
	} else {
		array_splice( $stickies, $offset, 1 );
		if ( empty( $stickies ) ) {
			$success = !empty( $super ) ? delete_option( '_bb_super_sticky_topics'            ) : delete_post_meta( $forum_id, '_bb_sticky_topics'            );
		} else {
			$success = !empty( $super ) ? update_option( '_bb_super_sticky_topics', $stickies ) : update_post_meta( $forum_id, '_bb_sticky_topics', $stickies );
		}
	}

	do_action( 'bb_unsticked_topic', $topic_id, $success );

	return (bool) $success;
}

/** Before Delete/Trash/Untrash ***********************************************/

/**
 * Called before deleting a topic.
 *
 * This function is supplemental to the actual topic deletion which is
 * handled by WordPress core API functions. It is used to clean up after
 * a topic that is being deleted.
 *
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_is_topic() To check if the passed id is a topic
 * @uses do_action() Calls 'bb_delete_topic' with the topic id
 * @uses bb_has_replies() To check if the topic has replies
 * @uses bb_replies() To loop through the replies
 * @uses bb_the_reply() To set a reply as the current reply in the loop
 * @uses bb_get_reply_id() To get the reply id
 * @uses wp_delete_post() To delete the reply
 */
function bb_delete_topic( $topic_id = 0 ) {

	// Validate topic ID
	$topic_id = bb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) || !bb_is_topic( $topic_id ) )
		return false;

	do_action( 'bb_delete_topic', $topic_id );

	// Topic is being permanently deleted, so its replies gotta go too
	// Note that we get all post statuses here
	$replies = new WP_Query( array(
		'suppress_filters' => true,
		'post_type'        => bb_get_reply_post_type(),
		'post_status'      => array_keys( get_post_stati() ),
		'post_parent'      => $topic_id,
		'posts_per_page'   => -1,
		'nopaging'         => true,
		'fields'           => 'id=>parent'
	) );

	// Loop through and delete child replies
	if ( ! empty( $replies->posts ) ) {
		foreach ( $replies->posts as $reply ) {
			wp_delete_post( $reply->ID, true );
		}

		// Reset the $post global
		wp_reset_postdata();
	}

	// Cleanup
	unset( $replies );
}

/**
 * Called before trashing a topic
 *
 * This function is supplemental to the actual topic being trashed which is
 * handled by WordPress core API functions. It is used to clean up after
 * a topic that is being trashed.
 *
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_is_topic() To check if the passed id is a topic
 * @uses do_action() Calls 'bb_trash_topic' with the topic id
 * @uses wp_trash_post() To trash the reply
 * @uses update_post_meta() To save a list of just trashed replies for future use
 */
function bb_trash_topic( $topic_id = 0 ) {

	// Validate topic ID
	$topic_id = bb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) || !bb_is_topic( $topic_id ) )
		return false;

	do_action( 'bb_trash_topic', $topic_id );

	// Topic is being trashed, so its replies are trashed too
	$replies = new WP_Query( array(
		'suppress_filters' => true,
		'post_type'        => bb_get_reply_post_type(),
		'post_status'      => bb_get_public_status_id(),
		'post_parent'      => $topic_id,
		'posts_per_page'   => -1,
		'nopaging'         => true,
		'fields'           => 'id=>parent'
	) );

	if ( !empty( $replies->posts ) ) {

		// Prevent debug notices
		$pre_trashed_replies = array();

		// Loop through replies, trash them, and add them to array
		foreach ( $replies->posts as $reply ) {
			wp_trash_post( $reply->ID );
			$pre_trashed_replies[] = $reply->ID;
		}

		// Set a post_meta entry of the replies that were trashed by this action.
		// This is so we can possibly untrash them, without untrashing replies
		// that were purposefully trashed before.
		update_post_meta( $topic_id, '_bb_pre_trashed_replies', $pre_trashed_replies );

		// Reset the $post global
		wp_reset_postdata();
	}

	// Cleanup
	unset( $replies );
}

/**
 * Called before untrashing a topic
 *
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_is_topic() To check if the passed id is a topic
 * @uses do_action() Calls 'bb_untrash_topic' with the topic id
 * @uses get_post_meta() To get the list of replies which were trashed with the
 *                        topic
 * @uses wp_untrash_post() To untrash the reply
 */
function bb_untrash_topic( $topic_id = 0 ) {
	$topic_id = bb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) || !bb_is_topic( $topic_id ) )
		return false;

	do_action( 'bb_untrash_topic', $topic_id );

	// Get the replies that were not previously trashed
	$pre_trashed_replies = get_post_meta( $topic_id, '_bb_pre_trashed_replies', true );

	// There are replies to untrash
	if ( !empty( $pre_trashed_replies ) ) {

		// Maybe reverse the trashed replies array
		if ( is_array( $pre_trashed_replies ) )
			$pre_trashed_replies = array_reverse( $pre_trashed_replies );

		// Loop through replies
		foreach ( (array) $pre_trashed_replies as $reply ) {
			wp_untrash_post( $reply );
		}
	}
}

/** After Delete/Trash/Untrash ************************************************/

/**
 * Called after deleting a topic
 *
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_is_topic() To check if the passed id is a topic
 * @uses do_action() Calls 'bb_deleted_topic' with the topic id
 */
function bb_deleted_topic( $topic_id = 0 ) {
	$topic_id = bb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) || !bb_is_topic( $topic_id ) )
		return false;

	do_action( 'bb_deleted_topic', $topic_id );
}

/**
 * Called after trashing a topic
 *
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_is_topic() To check if the passed id is a topic
 * @uses do_action() Calls 'bb_trashed_topic' with the topic id
 */
function bb_trashed_topic( $topic_id = 0 ) {
	$topic_id = bb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) || !bb_is_topic( $topic_id ) )
		return false;

	do_action( 'bb_trashed_topic', $topic_id );
}

/**
 * Called after untrashing a topic
 *
 * @uses bb_get_topic_id() To get the topic id
 * @uses bb_is_topic() To check if the passed id is a topic
 * @uses do_action() Calls 'bb_untrashed_topic' with the topic id
 */
function bb_untrashed_topic( $topic_id = 0 ) {
	$topic_id = bb_get_topic_id( $topic_id );

	if ( empty( $topic_id ) || !bb_is_topic( $topic_id ) )
		return false;

	do_action( 'bb_untrashed_topic', $topic_id );
}

/** Settings ******************************************************************/

/**
 * Return the topics per page setting
 *
 * @since barebones (1.0)
 *
 * @param int $default Default replies per page (15)
 * @uses get_option() To get the setting
 * @uses apply_filters() To allow the return value to be manipulated
 * @return int
 */
function bb_get_topics_per_page( $default = 15 ) {

	// Get database option and cast as integer
	$retval = get_option( '_bb_topics_per_page', $default );

	// If return val is empty, set it to default
	if ( empty( $retval ) )
		$retval = $default;

	// Filter and return
	return (int) apply_filters( 'bb_get_topics_per_page', $retval, $default );
}

/**
 * Return the topics per RSS page setting
 *
 * @since barebones (1.0)
 *
 * @param int $default Default replies per page (25)
 * @uses get_option() To get the setting
 * @uses apply_filters() To allow the return value to be manipulated
 * @return int
 */
function bb_get_topics_per_rss_page( $default = 25 ) {

	// Get database option and cast as integer
	$retval = get_option( '_bb_topics_per_rss_page', $default );

	// If return val is empty, set it to default
	if ( empty( $retval ) )
		$retval = $default;

	// Filter and return
	return (int) apply_filters( 'bb_get_topics_per_rss_page', $retval, $default );
}

/** Topic Tags ****************************************************************/

/**
 * Get topic tags for a specific topic ID
 *
 * @since barebones (1.0)
 *
 * @param int $topic_id
 * @param string $sep
 * @return string
 */
function bb_get_topic_tag_names( $topic_id = 0, $sep = ', ' ) {
	$topic_id   = bb_get_topic_id( $topic_id );
	$topic_tags = array_filter( (array) get_the_terms( $topic_id, bb_get_topic_tag_tax_id() ) );
	$terms      = array();
	foreach( $topic_tags as $term ) {
		$terms[] = $term->name;
	}
	$terms = !empty( $terms ) ? implode( $sep, $terms ) : '';

	return apply_filters( 'bb_get_topic_tags', $terms, $topic_id );
}

/** Autoembed *****************************************************************/

/**
 * Check if autoembeds are enabled and hook them in if so
 *
 * @since barebones (1.0)
 * @global WP_Embed $wp_embed
 */
function bb_topic_content_autoembed() {
	global $wp_embed;

	if ( bb_use_autoembed() && is_a( $wp_embed, 'WP_Embed' ) ) {
		add_filter( 'bb_get_topic_content', array( $wp_embed, 'autoembed' ), 2 );
	}
}

/** Feeds *********************************************************************/

/**
 * Output an RSS2 feed of topics, based on the query passed.
 *
 * @since barebones (1.0)
 *
 * @uses bb_version()
 * @uses bb_is_single_topic()
 * @uses bb_user_can_view_forum()
 * @uses bb_get_topic_forum_id()
 * @uses bb_show_load_topic()
 * @uses bb_topic_permalink()
 * @uses bb_topic_title()
 * @uses bb_get_topic_reply_count()
 * @uses bb_topic_content()
 * @uses bb_has_topics()
 * @uses bb_topics()
 * @uses bb_the_topic()
 * @uses get_wp_title_rss()
 * @uses get_option()
 * @uses bloginfo_rss
 * @uses self_link()
 * @uses the_author()
 * @uses get_post_time()
 * @uses rss_enclosure()
 * @uses do_action()
 * @uses apply_filters()
 *
 * @param array $topics_query
 */
function bb_display_topics_feed_rss2( $topics_query = array() ) {

	// User cannot access this forum
	if ( bb_is_single_forum() && !bb_user_can_view_forum( array( 'forum_id' => bb_get_forum_id() ) ) )
		return;

	// Display the feed
	header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true );
	header( 'Status: 200 OK' );
	echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; ?>

	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:atom="http://www.w3.org/2005/Atom"

		<?php do_action( 'bb_feed' ); ?>
	>

	<channel>

		<title><?php bloginfo_rss( 'name' ); ?> &#187; <?php _e( 'All Topics', 'bbpress' ); ?></title>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
		<link><?php self_link(); ?></link>
		<description><?php //?></description>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s O', current_time( 'mysql' ), false ); ?></pubDate>
		<generator>http://bbpress.org/?v=<?php bb_version(); ?></generator>
		<language><?php bloginfo_rss( 'language' ); ?></language>

		<?php do_action( 'bb_feed_head' ); ?>

		<?php if ( bb_has_topics( $topics_query ) ) : ?>

			<?php while ( bb_topics() ) : bb_the_topic(); ?>

				<item>
					<guid><?php bb_topic_permalink(); ?></guid>
					<title><![CDATA[<?php bb_topic_title(); ?>]]></title>
					<link><?php bb_topic_permalink(); ?></link>
					<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_meta( bb_get_topic_id(), '_bb_last_active_time', true ) ); ?></pubDate>
					<dc:creator><?php the_author() ?></dc:creator>

					<?php if ( !post_password_required() ) : ?>

					<description>
						<![CDATA[
						<p><?php printf( __( 'Replies: %s', 'bbpress' ), bb_get_topic_reply_count() ); ?></p>
						<?php bb_topic_content(); ?>
						]]>
					</description>

					<?php rss_enclosure(); ?>

					<?php endif; ?>

					<?php do_action( 'bb_feed_item' ); ?>

				</item>

				<?php endwhile; ?>
			<?php endif; ?>

		<?php do_action( 'bb_feed_footer' ); ?>

	</channel>
	</rss>

<?php
	exit();
}

/** Permissions ***************************************************************/

/**
 * Redirect if unathorized user is attempting to edit a topic
 *
 * @since barebones (1.0)
 *
 * @uses bb_is_topic_edit()
 * @uses current_user_can()
 * @uses bb_get_topic_id()
 * @uses wp_safe_redirect()
 * @uses bb_get_topic_permalink()
 */
function bb_check_topic_edit() {

	// Bail if not editing a topic
	if ( !bb_is_topic_edit() )
		return;

	// User cannot edit topic, so redirect back to topic
	if ( !current_user_can( 'edit_topic', bb_get_topic_id() ) ) {
		wp_safe_redirect( bb_get_topic_permalink() );
		exit();
	}
}

/**
 * Redirect if unathorized user is attempting to edit a topic tag
 *
 * @since barebones (1.0)
 *
 * @uses bb_is_topic_tag_edit()
 * @uses current_user_can()
 * @uses bb_get_topic_tag_id()
 * @uses wp_safe_redirect()
 * @uses bb_get_topic_tag_link()
 */
function bb_check_topic_tag_edit() {

	// Bail if not editing a topic tag
	if ( !bb_is_topic_tag_edit() )
		return;

	// Bail if current user cannot edit topic tags
	if ( !current_user_can( 'edit_topic_tags', bb_get_topic_tag_id() ) ) {
		wp_safe_redirect( bb_get_topic_tag_link() );
		exit();
	}
}
