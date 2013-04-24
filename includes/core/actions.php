<?php

/**
 * barebones Actions
 *
 * @package barebones
 * @subpackage Core
 *
 * This file contains the actions that are used through-out barebones. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * There are a few common places that additional actions can currently be found
 *
 *  - barebones: In {@link barebones::setup_actions()} in bbpress.php
 *  - Admin: More in {@link BB_Admin::setup_actions()} in admin.php
 *
 * @see /core/filters.php
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
 *           v--WordPress Actions        v--barebones Sub-actions
 */
add_action( 'plugins_loaded',           'bb_loaded',                   10    );
add_action( 'init',                     'bb_init',                     0     ); // Early for bb_register
add_action( 'parse_query',              'bb_parse_query',              2     ); // Early for overrides
add_action( 'widgets_init',             'bb_widgets_init',             10    );
add_action( 'generate_rewrite_rules',   'bb_generate_rewrite_rules',   10    );
add_action( 'wp_enqueue_scripts',       'bb_enqueue_scripts',          10    );
add_action( 'wp_head',                  'bb_head',                     10    );
add_action( 'wp_footer',                'bb_footer',                   10    );
add_action( 'set_current_user',         'bb_setup_current_user',       10    );
add_action( 'setup_theme',              'bb_setup_theme',              10    );
add_action( 'after_setup_theme',        'bb_after_setup_theme',        10    );
add_action( 'template_redirect',        'bb_template_redirect',        8     ); // Before BuddyPress's 10 [BB2225]
add_action( 'login_form_login',         'bb_login_form_login',         10    );
add_action( 'profile_update',           'bb_profile_update',           10, 2 ); // user_id and old_user_data
add_action( 'user_register',            'bb_user_register',            10    );

/**
 * bb_loaded - Attached to 'plugins_loaded' above
 *
 * Attach various loader actions to the bb_loaded action.
 * The load order helps to execute code at the correct time.
 *                                                         v---Load order
 */
add_action( 'bb_loaded', 'bb_constants',                 2  );
add_action( 'bb_loaded', 'bb_boot_strap_globals',        4  );
add_action( 'bb_loaded', 'bb_includes',                  6  );
add_action( 'bb_loaded', 'bb_setup_globals',             8  );
add_action( 'bb_loaded', 'bb_setup_option_filters',      10 );
add_action( 'bb_loaded', 'bb_setup_user_option_filters', 12 );
add_action( 'bb_loaded', 'bb_register_theme_packages',   14 );
add_action( 'bb_loaded', 'bb_filter_user_roles_option',  16 );

/**
 * bb_init - Attached to 'init' above
 *
 * Attach various initialization actions to the init action.
 * The load order helps to execute code at the correct time.
 *                                              v---Load order
 */
add_action( 'bb_init', 'bb_load_textdomain',  0   );
add_action( 'bb_init', 'bb_register',         0   );
add_action( 'bb_init', 'bb_add_rewrite_tags', 20  );
add_action( 'bb_init', 'bb_ready',            999 );

/**
 * There is no action API for roles to use, so hook in immediately after
 * everything is included (including the theme's functions.php. This is after
 * the $wp_roles global is set but before $wp->init().
 *
 * If it's hooked in any sooner, role names may not be translated correctly.
 *
 * @link http://bbpress.trac.wordpress.org/ticket/2219
 *
 * This is kind of lame, but is all we have for now.
 */
add_action( 'bb_after_setup_theme', 'bb_add_forums_roles', 1 );

/**
 * When setting up the current user, make sure they have a role for the forums.
 *
 * This is multisite aware, thanks to bb_filter_user_roles_option(), hooked to
 * the 'bb_loaded' action above.
 */
add_action( 'bb_setup_current_user', 'bb_set_current_user_default_role' );

/**
 * bb_register - Attached to 'init' above on 0 priority
 *
 * Attach various initialization actions early to the init action.
 * The load order helps to execute code at the correct time.
 *                                                         v---Load order
 */
add_action( 'bb_register', 'bb_register_post_types',     2  );
add_action( 'bb_register', 'bb_register_post_statuses',  4  );
add_action( 'bb_register', 'bb_register_taxonomies',     6  );
add_action( 'bb_register', 'bb_register_views',          8  );
add_action( 'bb_register', 'bb_register_shortcodes',     10 );

// Autoembeds
add_action( 'bb_init', 'bb_reply_content_autoembed', 8   );
add_action( 'bb_init', 'bb_topic_content_autoembed', 8   );

/**
 * bb_ready - attached to end 'bb_init' above
 *
 * Attach actions to the ready action after barebones has fully initialized.
 * The load order helps to execute code at the correct time.
 *                                                v---Load order
 */
add_action( 'bb_ready',  'bb_setup_akismet',    2  ); // Spam prevention for topics and replies
add_action( 'bp_include', 'bb_setup_buddypress', 10 ); // Social network integration

// Try to load the bbpress-functions.php file from the active themes
add_action( 'bb_after_setup_theme', 'bb_load_theme_functions', 10 );

// Widgets
add_action( 'bb_widgets_init', array( 'BB_Login_Widget',   'register_widget' ), 10 );
add_action( 'bb_widgets_init', array( 'BB_Views_Widget',   'register_widget' ), 10 );
add_action( 'bb_widgets_init', array( 'BB_Search_Widget',  'register_widget' ), 10 );
add_action( 'bb_widgets_init', array( 'BB_Forums_Widget',  'register_widget' ), 10 );
add_action( 'bb_widgets_init', array( 'BB_Topics_Widget',  'register_widget' ), 10 );
add_action( 'bb_widgets_init', array( 'BB_Replies_Widget', 'register_widget' ), 10 );
add_action( 'bb_widgets_init', array( 'BB_Stats_Widget',   'register_widget' ), 10 );

// Notices (loaded after bb_init for translations)
add_action( 'bb_head',             'bb_login_notices'    );
add_action( 'bb_head',             'bb_topic_notices'    );
add_action( 'bb_template_notices', 'bb_template_notices' );

// Always exclude private/hidden forums if needed
add_action( 'pre_get_posts', 'bb_pre_get_posts_normalize_forum_visibility', 4 );

// Profile Page Messages
add_action( 'bb_template_notices', 'bb_notice_edit_user_success'           );
add_action( 'bb_template_notices', 'bb_notice_edit_user_is_super_admin', 2 );

// Before Delete/Trash/Untrash Topic
add_action( 'wp_trash_post', 'bb_trash_forum'   );
add_action( 'trash_post',    'bb_trash_forum'   );
add_action( 'untrash_post',  'bb_untrash_forum' );
add_action( 'delete_post',   'bb_delete_forum'  );

// After Deleted/Trashed/Untrashed Topic
add_action( 'trashed_post',   'bb_trashed_forum'   );
add_action( 'untrashed_post', 'bb_untrashed_forum' );
add_action( 'deleted_post',   'bb_deleted_forum'   );

// Auto trash/untrash/delete a forums topics
add_action( 'bb_delete_forum',  'bb_delete_forum_topics',  10 );
add_action( 'bb_trash_forum',   'bb_trash_forum_topics',   10 );
add_action( 'bb_untrash_forum', 'bb_untrash_forum_topics', 10 );

// New/Edit Forum
add_action( 'bb_new_forum',  'bb_update_forum', 10 );
add_action( 'bb_edit_forum', 'bb_update_forum', 10 );

// Save forum extra metadata
add_action( 'bb_new_forum_post_extras',         'bb_save_forum_extras', 2 );
add_action( 'bb_edit_forum_post_extras',        'bb_save_forum_extras', 2 );
add_action( 'bb_forum_attributes_metabox_save', 'bb_save_forum_extras', 2 );

// New/Edit Reply
add_action( 'bb_new_reply',  'bb_update_reply', 10, 6 );
add_action( 'bb_edit_reply', 'bb_update_reply', 10, 6 );

// Before Delete/Trash/Untrash Reply
add_action( 'wp_trash_post', 'bb_trash_reply'   );
add_action( 'trash_post',    'bb_trash_reply'   );
add_action( 'untrash_post',  'bb_untrash_reply' );
add_action( 'delete_post',   'bb_delete_reply'  );

// After Deleted/Trashed/Untrashed Reply
add_action( 'trashed_post',   'bb_trashed_reply'   );
add_action( 'untrashed_post', 'bb_untrashed_reply' );
add_action( 'deleted_post',   'bb_deleted_reply'   );

// New/Edit Topic
add_action( 'bb_new_topic',  'bb_update_topic', 10, 5 );
add_action( 'bb_edit_topic', 'bb_update_topic', 10, 5 );

// Split/Merge Topic
add_action( 'bb_merged_topic',     'bb_merge_topic_count', 1, 3 );
add_action( 'bb_post_split_topic', 'bb_split_topic_count', 1, 3 );

// Move Reply
add_action( 'bb_post_move_reply', 'bb_move_reply_count', 1, 3 );

// Before Delete/Trash/Untrash Topic
add_action( 'wp_trash_post', 'bb_trash_topic'   );
add_action( 'trash_post',    'bb_trash_topic'   );
add_action( 'untrash_post',  'bb_untrash_topic' );
add_action( 'delete_post',   'bb_delete_topic'  );

// After Deleted/Trashed/Untrashed Topic
add_action( 'trashed_post',   'bb_trashed_topic'   );
add_action( 'untrashed_post', 'bb_untrashed_topic' );
add_action( 'deleted_post',   'bb_deleted_topic'   );

// Favorites
add_action( 'bb_trash_topic',  'bb_remove_topic_from_all_favorites' );
add_action( 'bb_delete_topic', 'bb_remove_topic_from_all_favorites' );

// Subscriptions
add_action( 'bb_trash_topic',  'bb_remove_topic_from_all_subscriptions'       );
add_action( 'bb_delete_topic', 'bb_remove_topic_from_all_subscriptions'       );
add_action( 'bb_new_reply',    'bb_notify_subscribers',                 11, 5 );

// Sticky
add_action( 'bb_trash_topic',  'bb_unstick_topic' );
add_action( 'bb_delete_topic', 'bb_unstick_topic' );

// Update topic branch
add_action( 'bb_trashed_topic',   'bb_update_topic_walker' );
add_action( 'bb_untrashed_topic', 'bb_update_topic_walker' );
add_action( 'bb_deleted_topic',   'bb_update_topic_walker' );
add_action( 'bb_spammed_topic',   'bb_update_topic_walker' );
add_action( 'bb_unspammed_topic', 'bb_update_topic_walker' );

// Update reply branch
add_action( 'bb_trashed_reply',   'bb_update_reply_walker' );
add_action( 'bb_untrashed_reply', 'bb_update_reply_walker' );
add_action( 'bb_deleted_reply',   'bb_update_reply_walker' );
add_action( 'bb_spammed_reply',   'bb_update_reply_walker' );
add_action( 'bb_unspammed_reply', 'bb_update_reply_walker' );

// User status
// @todo make these sub-actions
add_action( 'make_ham_user',  'bb_make_ham_user'  );
add_action( 'make_spam_user', 'bb_make_spam_user' );

// User role
add_action( 'bb_profile_update', 'bb_profile_update_role' );

// Hook WordPress admin actions to barebones profiles on save
add_action( 'bb_user_edit_after', 'bb_user_edit_after' );

// Caches
add_action( 'bb_new_forum_pre_extras',  'bb_clean_post_cache' );
add_action( 'bb_new_forum_post_extras', 'bb_clean_post_cache' );
add_action( 'bb_new_topic_pre_extras',  'bb_clean_post_cache' );
add_action( 'bb_new_topic_post_extras', 'bb_clean_post_cache' );
add_action( 'bb_new_reply_pre_extras',  'bb_clean_post_cache' );
add_action( 'bb_new_reply_post_extras', 'bb_clean_post_cache' );

/**
 * barebones needs to redirect the user around in a few different circumstances:
 *
 * 1. POST and GET requests
 * 2. Accessing private or hidden content (forums/topics/replies)
 * 3. Editing forums, topics, replies, users, and tags
 * 4. barebones specific AJAX requests
 */
add_action( 'bb_template_redirect', 'bb_forum_enforce_blocked', 1  );
add_action( 'bb_template_redirect', 'bb_forum_enforce_hidden',  1  );
add_action( 'bb_template_redirect', 'bb_forum_enforce_private', 1  );
add_action( 'bb_template_redirect', 'bb_post_request',          10 );
add_action( 'bb_template_redirect', 'bb_get_request',           10 );
add_action( 'bb_template_redirect', 'bb_check_user_edit',       10 );
add_action( 'bb_template_redirect', 'bb_check_forum_edit',      10 );
add_action( 'bb_template_redirect', 'bb_check_topic_edit',      10 );
add_action( 'bb_template_redirect', 'bb_check_reply_edit',      10 );
add_action( 'bb_template_redirect', 'bb_check_topic_tag_edit',  10 );

// Theme-side POST requests
add_action( 'bb_post_request', 'bb_do_ajax',                1  );
add_action( 'bb_post_request', 'bb_edit_topic_tag_handler', 1  );
add_action( 'bb_post_request', 'bb_edit_user_handler',      1  );
add_action( 'bb_post_request', 'bb_edit_forum_handler',     1  );
add_action( 'bb_post_request', 'bb_edit_reply_handler',     1  );
add_action( 'bb_post_request', 'bb_edit_topic_handler',     1  );
add_action( 'bb_post_request', 'bb_merge_topic_handler',    1  );
add_action( 'bb_post_request', 'bb_split_topic_handler',    1  );
add_action( 'bb_post_request', 'bb_move_reply_handler',     1  );
add_action( 'bb_post_request', 'bb_new_forum_handler',      10 );
add_action( 'bb_post_request', 'bb_new_reply_handler',      10 );
add_action( 'bb_post_request', 'bb_new_topic_handler',      10 );

// Theme-side GET requests
add_action( 'bb_get_request', 'bb_toggle_topic_handler',   1  );
add_action( 'bb_get_request', 'bb_toggle_reply_handler',   1  );
add_action( 'bb_get_request', 'bb_favorites_handler',      1  );
add_action( 'bb_get_request', 'bb_subscriptions_handler',  1  );

// Maybe convert the users password
add_action( 'bb_login_form_login', 'bb_user_maybe_convert_pass' );

add_action( 'bb_activation', 'bb_add_activation_redirect' );
