<?php

/**
 * barebones Admin Settings
 *
 * @package barebones
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Sections ******************************************************************/

/**
 * Get the Forums settings sections.
 *
 * @since barebones (1.0)
 * @return array
 */
function bb_admin_get_settings_sections() {
	return (array) apply_filters( 'bb_admin_get_settings_sections', array(
		'bb_settings_main' => array(
			'title'    => __( 'Main Settings', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_main_section',
			'page'     => 'barebones',
		),
		'bb_settings_theme_compat' => array(
			'title'    => __( 'Theme Packages', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_subtheme_section',
			'page'     => 'barebones',
		),
		'bb_settings_per_page' => array(
			'title'    => __( 'Per Page', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_per_page_section',
			'page'     => 'barebones',
		),
		'bb_settings_per_rss_page' => array(
			'title'    => __( 'Per RSS Page', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_per_rss_page_section',
			'page'     => 'barebones',
		),
		'bb_settings_root_slugs' => array(
			'title'    => __( 'Archive Slugs', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_root_slug_section',
			'page'     => 'barebones',
		),
		'bb_settings_single_slugs' => array(
			'title'    => __( 'Single Slugs', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_single_slug_section',
			'page'     => 'barebones',
		),
		'bb_settings_buddypress' => array(
			'title'    => __( 'BuddyPress', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_buddypress_section',
			'page'     => 'barebones',
		),
		'bb_settings_akismet' => array(
			'title'    => __( 'Akismet', 'barebones' ),
			'callback' => 'bb_admin_setting_callback_akismet_section',
			'page'     => 'barebones'
		)
	) );
}

/**
 * Get all of the settings fields.
 *
 * @since barebones (1.0)
 * @return type
 */
function bb_admin_get_settings_fields() {
	return (array) apply_filters( 'bb_admin_get_settings_fields', array(

		/** Main Section ******************************************************/

		'bb_settings_main' => array(

			// Edit lock setting
			'_bb_edit_lock' => array(
				'title'             => __( 'Disallow editing after', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_editlock',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Throttle setting
			'_bb_throttle_time' => array(
				'title'             => __( 'Throttle posting every', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_throttle',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow topic and reply revisions
			'_bb_allow_revisions' => array(
				'title'             => __( 'Revisions', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_revisions',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow favorites setting
			'_bb_enable_favorites' => array(
				'title'             => __( 'Favorites', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_favorites',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow subscriptions setting
			'_bb_enable_subscriptions' => array(
				'title'             => __( 'Subscriptions', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_subscriptions',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow topic tags
			'_bb_allow_topic_tags' => array(
				'title'             => __( 'Topic tags', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_topic_tags',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow anonymous posting setting
			'_bb_allow_anonymous' => array(
				'title'             => __( 'Anonymous posting', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_anonymous',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow global access (on multisite)
			'_bb_default_role' => array(
				'title'             => __( 'Default user role', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_default_role',
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array()
			),

			// Allow global access (on multisite)
			'_bb_allow_global_access' => array(
				'title'             => __( 'Auto role', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_global_access',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Allow fancy editor setting
			'_bb_use_wp_editor' => array(
				'title'             => __( 'Fancy editor', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_use_wp_editor',
				'args'              => array(),
				'sanitize_callback' => 'intval'
			),

			// Allow auto embedding setting
			'_bb_use_autoembed' => array(
				'title'             => __( 'Auto-embed links', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_use_autoembed',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Theme Packages ****************************************************/

		'bb_settings_theme_compat' => array(

			// Replies per page setting
			'_bb_theme_package_id' => array(
				'title'             => __( 'Current Package', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_subtheme_id',
				'sanitize_callback' => 'esc_sql',
				'args'              => array()
			)
		),

		/** Per Page Section **************************************************/

		'bb_settings_per_page' => array(

			// Replies per page setting
			'_bb_topics_per_page' => array(
				'title'             => __( 'Topics', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_topics_per_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Replies per page setting
			'_bb_replies_per_page' => array(
				'title'             => __( 'Replies', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_replies_per_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Per RSS Page Section **********************************************/

		'bb_settings_per_rss_page' => array(

			// Replies per page setting
			'_bb_topics_per_rss_page' => array(
				'title'             => __( 'Topics', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_topics_per_rss_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Replies per page setting
			'_bb_replies_per_rss_page' => array(
				'title'             => __( 'Replies', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_replies_per_rss_page',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Front Slugs *******************************************************/

		'bb_settings_root_slugs' => array(

			// Root slug setting
			'_bb_root_slug' => array(
				'title'             => __( 'Forums base', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_root_slug',
				'sanitize_callback' => 'esc_sql',
				'args'              => array()
			),

			// Topic archive setting
			'_bb_topic_archive_slug' => array(
				'title'             => __( 'Topics base', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_topic_archive_slug',
				'sanitize_callback' => 'esc_sql',
				'args'              => array()
			)
		),

		/** Single Slugs ******************************************************/

		'bb_settings_single_slugs' => array(

			// Include root setting
			'_bb_include_root' => array(
				'title'             => __( 'Forum Prefix', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_include_root',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Forum slug setting
			'_bb_forum_slug' => array(
				'title'             => __( 'Forum slug', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_forum_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),

			// Topic slug setting
			'_bb_topic_slug' => array(
				'title'             => __( 'Topic slug', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_topic_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),

			// Topic tag slug setting
			'_bb_topic_tag_slug' => array(
				'title'             => __( 'Topic tag slug', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_topic_tag_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),

			// Reply slug setting
			'_bb_reply_slug' => array(
				'title'             => __( 'Reply slug', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_reply_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),

			// User slug setting
			'_bb_user_slug' => array(
				'title'             => __( 'User slug', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_user_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),

			// View slug setting
			'_bb_view_slug' => array(
				'title'             => __( 'Topic view slug', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_view_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			),

			// Search slug setting
			'_bb_search_slug' => array(
				'title'             => __( 'Search slug', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_search_slug',
				'sanitize_callback' => 'sanitize_title',
				'args'              => array()
			)
		),

		/** BuddyPress ********************************************************/

		'bb_settings_buddypress' => array(

			// Are group forums enabled?
			'_bb_enable_group_forums' => array(
				'title'             => __( 'Enable Group Forums', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_group_forums',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Group forums parent forum ID
			'_bb_group_forums_root_id' => array(
				'title'             => __( 'Group Forums Parent', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_group_forums_root_id',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		),

		/** Akismet ***********************************************************/

		'bb_settings_akismet' => array(

			// Should we use Akismet
			'_bb_enable_akismet' => array(
				'title'             => __( 'Use Akismet', 'barebones' ),
				'callback'          => 'bb_admin_setting_callback_akismet',
				'sanitize_callback' => 'intval',
				'args'              => array()
			)
		)
	) );
}

/**
 * Get settings fields by section.
 *
 * @since barebones (1.0)
 * @param string $section_id
 * @return mixed False if section is invalid, array of fields otherwise.
 */
function bb_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = bb_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : false;

	return (array) apply_filters( 'bb_admin_get_settings_fields_for_section', $retval, $section_id );
}

/** Main Section **************************************************************/

/**
 * Main settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_main_section() {
?>

	<p><?php _e( 'Main forum settings for enabling features and setting time limits', 'barebones' ); ?></p>

<?php
}

/**
 * Edit lock setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_editlock() {
?>

	<input name="_bb_edit_lock" type="number" min="0" step="1" id="_bb_edit_lock" value="<?php bb_form_option( '_bb_edit_lock', '5' ); ?>" class="small-text"<?php bb_maybe_admin_setting_disabled( '_bb_edit_lock' ); ?> />
	<label for="_bb_edit_lock"><?php _e( 'minutes', 'barebones' ); ?></label>

<?php
}

/**
 * Throttle setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_throttle() {
?>

	<input name="_bb_throttle_time" type="number" min="0" step="1" id="_bb_throttle_time" value="<?php bb_form_option( '_bb_throttle_time', '10' ); ?>" class="small-text"<?php bb_maybe_admin_setting_disabled( '_bb_throttle_time' ); ?> />
	<label for="_bb_throttle_time"><?php _e( 'seconds', 'barebones' ); ?></label>

<?php
}

/**
 * Allow favorites setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_favorites() {
?>

	<input id="_bb_enable_favorites" name="_bb_enable_favorites" type="checkbox" id="_bb_enable_favorites" value="1" <?php checked( bb_is_favorites_active( true ) ); bb_maybe_admin_setting_disabled( '_bb_enable_favorites' ); ?> />
	<label for="_bb_enable_favorites"><?php _e( 'Allow users to mark topics as favorites', 'barebones' ); ?></label>

<?php
}

/**
 * Allow subscriptions setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_subscriptions() {
?>

	<input id="_bb_enable_subscriptions" name="_bb_enable_subscriptions" type="checkbox" id="_bb_enable_subscriptions" value="1" <?php checked( bb_is_subscriptions_active( true ) ); bb_maybe_admin_setting_disabled( '_bb_enable_subscriptions' ); ?> />
	<label for="_bb_enable_subscriptions"><?php _e( 'Allow users to subscribe to topics', 'barebones' ); ?></label>

<?php
}

/**
 * Allow topic tags setting field
 *
 * @since barebones (r####)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_topic_tags() {
?>

	<input id="_bb_allow_topic_tags" name="_bb_allow_topic_tags" type="checkbox" id="_bb_allow_topic_tags" value="1" <?php checked( bb_allow_topic_tags( true ) ); bb_maybe_admin_setting_disabled( '_bb_allow_topic_tags' ); ?> />
	<label for="_bb_allow_topic_tags"><?php _e( 'Allow topics to have tags', 'barebones' ); ?></label>

<?php
}

/**
 * Allow topic and reply revisions
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_revisions() {
?>

	<input id="_bb_allow_revisions" name="_bb_allow_revisions" type="checkbox" id="_bb_allow_revisions" value="1" <?php checked( bb_allow_revisions( true ) ); bb_maybe_admin_setting_disabled( '_bb_allow_revisions' ); ?> />
	<label for="_bb_allow_revisions"><?php _e( 'Allow topic and reply revision logging', 'barebones' ); ?></label>

<?php
}

/**
 * Allow anonymous posting setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_anonymous() {
?>

	<input id="_bb_allow_anonymous" name="_bb_allow_anonymous" type="checkbox" id="_bb_allow_anonymous" value="1" <?php checked( bb_allow_anonymous( false ) ); bb_maybe_admin_setting_disabled( '_bb_allow_anonymous' ); ?> />
	<label for="_bb_allow_anonymous"><?php _e( 'Allow guest users without accounts to create topics and replies', 'barebones' ); ?></label>

<?php
}

/**
 * Allow global access setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_global_access() {
?>

	<input id="_bb_allow_global_access" name="_bb_allow_global_access" type="checkbox" id="_bb_allow_global_access" value="1" <?php checked( bb_allow_global_access( true ) ); bb_maybe_admin_setting_disabled( '_bb_allow_global_access' ); ?> />
	<label for="_bb_allow_global_access"><?php _e( 'Automatically assign default role to new, registered users upon visiting the site.', 'barebones' ); ?></label>

<?php
}

/**
 * Output forum role selector (for user edit)
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_default_role() {

	$default_role = bb_get_default_role(); ?>

	<select name="_bb_default_role" id="_bb_default_role" <?php bb_maybe_admin_setting_disabled( '_bb_default_role' ); ?>>

		<?php foreach ( bb_get_dynamic_roles() as $role => $details ) : ?>

			<option <?php selected( $default_role, $role ); ?> value="<?php echo esc_attr( $role ); ?>"><?php echo translate_user_role( $details['name'] ); ?></option>

		<?php endforeach; ?>

	</select>

	<?php
}

/**
 * Use the WordPress editor setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_use_wp_editor() {
?>

	<input id="_bb_use_wp_editor" name="_bb_use_wp_editor" type="checkbox" id="_bb_use_wp_editor" value="1" <?php checked( bb_use_wp_editor( true ) ); bb_maybe_admin_setting_disabled( '_bb_use_wp_editor' ); ?> />
	<label for="_bb_use_wp_editor"><?php _e( 'Use the fancy WordPress editor to create and edit topics and replies', 'barebones' ); ?></label>

<?php
}

/**
 * Main subtheme section
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_subtheme_section() {
?>

	<p><?php _e( 'How your forum content is displayed within your existing theme.', 'barebones' ); ?></p>

<?php
}

/**
 * Use the WordPress editor setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_subtheme_id() {

	// Declare locale variable
	$theme_options   = '';
	$current_package = bb_get_theme_package_id( 'default' );

	// Note: This should never be empty. /templates/ is the
	// canonical backup if no other packages exist. If there's an error here,
	// something else is wrong.
	//
	// @see barebones::register_theme_packages()
	foreach ( (array) barebones()->theme_compat->packages as $id => $theme ) {
		$theme_options .= '<option value="' . esc_attr( $id ) . '"' . selected( $theme->id, $current_package, false ) . '>' . sprintf( __( '%1$s - %2$s', 'barebones' ), esc_html( $theme->name ), esc_html( str_replace( WP_CONTENT_DIR, '', $theme->dir ) ) )  . '</option>';
	}

	if ( !empty( $theme_options ) ) : ?>

		<select name="_bb_theme_package_id" id="_bb_theme_package_id" <?php bb_maybe_admin_setting_disabled( '_bb_theme_package_id' ); ?>><?php echo $theme_options ?></select>
		<label for="_bb_theme_package_id"><?php _e( 'will serve all barebones templates', 'barebones' ); ?></label>

	<?php else : ?>

		<p><?php _e( 'No template packages available.', 'barebones' ); ?></p>

	<?php endif;
}

/**
 * Allow oEmbed in replies
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_use_autoembed() {
?>

	<input id="_bb_use_autoembed" name="_bb_use_autoembed" type="checkbox" id="_bb_use_autoembed" value="1" <?php checked( bb_use_autoembed( true ) ); bb_maybe_admin_setting_disabled( '_bb_use_autoembed' ); ?> />
	<label for="_bb_use_autoembed"><?php _e( 'Embed media (YouTube, Twitter, Flickr, etc...) directly into topics and replies', 'barebones' ); ?></label>

<?php
}

/** Per Page Section **********************************************************/

/**
 * Per page settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_per_page_section() {
?>

	<p><?php _e( 'How many topics and replies to show per page', 'barebones' ); ?></p>

<?php
}

/**
 * Topics per page setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_topics_per_page() {
?>

	<input name="_bb_topics_per_page" type="number" min="1" step="1" id="_bb_topics_per_page" value="<?php bb_form_option( '_bb_topics_per_page', '15' ); ?>" class="small-text"<?php bb_maybe_admin_setting_disabled( '_bb_topics_per_page' ); ?> />
	<label for="_bb_topics_per_page"><?php _e( 'per page', 'barebones' ); ?></label>

<?php
}

/**
 * Replies per page setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_replies_per_page() {
?>

	<input name="_bb_replies_per_page" type="number" min="1" step="1" id="_bb_replies_per_page" value="<?php bb_form_option( '_bb_replies_per_page', '15' ); ?>" class="small-text"<?php bb_maybe_admin_setting_disabled( '_bb_replies_per_page' ); ?> />
	<label for="_bb_replies_per_page"><?php _e( 'per page', 'barebones' ); ?></label>

<?php
}

/** Per RSS Page Section ******************************************************/

/**
 * Per page settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_per_rss_page_section() {
?>

	<p><?php _e( 'How many topics and replies to show per RSS page', 'barebones' ); ?></p>

<?php
}

/**
 * Topics per RSS page setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_topics_per_rss_page() {
?>

	<input name="_bb_topics_per_rss_page" type="number" min="1" step="1" id="_bb_topics_per_rss_page" value="<?php bb_form_option( '_bb_topics_per_rss_page', '25' ); ?>" class="small-text"<?php bb_maybe_admin_setting_disabled( '_bb_topics_per_rss_page' ); ?> />
	<label for="_bb_topics_per_rss_page"><?php _e( 'per page', 'barebones' ); ?></label>

<?php
}

/**
 * Replies per RSS page setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_replies_per_rss_page() {
?>

	<input name="_bb_replies_per_rss_page" type="number" min="1" step="1" id="_bb_replies_per_rss_page" value="<?php bb_form_option( '_bb_replies_per_rss_page', '25' ); ?>" class="small-text"<?php bb_maybe_admin_setting_disabled( '_bb_replies_per_rss_page' ); ?> />
	<label for="_bb_replies_per_rss_page"><?php _e( 'per page', 'barebones' ); ?></label>

<?php
}

/** Slug Section **************************************************************/

/**
 * Slugs settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_root_slug_section() {

	// Flush rewrite rules when this section is saved
	if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) )
		flush_rewrite_rules(); ?>

	<p><?php printf( __( 'Custom root slugs to prefix your forums and topics with. These can be partnered with WordPress pages to allow more flexibility.', 'barebones' ), get_admin_url( null, 'options-permalink.php' ) ); ?></p>

<?php
}

/**
 * Root slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_root_slug() {
?>

		<input name="_bb_root_slug" type="text" id="_bb_root_slug" class="regular-text code" value="<?php bb_form_option( '_bb_root_slug', 'forums', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_root_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_root_slug', 'forums' );
}

/**
 * Topic archive slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_topic_archive_slug() {
?>

	<input name="_bb_topic_archive_slug" type="text" id="_bb_topic_archive_slug" class="regular-text code" value="<?php bb_form_option( '_bb_topic_archive_slug', 'topics', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_topic_archive_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_topic_archive_slug', 'topics' );
}

/** Single Slugs **************************************************************/

/**
 * Slugs settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_single_slug_section() {
?>

	<p><?php printf( __( 'Custom slugs for single forums, topics, replies, tags, users, and views here. If you change these, existing permalinks will also change.', 'barebones' ), get_admin_url( null, 'options-permalink.php' ) ); ?></p>

<?php
}

/**
 * Include root slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_include_root() {
?>

	<input id="_bb_include_root" name="_bb_include_root" type="checkbox" id="_bb_include_root" value="1" <?php checked( get_option( '_bb_include_root', true ) ); bb_maybe_admin_setting_disabled( '_bb_include_root' ); ?> />
	<label for="_bb_include_root"><?php _e( 'Prefix your forum area with the Forum Base slug (Recommended)', 'barebones' ); ?></label>

<?php
}

/**
 * Forum slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_forum_slug() {
?>

	<input name="_bb_forum_slug" type="text" id="_bb_forum_slug" class="regular-text code" value="<?php bb_form_option( '_bb_forum_slug', 'forum', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_forum_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_forum_slug', 'forum' );
}

/**
 * Topic slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_topic_slug() {
?>

	<input name="_bb_topic_slug" type="text" id="_bb_topic_slug" class="regular-text code" value="<?php bb_form_option( '_bb_topic_slug', 'topic', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_topic_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_topic_slug', 'topic' );
}

/**
 * Reply slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_reply_slug() {
?>

	<input name="_bb_reply_slug" type="text" id="_bb_reply_slug" class="regular-text code" value="<?php bb_form_option( '_bb_reply_slug', 'reply', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_reply_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_reply_slug', 'reply' );
}

/**
 * Topic tag slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_topic_tag_slug() {
?>

	<input name="_bb_topic_tag_slug" type="text" id="_bb_topic_tag_slug" class="regular-text code" value="<?php bb_form_option( '_bb_topic_tag_slug', 'topic-tag', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_topic_tag_slug' ); ?> />

<?php

	// Slug Check
	bb_form_slug_conflict_check( '_bb_topic_tag_slug', 'topic-tag' );
}

/** Other Slugs ***************************************************************/

/**
 * User slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_user_slug() {
?>

	<input name="_bb_user_slug" type="text" id="_bb_user_slug" class="regular-text code" value="<?php bb_form_option( '_bb_user_slug', 'users', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_user_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_user_slug', 'users' );
}

/**
 * View slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_view_slug() {
?>

	<input name="_bb_view_slug" type="text" id="_bb_view_slug" class="regular-text code" value="<?php bb_form_option( '_bb_view_slug', 'view', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_view_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_view_slug', 'view' );
}

/**
 * Search slug setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_search_slug() {
?>

	<input name="_bb_search_slug" type="text" id="_bb_search_slug" class="regular-text code" value="<?php bb_form_option( '_bb_search_slug', 'search', true ); ?>"<?php bb_maybe_admin_setting_disabled( '_bb_search_slug' ); ?> />

<?php
	// Slug Check
	bb_form_slug_conflict_check( '_bb_search_slug', 'search' );
}

/** BuddyPress ****************************************************************/

/**
 * Extension settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_buddypress_section() {
?>

	<p><?php _e( 'Forum settings for BuddyPress', 'barebones' ); ?></p>

<?php
}

/**
 * Allow BuddyPress group forums setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_group_forums() {
?>

	<input id="_bb_enable_group_forums" name="_bb_enable_group_forums" type="checkbox" id="_bb_enable_group_forums" value="1" <?php checked( bb_is_group_forums_active( true ) );  bb_maybe_admin_setting_disabled( '_bb_enable_group_forums' ); ?> />
	<label for="_bb_enable_group_forums"><?php _e( 'Allow BuddyPress Groups to have their own forums', 'barebones' ); ?></label>

<?php
}

/**
 * Replies per page setting field
 *
 * @since barebones (1.0)
 *
 * @uses bb_form_option() To output the option value
 */
function bb_admin_setting_callback_group_forums_root_id() {

	// Output the dropdown for all forums
	bb_dropdown( array(
		'selected'           => bb_get_group_forums_root_id(),
		'show_none'          => __( '&mdash; Forum root &mdash;', 'barebones' ),
		'orderby'            => 'title',
		'order'              => 'ASC',
		'select_id'          => '_bb_group_forums_root_id',
		'disable_categories' => false,
		'disabled'           => '_bb_group_forums_root_id'
	) ); ?>

	<label for="_bb_group_forums_root_id"><?php _e( 'is the parent for all group forums', 'barebones' ); ?></label>
	<p class="description"><?php _e( 'Using the Forum Root is not recommended. Changing this does not move existing forums.', 'barebones' ); ?></p>

<?php
}

/** Akismet *******************************************************************/

/**
 * Extension settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_admin_setting_callback_akismet_section() {
?>

	<p><?php _e( 'Forum settings for Akismet', 'barebones' ); ?></p>

<?php
}


/**
 * Allow Akismet setting field
 *
 * @since barebones (1.0)
 *
 * @uses checked() To display the checked attribute
 */
function bb_admin_setting_callback_akismet() {
?>

	<input id="_bb_enable_akismet" name="_bb_enable_akismet" type="checkbox" id="_bb_enable_akismet" value="1" <?php checked( bb_is_akismet_active( true ) );  bb_maybe_admin_setting_disabled( '_bb_enable_akismet' ); ?> />
	<label for="_bb_enable_akismet"><?php _e( 'Allow Akismet to actively prevent forum spam.', 'barebones' ); ?></label>

<?php
}

/** Settings Page *************************************************************/

/**
 * The main settings page
 *
 * @since barebones (1.0)
 *
 * @uses screen_icon() To display the screen icon
 * @uses settings_fields() To output the hidden fields for the form
 * @uses do_settings_sections() To output the settings sections
 */
function bb_admin_settings() {
?>

	<div class="wrap">

		<?php screen_icon(); ?>

		<h2><?php _e( 'Forums Settings', 'barebones' ) ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'barebones' ); ?>

			<?php do_settings_sections( 'barebones' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'barebones' ); ?>" />
			</p>
		</form>
	</div>

<?php
}


/** Converter Section *********************************************************/

/**
 * Main settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_main_section() {
?>

	<p><?php _e( 'Information about your previous forums database so that they can be converted. <strong>Backup your database before proceeding.</strong>', 'barebones' ); ?></p>

<?php
}

/**
 * Edit Platform setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_platform() {

	$platform_options = '';
	$curdir           = opendir( barebones()->admin->admin_dir . 'converters/' );

	// Bail if no directory was found (how did this happen?)
	if ( empty( $curdir ) )
		return;

	// Loop through files in the converters folder and assemble some options
	while ( $file = readdir( $curdir ) ) {
		if ( ( stristr( $file, '.php' ) ) && ( stristr( $file, 'index' ) === false ) ) {
			$file              = preg_replace( '/.php/', '', $file );
			$platform_options .= '<option value="' . $file . '">' . $file . '</option>';
		}
	}

	closedir( $curdir ); ?>

	<select name="_bb_converter_platform" id="_bb_converter_platform" /><?php echo $platform_options ?></select>
	<label for="_bb_converter_platform"><?php _e( 'is the previous forum software', 'barebones' ); ?></label>

<?php
}

/**
 * Edit Database Server setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_dbserver() {
?>

	<input name="_bb_converter_db_server" type="text" id="_bb_converter_db_server" value="<?php bb_form_option( '_bb_converter_db_server', 'localhost' ); ?>" class="medium-text" />
	<label for="_bb_converter_db_server"><?php _e( 'IP or hostname', 'barebones' ); ?></label>

<?php
}

/**
 * Edit Database Server Port setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_dbport() {
?>

	<input name="_bb_converter_db_port" type="text" id="_bb_converter_db_port" value="<?php bb_form_option( '_bb_converter_db_port', '3306' ); ?>" class="small-text" />
	<label for="_bb_converter_db_port"><?php _e( 'Use default 3306 if unsure', 'barebones' ); ?></label>

<?php
}

/**
 * Edit Database User setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_dbuser() {
?>

	<input name="_bb_converter_db_user" type="text" id="_bb_converter_db_user" value="<?php bb_form_option( '_bb_converter_db_user' ); ?>" class="medium-text" />
	<label for="_bb_converter_db_user"><?php _e( 'User for your database connection', 'barebones' ); ?></label>

<?php
}

/**
 * Edit Database Pass setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_dbpass() {
?>

	<input name="_bb_converter_db_pass" type="password" id="_bb_converter_db_pass" value="<?php bb_form_option( '_bb_converter_db_pass' ); ?>" class="medium-text" />
	<label for="_bb_converter_db_pass"><?php _e( 'Password to access the database', 'barebones' ); ?></label>

<?php
}

/**
 * Edit Database Name setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_dbname() {
?>

	<input name="_bb_converter_db_name" type="text" id="_bb_converter_db_name" value="<?php bb_form_option( '_bb_converter_db_name' ); ?>" class="medium-text" />
	<label for="_bb_converter_db_name"><?php _e( 'Name of the database with your old forum data', 'barebones' ); ?></label>

<?php
}

/**
 * Main settings section description for the settings page
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_options_section() {
?>

	<p><?php _e( 'Some optional parameters to help tune the conversion process.', 'barebones' ); ?></p>

<?php
}

/**
 * Edit Table Prefix setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_dbprefix() {
?>

	<input name="_bb_converter_db_prefix" type="text" id="_bb_converter_db_prefix" value="<?php bb_form_option( '_bb_converter_db_prefix' ); ?>" class="medium-text" />
	<label for="_bb_converter_db_prefix"><?php _e( '(If converting from BuddyPress Forums, use "wp_bb_" or your custom prefix)', 'barebones' ); ?></label>

<?php
}

/**
 * Edit Rows Limit setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_rows() {
?>

	<input name="_bb_converter_rows" type="text" id="_bb_converter_rows" value="<?php bb_form_option( '_bb_converter_rows', '100' ); ?>" class="small-text" />
	<label for="_bb_converter_rows"><?php _e( 'rows to process at a time', 'barebones' ); ?></label>
	<p class="description"><?php _e( 'Keep this low if you experience out-of-memory issues.', 'barebones' ); ?></p>

<?php
}

/**
 * Edit Delay Time setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_delay_time() {
?>

	<input name="_bb_converter_delay_time" type="text" id="_bb_converter_delay_time" value="<?php bb_form_option( '_bb_converter_delay_time', '1' ); ?>" class="small-text" />
	<label for="_bb_converter_delay_time"><?php _e( 'second(s) delay between each group of rows', 'barebones' ); ?></label>
	<p class="description"><?php _e( 'Keep this high to prevent too-many-connection issues.', 'barebones' ); ?></p>

<?php
}

/**
 * Edit Restart setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_restart() {
?>

	<input id="_bb_converter_restart" name="_bb_converter_restart" type="checkbox" id="_bb_converter_restart" value="1" <?php checked( get_option( '_bb_converter_restart', false ) ); ?> />
	<label for="_bb_converter_restart"><?php _e( 'Start a fresh conversion from the beginning', 'barebones' ); ?></label>
	<p class="description"><?php _e( 'You should clean old conversion information before starting over.', 'barebones' ); ?></p>

<?php
}

/**
 * Edit Clean setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_clean() {
?>

	<input id="_bb_converter_clean" name="_bb_converter_clean" type="checkbox" id="_bb_converter_clean" value="1" <?php checked( get_option( '_bb_converter_clean', false ) ); ?> />
	<label for="_bb_converter_clean"><?php _e( 'Purge all information from a previously attempted import', 'barebones' ); ?></label>
	<p class="description"><?php _e( 'Use this if an import failed and you want to remove that incomplete data.', 'barebones' ); ?></p>

<?php
}

/**
 * Edit Convert Users setting field
 *
 * @since barebones (1.0)
 */
function bb_converter_setting_callback_convert_users() {
?>

	<input id="_bb_converter_convert_users" name="_bb_converter_convert_users" type="checkbox" id="_bb_converter_convert_users" value="1" <?php checked( get_option( '_bb_converter_convert_users', false ) ); ?> />
	<label for="_bb_converter_convert_users"><?php _e( 'Attempt to import user accounts from previous forums', 'barebones' ); ?></label>
	<p class="description"><?php _e( 'Non-barebones passwords cannot be automatically converted. They will be converted as each user logs in.', 'barebones' ); ?></p>

<?php
}

/** Converter Page ************************************************************/

/**
 * The main settings page
 *
 * @uses screen_icon() To display the screen icon
 * @uses settings_fields() To output the hidden fields for the form
 * @uses do_settings_sections() To output the settings sections
 */
function bb_converter_settings() {
?>

	<div class="wrap">

		<?php screen_icon( 'tools' ); ?>

		<h2 class="nav-tab-wrapper"><?php bb_tools_admin_tabs( __( 'Import Forums', 'barebones' ) ); ?></h2>

		<form action="#" method="post" id="bbp-converter-settings">

			<?php settings_fields( 'barebones_converter' ); ?>

			<?php do_settings_sections( 'barebones_converter' ); ?>

			<p class="submit">
				<input type="button" name="submit" class="button-primary" id="bbp-converter-start" value="<?php esc_attr_e( 'Start', 'barebones' ); ?>" onclick="bbconverter_start()" />
				<input type="button" name="submit" class="button-primary" id="bbp-converter-stop" value="<?php esc_attr_e( 'Stop', 'barebones' ); ?>" onclick="bbconverter_stop()" />
				<img id="bbp-converter-progress" src="">
			</p>

			<div class="bbp-converter-updated" id="bbp-converter-message"></div>
		</form>
	</div>

<?php
}

/** Helpers *******************************************************************/

/**
 * Contextual help for Forums settings page
 *
 * @since barebones (1.0)
 * @uses get_current_screen()
 */
function bb_admin_settings_help() {

	$current_screen = get_current_screen();

	// Bail if current screen could not be found
	if ( empty( $current_screen ) )
		return;

	// Overview
	$current_screen->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'barebones' ),
		'content' => '<p>' . __( 'This screen provides access to all of the Forums settings.',                          'barebones' ) . '</p>' .
					 '<p>' . __( 'Please see the additional help tabs for more information on each indiviual section.', 'barebones' ) . '</p>'
	) );

	// Main Settings
	$current_screen->add_help_tab( array(
		'id'      => 'main_settings',
		'title'   => __( 'Main Settings', 'barebones' ),
		'content' => '<p>' . __( 'In the Main Settings you have a number of options:', 'barebones' ) . '</p>' .
					 '<p>' .
						'<ul>' .
							'<li>' . __( 'You can choose to lock a post after a certain number of minutes. "Locking post editing" will prevent the author from editing some amount of time after saving a post.',              'barebones' ) . '</li>' .
							'<li>' . __( '"Throttle time" is the amount of time required between posts from a single author. The higher the throttle time, the longer a user will need to wait between posting to the forum.', 'barebones' ) . '</li>' .
							'<li>' . __( 'Favorites are a way for users to save and later return to topics they favor. This is enabled by default.',                                                                           'barebones' ) . '</li>' .
							'<li>' . __( 'Subscriptions allow users to subscribe for notifications to topics that interest them. This is enabled by default.',                                                                 'barebones' ) . '</li>' .
							'<li>' . __( 'Topic-Tags allow users to filter topics between forums. This is enabled by default.',                                                                                                'barebones' ) . '</li>' .
							'<li>' . __( '"Anonymous Posting" allows guest users who do not have accounts on your site to both create topics as well as replies.',                                                             'barebones' ) . '</li>' .
							'<li>' . __( 'The Fancy Editor brings the luxury of the Visual editor and HTML editor from the traditional WordPress dashboard into your theme.',                                                  'barebones' ) . '</li>' .
							'<li>' . __( 'Auto-embed will embed the media content from a URL directly into the replies. For example: links to Flickr and YouTube.',                                                            'barebones' ) . '</li>' .
						'</ul>' .
					'</p>' .
					'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'barebones' ) . '</p>'
	) );

	// Per Page
	$current_screen->add_help_tab( array(
		'id'      => 'per_page',
		'title'   => __( 'Per Page', 'barebones' ),
		'content' => '<p>' . __( 'Per Page settings allow you to control the number of topics and replies appear on each page.',                                                    'barebones' ) . '</p>' .
					 '<p>' . __( 'This is comparable to the WordPress "Reading Settings" page, where you can set the number of posts that should show on blog pages and in feeds.', 'barebones' ) . '</p>' .
					 '<p>' . __( 'These are broken up into two separate groups: one for what appears in your theme, another for RSS feeds.',                                        'barebones' ) . '</p>'
	) );

	// Slugs
	$current_screen->add_help_tab( array(
		'id'      => 'slus',
		'title'   => __( 'Slugs', 'barebones' ),
		'content' => '<p>' . __( 'The Slugs section allows you to control the permalink structure for your forums.',                                                                                                            'barebones' ) . '</p>' .
					 '<p>' . __( '"Archive Slugs" are used as the "root" for your forums and topics. If you combine these values with existing page slugs, barebones will attempt to output the most correct title and content.', 'barebones' ) . '</p>' .
					 '<p>' . __( '"Single Slugs" are used as a prefix when viewing an individual forum, topic, reply, user, or view.',                                                                                          'barebones' ) . '</p>' .
					 '<p>' . __( 'In the event of a slug collision with WordPress or BuddyPress, a warning will appear next to the problem slug(s).', 'barebones' ) . '</p>'
	) );

	// Help Sidebar
	$current_screen->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'barebones' ) . '</strong></p>' .
		'<p>' . __( '<a href="http://codex.example.org" target="_blank">barebones Documentation</a>',    'barebones' ) . '</p>' .
		'<p>' . __( '<a href="http://example.org/forums/" target="_blank">barebones Support Forums</a>', 'barebones' ) . '</p>'
	);
}

/**
 * Disable a settings field if the value is forcibly set in barebones's global
 * options array.
 *
 * @since barebones (1.0)
 *
 * @param string $option_key
 */
function bb_maybe_admin_setting_disabled( $option_key = '' ) {
	disabled( isset( barebones()->options[$option_key] ) );
}

/**
 * Output settings API option
 *
 * @since barebones (1.0)
 *
 * @uses bb_get_bb_form_option()
 *
 * @param string $option
 * @param string $default
 * @param bool $slug
 */
function bb_form_option( $option, $default = '' , $slug = false ) {
	echo bb_get_form_option( $option, $default, $slug );
}
	/**
	 * Return settings API option
	 *
	 * @since barebones (1.0)
	 *
	 * @uses get_option()
	 * @uses esc_attr()
	 * @uses apply_filters()
	 *
	 * @param string $option
	 * @param string $default
	 * @param bool $slug
	 */
	function bb_get_form_option( $option, $default = '', $slug = false ) {

		// Get the option and sanitize it
		$value = get_option( $option, $default );

		// Slug?
		if ( true === $slug ) {
			$value = esc_attr( apply_filters( 'editable_slug', $value ) );

		// Not a slug
		} else {
			$value = esc_attr( $value );
		}

		// Fallback to default
		if ( empty( $value ) )
			$value = $default;

		// Allow plugins to further filter the output
		return apply_filters( 'bb_get_form_option', $value, $option );
	}

/**
 * Used to check if a barebones slug conflicts with an existing known slug.
 *
 * @since barebones (1.0)
 *
 * @param string $slug
 * @param string $default
 *
 * @uses bb_get_form_option() To get a sanitized slug string
 */
function bb_form_slug_conflict_check( $slug, $default ) {

	// Only set the slugs once ver page load
	static $the_core_slugs = array();

	// Get the form value
	$this_slug = bb_get_form_option( $slug, $default, true );

	if ( empty( $the_core_slugs ) ) {

		// Slugs to check
		$core_slugs = apply_filters( 'bb_slug_conflict_check', array(

			/** WordPress Core ****************************************************/

			// Core Post Types
			'post_base'       => array( 'name' => __( 'Posts',         'barebones' ), 'default' => 'post',          'context' => 'WordPress' ),
			'page_base'       => array( 'name' => __( 'Pages',         'barebones' ), 'default' => 'page',          'context' => 'WordPress' ),
			'revision_base'   => array( 'name' => __( 'Revisions',     'barebones' ), 'default' => 'revision',      'context' => 'WordPress' ),
			'attachment_base' => array( 'name' => __( 'Attachments',   'barebones' ), 'default' => 'attachment',    'context' => 'WordPress' ),
			'nav_menu_base'   => array( 'name' => __( 'Menus',         'barebones' ), 'default' => 'nav_menu_item', 'context' => 'WordPress' ),

			// Post Tags
			'tag_base'        => array( 'name' => __( 'Tag base',      'barebones' ), 'default' => 'tag',           'context' => 'WordPress' ),

			// Post Categories
			'category_base'   => array( 'name' => __( 'Category base', 'barebones' ), 'default' => 'category',      'context' => 'WordPress' ),

			/** barebones Core ******************************************************/

			// Forum archive slug
			'_bb_root_slug'          => array( 'name' => __( 'Forums base', 'barebones' ), 'default' => 'forums', 'context' => 'barebones' ),

			// Topic archive slug
			'_bb_topic_archive_slug' => array( 'name' => __( 'Topics base', 'barebones' ), 'default' => 'topics', 'context' => 'barebones' ),

			// Forum slug
			'_bb_forum_slug'         => array( 'name' => __( 'Forum slug',  'barebones' ), 'default' => 'forum',  'context' => 'barebones' ),

			// Topic slug
			'_bb_topic_slug'         => array( 'name' => __( 'Topic slug',  'barebones' ), 'default' => 'topic',  'context' => 'barebones' ),

			// Reply slug
			'_bb_reply_slug'         => array( 'name' => __( 'Reply slug',  'barebones' ), 'default' => 'reply',  'context' => 'barebones' ),

			// User profile slug
			'_bb_user_slug'          => array( 'name' => __( 'User base',   'barebones' ), 'default' => 'users',  'context' => 'barebones' ),

			// View slug
			'_bb_view_slug'          => array( 'name' => __( 'View base',   'barebones' ), 'default' => 'view',   'context' => 'barebones' ),

			// Topic tag slug
			'_bb_topic_tag_slug'     => array( 'name' => __( 'Topic tag slug', 'barebones' ), 'default' => 'topic-tag', 'context' => 'barebones' ),
		) );

		/** BuddyPress Core *******************************************************/

		if ( defined( 'BP_VERSION' ) ) {
			$bp = buddypress();

			// Loop through root slugs and check for conflict
			if ( !empty( $bp->pages ) ) {
				foreach ( $bp->pages as $page => $page_data ) {
					$page_base    = $page . '_base';
					$page_title   = sprintf( __( '%s page', 'barebones' ), $page_data->title );
					$core_slugs[$page_base] = array( 'name' => $page_title, 'default' => $page_data->slug, 'context' => 'BuddyPress' );
				}
			}
		}

		// Set the static
		$the_core_slugs = apply_filters( 'bb_slug_conflict', $core_slugs );
	}

	// Loop through slugs to check
	foreach( $the_core_slugs as $key => $value ) {

		// Get the slug
		$slug_check = bb_get_form_option( $key, $value['default'], true );

		// Compare
		if ( ( $slug != $key ) && ( $slug_check == $this_slug ) ) : ?>

			<span class="attention"><?php printf( __( 'Possible %1$s conflict: <strong>%2$s</strong>', 'barebones' ), $value['context'], $value['name'] ); ?></span>

		<?php endif;
	}
}
