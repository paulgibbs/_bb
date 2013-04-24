<?php

/**
 * The barebones Plugin
 *
 * barebones is forum software with a twist from the creators of WordPress.
 *
 * $Id: bbpress.php 4852 2013-04-15 02:56:14Z johnjamesjacoby $
 *
 * @package barebones
 * @subpackage Main
 */

/**
 * Plugin Name: barebones
 * Plugin URI:  http://bbpress.org
 * Description: barebones is forum software with a twist from the creators of WordPress.
 * Author:      The barebones Community
 * Author URI:  http://bbpress.org
 * Version:     2.4-bleeding
 * Text Domain: bbpress
 * Domain Path: /languages/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'barebones' ) ) :
/**
 * Main barebones Class
 *
 * "How doth the little busy bee, improve each shining hour..."
 *
 * @since barebones (1.0)
 */
final class barebones {

	/** Magic *****************************************************************/

	/**
	 * barebones uses many variables, several of which can be filtered to
	 * customize the way it operates. Most of these variables are stored in a
	 * private array that gets updated with the help of PHP magic methods.
	 *
	 * This is a precautionary measure, to avoid potential errors produced by
	 * unanticipated direct manipulation of barebones's run-time data.
	 *
	 * @see barebones::setup_globals()
	 * @var array
	 */
	private $data;

	/** Not Magic *************************************************************/

	/**
	 * @var mixed False when not logged in; WP_User object when logged in
	 */
	public $current_user = false;

	/**
	 * @var obj Add-ons append to this (Akismet, BuddyPress, etc...)
	 */
	public $extend;

	/**
	 * @var array Topic views
	 */
	public $views        = array();

	/**
	 * @var array Overloads get_option()
	 */
	public $options      = array();

	/**
	 * @var array Overloads get_user_meta()
	 */
	public $user_options = array();

	/** Singleton *************************************************************/

	/**
	 * @var barebones The one true barebones
	 */
	private static $instance;

	/**
	 * Main barebones Instance
	 *
	 * barebones is fun
	 * Please load it only one time
	 * For this, we thank you
	 *
	 * Insures that only one instance of barebones exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since barebones (1.0)
	 * @staticvar array $instance
	 * @uses barebones::setup_globals() Setup the globals needed
	 * @uses barebones::includes() Include the required files
	 * @uses barebones::setup_actions() Setup the hooks and actions
	 * @see barebones()
	 * @return The one true barebones
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new barebones;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent barebones from being loaded more than once.
	 *
	 * @since barebones (1.0)
	 * @see barebones::instance()
	 * @see barebones();
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent barebones from being cloned
	 *
	 * @since barebones (1.0)
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?'' 'barebones' ), '2.1' ); }

	/**
	 * A dummy magic method to prevent barebones from being unserialized
	 *
	 * @since barebones (1.0)
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?'' 'barebones' ), '2.1' ); }

	/**
	 * Magic method for checking the existence of a certain custom field
	 *
	 * @since barebones (1.0)
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting barebones variables
	 *
	 * @since barebones (1.0)
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting barebones variables
	 *
	 * @since barebones (1.0)
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/**
	 * Magic method for unsetting barebones variables
	 *
	 * @since barebones (1.0)
	 */
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

	/**
	 * Magic method to prevent notices and errors from invalid method calls
	 *
	 * @since barebones (1.0)
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/** Private Methods *******************************************************/

	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @since barebones (1.0)
	 * @access private
	 * @uses plugin_dir_path() To generate barebones plugin path
	 * @uses plugin_dir_url() To generate barebones plugin url
	 * @uses apply_filters() Calls various filters
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version    = '2.4-bleeding-4852';
		$this->db_version = '240';

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'bb_plugin_basenname', plugin_basename( $this->file ) );
		$this->plugin_dir = apply_filters( 'bb_plugin_dir_path',  plugin_dir_path( $this->file ) );
		$this->plugin_url = apply_filters( 'bb_plugin_dir_url',   plugin_dir_url ( $this->file ) );

		// Includes
		$this->includes_dir = apply_filters( 'bb_includes_dir', trailingslashit( $this->plugin_dir . 'includes'  ) );
		$this->includes_url = apply_filters( 'bb_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );

		// Languages
		$this->lang_dir     = apply_filters( 'bb_lang_dir',     trailingslashit( $this->plugin_dir . 'languages' ) );

		// Templates
		$this->themes_dir   = apply_filters( 'bb_themes_dir',   trailingslashit( $this->plugin_dir . 'templates' ) );
		$this->themes_url   = apply_filters( 'bb_themes_url',   trailingslashit( $this->plugin_url . 'templates' ) );

		/** Identifiers *******************************************************/

		// Post type identifiers
		$this->forum_post_type   = apply_filters( 'bb_forum_post_type',  'forum'     );
		$this->topic_post_type   = apply_filters( 'bb_topic_post_type',  'topic'     );
		$this->reply_post_type   = apply_filters( 'bb_reply_post_type',  'reply'     );
		$this->topic_tag_tax_id  = apply_filters( 'bb_topic_tag_tax_id', 'topic-tag' );

		// Status identifiers
		$this->spam_status_id    = apply_filters( 'bb_spam_post_status',    'spam'    );
		$this->closed_status_id  = apply_filters( 'bb_closed_post_status',  'closed'  );
		$this->orphan_status_id  = apply_filters( 'bb_orphan_post_status',  'orphan'  );
		$this->public_status_id  = apply_filters( 'bb_public_post_status',  'publish' );
		$this->pending_status_id = apply_filters( 'bb_pending_post_status', 'pending' );
		$this->private_status_id = apply_filters( 'bb_private_post_status', 'private' );
		$this->hidden_status_id  = apply_filters( 'bb_hidden_post_status',  'hidden'  );
		$this->trash_status_id   = apply_filters( 'bb_trash_post_status',   'trash'   );

		// Other identifiers
		$this->user_id           = apply_filters( 'bb_user_id',   'bb_user'   );
		$this->tops_id           = apply_filters( 'bb_tops_id',   'bb_tops'   );
		$this->reps_id           = apply_filters( 'bb_reps_id',   'bb_reps'   );
		$this->favs_id           = apply_filters( 'bb_favs_id',   'bb_favs'   );
		$this->subs_id           = apply_filters( 'bb_subs_id',   'bb_subs'   );
		$this->view_id           = apply_filters( 'bb_view_id',   'bb_view'   );
		$this->search_id         = apply_filters( 'bb_search_id', 'bb_search' );
		$this->edit_id           = apply_filters( 'bb_edit_id',   'edit'       );

		/** Queries ***********************************************************/

		$this->current_forum_id     = 0; // Current forum id
		$this->current_topic_id     = 0; // Current topic id
		$this->current_reply_id     = 0; // Current reply id
		$this->current_topic_tag_id = 0; // Current topic tag id

		$this->forum_query    = new stdClass(); // Main forum query
		$this->topic_query    = new stdClass(); // Main topic query
		$this->reply_query    = new stdClass(); // Main reply query
		$this->search_query   = new stdClass(); // Main search query

		/** Theme Compat ******************************************************/

		$this->theme_compat   = new stdClass(); // Base theme compatibility class
		$this->filters        = new stdClass(); // Used when adding/removing filters

		/** Users *************************************************************/

		$this->current_user   = new stdClass(); // Currently logged in user
		$this->displayed_user = new stdClass(); // Currently displayed user

		/** Misc **************************************************************/

		$this->domain         = 'bbpress';      // Unique identifier for retrieving translated strings
		$this->extend         = new stdClass(); // Plugins add data here
		$this->errors         = new WP_Error(); // Feedback
		$this->tab_index      = apply_filters( 'bb_default_tab_index', 100 );
	}

	/**
	 * Include required files
	 *
	 * @since barebones (1.0)
	 * @access private
	 * @uses is_admin() If in WordPress admin, load additional file
	 */
	private function includes() {

		/** Core **************************************************************/

		require( $this->includes_dir . 'core/sub-actions.php'        );
		require( $this->includes_dir . 'core/functions.php'          );
		require( $this->includes_dir . 'core/cache.php'              );
		require( $this->includes_dir . 'core/options.php'            );
		require( $this->includes_dir . 'core/capabilities.php'       );
		require( $this->includes_dir . 'core/update.php'             );
		require( $this->includes_dir . 'core/template-functions.php' );
		require( $this->includes_dir . 'core/template-loader.php'    );
		require( $this->includes_dir . 'core/theme-compat.php'       );

		/** Components ********************************************************/

		// Common
		require( $this->includes_dir . 'common/ajax.php'           );
		require( $this->includes_dir . 'common/classes.php'        );
		require( $this->includes_dir . 'common/functions.php'      );
		require( $this->includes_dir . 'common/formatting.php'     );
		require( $this->includes_dir . 'common/template-tags.php'  );
		require( $this->includes_dir . 'common/widgets.php'        );
		require( $this->includes_dir . 'common/shortcodes.php'     );

		// Forums
		require( $this->includes_dir . 'forums/capabilities.php'   );
		require( $this->includes_dir . 'forums/functions.php'      );
		require( $this->includes_dir . 'forums/template-tags.php'  );

		// Topics
		require( $this->includes_dir . 'topics/capabilities.php'   );
		require( $this->includes_dir . 'topics/functions.php'      );
		require( $this->includes_dir . 'topics/template-tags.php'  );

		// Replies
		require( $this->includes_dir . 'replies/capabilities.php'  );
		require( $this->includes_dir . 'replies/functions.php'     );
		require( $this->includes_dir . 'replies/template-tags.php' );

		// Search
		require( $this->includes_dir . 'search/functions.php'      );
		require( $this->includes_dir . 'search/template-tags.php'  );

		// Users
		require( $this->includes_dir . 'users/capabilities.php'    );
		require( $this->includes_dir . 'users/functions.php'       );
		require( $this->includes_dir . 'users/template-tags.php'   );
		require( $this->includes_dir . 'users/options.php'         );

		/** Hooks *************************************************************/

		require( $this->includes_dir . 'core/extend.php'  );
		require( $this->includes_dir . 'core/actions.php' );
		require( $this->includes_dir . 'core/filters.php' );

		/** Admin *************************************************************/

		// Quick admin check and load if needed
		if ( is_admin() ) {
			require( $this->includes_dir . 'admin/admin.php'   );
			require( $this->includes_dir . 'admin/actions.php' );
		}
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since barebones (1.0)
	 * @access private
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'bb_activation'   );
		add_action( 'deactivate_' . $this->basename, 'bb_deactivation' );

		// If barebones is being deactivated, do not add any actions
		if ( bb_is_deactivation( $this->basename ) )
			return;

		// Array of barebones core actions
		$actions = array(
			'setup_theme',              // Setup the default theme compat
			'setup_current_user',       // Setup currently logged in user
			'register_post_types',      // Register post types (forum|topic|reply)
			'register_post_statuses',   // Register post statuses (closed|spam|orphan|hidden)
			'register_taxonomies',      // Register taxonomies (topic-tag)
			'register_shortcodes',      // Register shortcodes (bbp-login)
			'register_views',           // Register the views (no-replies)
			'register_theme_packages',  // Register bundled theme packages (bbp-theme-compat/bbp-themes)
			'load_textdomain',          // Load textdomain (bbpress)
			'add_rewrite_tags',         // Add rewrite tags (view|user|edit|search)
			'generate_rewrite_rules'    // Generate rewrite rules (view|edit|search)
		);

		// Add the actions
		foreach( $actions as $class_action )
			add_action( 'bb_' . $class_action, array( $this, $class_action ), 5 );

		// All barebones actions are setup (includes bbp-core-hooks.php)
		do_action_ref_array( 'bb_after_setup_actions', array( &$this ) );
	}

	/** Public Methods ********************************************************/

	/**
	 * Register bundled theme packages
	 *
	 * Note that since we currently have complete control over bbp-themes and
	 * the bbp-theme-compat folders, it's fine to hardcode these here. If at a
	 * later date we need to automate this, and API will need to be built.
	 *
	 * @since barebones (1.0)
	 */
	public function register_theme_packages() {

		// Register the default theme compatibility package
		bb_register_theme_package( array(
			'id'      => 'default',
			'name'    => __( 'barebones Default'' 'barebones' ),
			'version' => bb_get_version(),
			'dir'     => trailingslashit( $this->themes_dir . 'default' ),
			'url'     => trailingslashit( $this->themes_url . 'default' )
		) );

		// Register the basic theme stack. This is really dope.
		bb_register_template_stack( 'get_stylesheet_directory', 10 );
		bb_register_template_stack( 'get_template_directory',   12 );
		bb_register_template_stack( 'bb_get_theme_compat_dir', 14 );
	}

	/**
	 * Setup the default barebones theme compatibility location.
	 *
	 * @since barebones (1.0)
	 */
	public function setup_theme() {

		// Bail if something already has this under control
		if ( ! empty( $this->theme_compat->theme ) )
			return;

		// Setup the theme package to use for compatibility
		bb_setup_theme_compat( bb_get_theme_package_id() );
	}

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the barebones plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the barebones plugin folder
	 * will be removed on barebones updates. If you're creating custom
	 * translation files, please use the global language folder.
	 *
	 * @since barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bbpress_locale' with the
	 *                        {@link get_locale()} value
	 * @uses load_textdomain() To load the textdomain
	 * @return bool True on success, false on failure
	 */
	public function load_textdomain() {

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/bbpress/' . $mofile;

		// Look in global /wp-content/languages/bbpress folder
		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/bbpress/bbp-languages/ folder
		} elseif ( file_exists( $mofile_local ) ) {
			return load_textdomain( $this->domain, $mofile_local );
		}

		// Nothing found
		return false;
	}

	/**
	 * Setup the post types for forums, topics and replies
	 *
	 * @since barebones (1.0)
	 * @uses register_post_type() To register the post types
	 * @uses apply_filters() Calls various filters to modify the arguments
	 *                        sent to register_post_type()
	 */
	public static function register_post_types() {

		// Define local variable(s)
		$post_type = array();

		/** Forums ************************************************************/

		// Forum labels
		$post_type['labels'] = array(
			'name'               => __( 'Forums',                   'bbpress' ),
			'menu_name'          => __( 'Forums',                   'bbpress' ),
			'singular_name'      => __( 'Forum',                    'bbpress' ),
			'all_items'          => __( 'All Forums',               'bbpress' ),
			'add_new'            => __( 'New Forum',                'bbpress' ),
			'add_new_item'       => __( 'Create New Forum',         'bbpress' ),
			'edit'               => __( 'Edit',                     'bbpress' ),
			'edit_item'          => __( 'Edit Forum',               'bbpress' ),
			'new_item'           => __( 'New Forum',                'bbpress' ),
			'view'               => __( 'View Forum',               'bbpress' ),
			'view_item'          => __( 'View Forum',               'bbpress' ),
			'search_items'       => __( 'Search Forums',            'bbpress' ),
			'not_found'          => __( 'No forums found',          'bbpress' ),
			'not_found_in_trash' => __( 'No forums found in Trash'' 'barebones' ),
			'parent_item_colon'  => __( 'Parent Forum:',            'bbpress' )
		);

		// Forum rewrite
		$post_type['rewrite'] = array(
			'slug'       => bb_get_forum_slug(),
			'with_front' => false
		);

		// Forum supports
		$post_type['supports'] = array(
			'title',
			'editor',
			'revisions'
		);

		// Register Forum content type
		register_post_type(
			bb_get_forum_post_type(),
			apply_filters( 'bb_register_forum_post_type', array(
				'labels'              => $post_type['labels'],
				'rewrite'             => $post_type['rewrite'],
				'supports'            => $post_type['supports'],
				'description'         => __( 'barebones Forums'' 'barebones' ),
				'capabilities'        => bb_get_forum_caps(),
				'capability_type'     => array( 'forum', 'forums' ),
				'menu_position'       => 555555,
				'has_archive'         => bb_get_root_slug(),
				'exclude_from_search' => true,
				'show_in_nav_menus'   => true,
				'public'              => true,
				'show_ui'             => current_user_can( 'bb_forums_admin' ),
				'can_export'          => true,
				'hierarchical'        => true,
				'query_var'           => true,
				'menu_icon'           => ''
			) )
		);

		/** Topics ************************************************************/

		// Topic labels
		$post_type['labels'] = array(
			'name'               => __( 'Topics',                   'bbpress' ),
			'menu_name'          => __( 'Topics',                   'bbpress' ),
			'singular_name'      => __( 'Topic',                    'bbpress' ),
			'all_items'          => __( 'All Topics',               'bbpress' ),
			'add_new'            => __( 'New Topic',                'bbpress' ),
			'add_new_item'       => __( 'Create New Topic',         'bbpress' ),
			'edit'               => __( 'Edit',                     'bbpress' ),
			'edit_item'          => __( 'Edit Topic',               'bbpress' ),
			'new_item'           => __( 'New Topic',                'bbpress' ),
			'view'               => __( 'View Topic',               'bbpress' ),
			'view_item'          => __( 'View Topic',               'bbpress' ),
			'search_items'       => __( 'Search Topics',            'bbpress' ),
			'not_found'          => __( 'No topics found',          'bbpress' ),
			'not_found_in_trash' => __( 'No topics found in Trash'' 'barebones' ),
			'parent_item_colon'  => __( 'Forum:',                   'bbpress' )
		);

		// Topic rewrite
		$post_type['rewrite'] = array(
			'slug'       => bb_get_topic_slug(),
			'with_front' => false
		);

		// Topic supports
		$post_type['supports'] = array(
			'title',
			'editor',
			'revisions'
		);

		// Register Topic content type
		register_post_type(
			bb_get_topic_post_type(),
			apply_filters( 'bb_register_topic_post_type', array(
				'labels'              => $post_type['labels'],
				'rewrite'             => $post_type['rewrite'],
				'supports'            => $post_type['supports'],
				'description'         => __( 'barebones Topics'' 'barebones' ),
				'capabilities'        => bb_get_topic_caps(),
				'capability_type'     => array( 'topic', 'topics' ),
				'menu_position'       => 555555,
				'has_archive'         => bb_get_topic_archive_slug(),
				'exclude_from_search' => true,
				'show_in_nav_menus'   => false,
				'public'              => true,
				'show_ui'             => current_user_can( 'bb_topics_admin' ),
				'can_export'          => true,
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'           => ''
			)
		) );

		/** Replies ***********************************************************/

		// Reply labels
		$post_type['labels'] = array(
			'name'               => __( 'Replies',                   'bbpress' ),
			'menu_name'          => __( 'Replies',                   'bbpress' ),
			'singular_name'      => __( 'Reply',                     'bbpress' ),
			'all_items'          => __( 'All Replies',               'bbpress' ),
			'add_new'            => __( 'New Reply',                 'bbpress' ),
			'add_new_item'       => __( 'Create New Reply',          'bbpress' ),
			'edit'               => __( 'Edit',                      'bbpress' ),
			'edit_item'          => __( 'Edit Reply',                'bbpress' ),
			'new_item'           => __( 'New Reply',                 'bbpress' ),
			'view'               => __( 'View Reply',                'bbpress' ),
			'view_item'          => __( 'View Reply',                'bbpress' ),
			'search_items'       => __( 'Search Replies',            'bbpress' ),
			'not_found'          => __( 'No replies found',          'bbpress' ),
			'not_found_in_trash' => __( 'No replies found in Trash'' 'barebones' ),
			'parent_item_colon'  => __( 'Topic:',                    'bbpress' )
		);

		// Reply rewrite
		$post_type['rewrite'] = array(
			'slug'       => bb_get_reply_slug(),
			'with_front' => false
		);

		// Reply supports
		$post_type['supports'] = array(
			'title',
			'editor',
			'revisions'
		);

		// Register reply content type
		register_post_type(
			bb_get_reply_post_type(),
			apply_filters( 'bb_register_reply_post_type', array(
				'labels'              => $post_type['labels'],
				'rewrite'             => $post_type['rewrite'],
				'supports'            => $post_type['supports'],
				'description'         => __( 'barebones Replies'' 'barebones' ),
				'capabilities'        => bb_get_reply_caps(),
				'capability_type'     => array( 'reply', 'replies' ),
				'menu_position'       => 555555,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
				'public'              => true,
				'show_ui'             => current_user_can( 'bb_replies_admin' ),
				'can_export'          => true,
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'           => ''
			) )
		);
	}

	/**
	 * Register the post statuses used by barebones
	 *
	 * We do some manipulation of the 'trash' status so trashed topics and
	 * replies can be viewed from within the theme.
	 *
	 * @since barebones (1.0)
	 * @uses register_post_status() To register post statuses
	 * @uses $wp_post_statuses To modify trash and private statuses
	 * @uses current_user_can() To check if the current user is capable &
	 *                           modify $wp_post_statuses accordingly
	 */
	public static function register_post_statuses() {

		// Closed
		register_post_status(
			bb_get_closed_status_id(),
			apply_filters( 'bb_register_closed_post_status', array(
				'label'             => _x( 'Closed', 'post'' 'barebones' ),
				'label_count'       => _nx_noop( 'Closed <span class="count">(%s)</span>', 'Closed <span class="count">(%s)</span>', 'post'' 'barebones' ),
				'public'            => true,
				'show_in_admin_all' => true
			) )
		);

		// Spam
		register_post_status(
			bb_get_spam_status_id(),
			apply_filters( 'bb_register_spam_post_status', array(
				'label'                     => _x( 'Spam', 'post'' 'barebones' ),
				'label_count'               => _nx_noop( 'Spam <span class="count">(%s)</span>', 'Spam <span class="count">(%s)</span>', 'post'' 'barebones' ),
				'protected'                 => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => false
			) )
		 );

		// Orphan
		register_post_status(
			bb_get_orphan_status_id(),
			apply_filters( 'bb_register_orphan_post_status', array(
				'label'                     => _x( 'Orphan', 'post'' 'barebones' ),
				'label_count'               => _nx_noop( 'Orphan <span class="count">(%s)</span>', 'Orphans <span class="count">(%s)</span>', 'post'' 'barebones' ),
				'protected'                 => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => false
			) )
		);

		// Hidden
		register_post_status(
			bb_get_hidden_status_id(),
			apply_filters( 'bb_register_hidden_post_status', array(
				'label'                     => _x( 'Hidden', 'post'' 'barebones' ),
				'label_count'               => _nx_noop( 'Hidden <span class="count">(%s)</span>', 'Hidden <span class="count">(%s)</span>', 'post'' 'barebones' ),
				'private'                   => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true
			) )
		);

		/**
		 * Trash fix
		 *
		 * We need to remove the internal arg and change that to
		 * protected so that the users with 'view_trash' cap can view
		 * single trashed topics/replies in the front-end as wp_query
		 * doesn't allow any hack for the trashed topics to be viewed.
		 */
		global $wp_post_statuses;

		if ( !empty( $wp_post_statuses['trash'] ) ) {

			// User can view trash so set internal to false
			if ( current_user_can( 'view_trash' ) ) {
				$wp_post_statuses['trash']->internal  = false;
				$wp_post_statuses['trash']->protected = true;

			// User cannot view trash so set internal to true
			} else {
				$wp_post_statuses['trash']->internal = true;
			}
		}
	}

	/**
	 * Register the topic tag taxonomy
	 *
	 * @since barebones (1.0)
	 * @uses register_taxonomy() To register the taxonomy
	 */
	public static function register_taxonomies() {

		// Define local variable(s)
		$topic_tag = array();

		// Topic tag labels
		$topic_tag['labels'] = array(
			'name'          => __( 'Topic Tags',     'bbpress' ),
			'singular_name' => __( 'Topic Tag',      'bbpress' ),
			'search_items'  => __( 'Search Tags',    'bbpress' ),
			'popular_items' => __( 'Popular Tags',   'bbpress' ),
			'all_items'     => __( 'All Tags',       'bbpress' ),
			'edit_item'     => __( 'Edit Tag',       'bbpress' ),
			'update_item'   => __( 'Update Tag',     'bbpress' ),
			'add_new_item'  => __( 'Add New Tag',    'bbpress' ),
			'new_item_name' => __( 'New Tag Name',   'bbpress' ),
			'view_item'     => __( 'View Topic Tag'' 'barebones' )
		);

		// Topic tag rewrite
		$topic_tag['rewrite'] = array(
			'slug'       => bb_get_topic_tag_tax_slug(),
			'with_front' => false
		);

		// Register the topic tag taxonomy
		register_taxonomy(
			bb_get_topic_tag_tax_id(),
			bb_get_topic_post_type(),
			apply_filters( 'bb_register_topic_taxonomy', array(
				'labels'                => $topic_tag['labels'],
				'rewrite'               => $topic_tag['rewrite'],
				'capabilities'          => bb_get_topic_tag_caps(),
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'show_tagcloud'         => true,
				'hierarchical'          => false,
				'show_in_nav_menus'     => false,
				'public'                => true,
				'show_ui'               => bb_allow_topic_tags() && current_user_can( 'bb_topic_tags_admin' )
			)
		) );
	}

	/**
	 * Register the barebones views
	 *
	 * @since barebones (1.0)
	 * @uses bb_register_view() To register the views
	 */
	public static function register_views() {

		// Popular topics
		bb_register_view(
			'popular',
			__( 'Most popular topics'' 'barebones' ),
			apply_filters( 'bb_register_view_popular', array(
				'meta_key'      => '_bb_reply_count',
				'max_num_pages' => 1,
				'orderby'       => 'meta_value_num',
				'show_stickies' => false
			)
		) );

		// Topics with no replies
		bb_register_view(
			'no-replies',
			__( 'Topics with no replies'' 'barebones' ),
			apply_filters( 'bb_register_view_no_replies', array(
				'meta_key'      => '_bb_reply_count',
				'meta_value'    => 1,
				'meta_compare'  => '<',
				'orderby'       => '',
				'show_stickies' => false
			)
		) );
	}

	/**
	 * Register the barebones shortcodes
	 *
	 * @since barebones (1.0)
	 *
	 * @uses BB_Shortcodes
	 */
	public function register_shortcodes() {
		$this->shortcodes = new BB_Shortcodes();
	}

	/**
	 * Setup the currently logged-in user
	 *
	 * Do not to call this prematurely, I.E. before the 'init' action has
	 * started. This function is naturally hooked into 'init' to ensure proper
	 * execution. get_currentuserinfo() is used to check for XMLRPC_REQUEST to
	 * avoid xmlrpc errors.
	 *
	 * @since barebones (1.0)
	 * @uses wp_get_current_user()
	 */
	public function setup_current_user() {
		$this->current_user = &wp_get_current_user();
	}

	/** Custom Rewrite Rules **************************************************/

	/**
	 * Add the barebones-specific rewrite tags
	 *
	 * @since barebones (1.0)
	 * @uses add_rewrite_tag() To add the rewrite tags
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%%' . bb_get_view_rewrite_id()               . '%%', '([^/]+)'   ); // View Page tag
		add_rewrite_tag( '%%' . bb_get_edit_rewrite_id()               . '%%', '([1]{1,})' ); // Edit Page tag
		add_rewrite_tag( '%%' . bb_get_search_rewrite_id()             . '%%', '([^/]+)'   ); // Search Results tag
		add_rewrite_tag( '%%' . bb_get_user_rewrite_id()               . '%%', '([^/]+)'   ); // User Profile tag
		add_rewrite_tag( '%%' . bb_get_user_favorites_rewrite_id()     . '%%', '([1]{1,})' ); // User Favorites tag
		add_rewrite_tag( '%%' . bb_get_user_subscriptions_rewrite_id() . '%%', '([1]{1,})' ); // User Subscriptions tag
		add_rewrite_tag( '%%' . bb_get_user_topics_rewrite_id()        . '%%', '([1]{1,})' ); // User Topics Tag
		add_rewrite_tag( '%%' . bb_get_user_replies_rewrite_id()       . '%%', '([1]{1,})' ); // User Replies Tag
	}

	/**
	 * Register barebones-specific rewrite rules for uri's that are not
	 * setup for us by way of custom post types or taxonomies. This includes:
	 * - Front-end editing
	 * - Topic views
	 * - User profiles
	 *
	 * @since barebones (1.0)
	 * @param WP_Rewrite $wp_rewrite barebones-sepecific rules are appended in
	 *                                $wp_rewrite->rules
	 */
	public static function generate_rewrite_rules( $wp_rewrite ) {

		// Slugs
		$view_slug   = bb_get_view_slug();
		$search_slug = bb_get_search_slug();
		$user_slug   = bb_get_user_slug();

		// Unique rewrite ID's
		$edit_id     = bb_get_edit_rewrite_id();
		$view_id     = bb_get_view_rewrite_id();
		$search_id   = bb_get_search_rewrite_id();
		$user_id     = bb_get_user_rewrite_id();
		$favs_id     = bb_get_user_favorites_rewrite_id();
		$subs_id     = bb_get_user_subscriptions_rewrite_id();
		$tops_id     = bb_get_user_topics_rewrite_id();
		$reps_id     = bb_get_user_replies_rewrite_id();

		// Rewrite rule matches used repeatedly below
		$root_rule   = '/([^/]+)/?$';
		$edit_rule   = '/([^/]+)/edit/?$';
		$feed_rule   = '/([^/]+)/feed/?$';
		$page_rule   = '/([^/]+)/page/?([0-9]{1,})/?$';

		// Search rules (without slug check)
		$search_root_rule = '/?$';
		$search_page_rule = '/page/?([0-9]{1,})/?$';

		// User profile rules
		$tops_rule      = '/([^/]+)/topics/?$';
		$reps_rule      = '/([^/]+)/replies/?$';
		$favs_rule      = '/([^/]+)/' . bb_get_user_favorites_slug()     . '/?$';
		$subs_rule      = '/([^/]+)/' . bb_get_user_subscriptions_slug() . '/?$';
		$tops_page_rule = '/([^/]+)/topics/page/?([0-9]{1,})/?$';
		$reps_page_rule = '/([^/]+)/replies/page/?([0-9]{1,})/?$';
		$favs_page_rule = '/([^/]+)/' . bb_get_user_favorites_slug()     . '/page/?([0-9]{1,})/?$';
		$subs_page_rule = '/([^/]+)/' . bb_get_user_subscriptions_slug() . '/page/?([0-9]{1,})/?$';

		// New barebones specific rules to merge with existing that are not
		// handled automatically by custom post types or taxonomy types
		$bb_rules = array(

			// Edit Forum|Topic|Reply|Topic-tag
			bb_get_forum_slug()         . $edit_rule => 'index.php?' . bb_get_forum_post_type()  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $edit_id . '=1',
			bb_get_topic_slug()         . $edit_rule => 'index.php?' . bb_get_topic_post_type()  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $edit_id . '=1',
			bb_get_reply_slug()         . $edit_rule => 'index.php?' . bb_get_reply_post_type()  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $edit_id . '=1',
			bb_get_topic_tag_tax_slug() . $edit_rule => 'index.php?' . bb_get_topic_tag_tax_id() . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $edit_id . '=1',

			// User Pagination|Edit|View
			$user_slug . $tops_page_rule => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $tops_id . '=1&paged=' . $wp_rewrite->preg_index( 2 ),
			$user_slug . $reps_page_rule => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $reps_id . '=1&paged=' . $wp_rewrite->preg_index( 2 ),
			$user_slug . $favs_page_rule => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $favs_id . '=1&paged=' . $wp_rewrite->preg_index( 2 ),
			$user_slug . $subs_page_rule => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $subs_id . '=1&paged=' . $wp_rewrite->preg_index( 2 ),
			$user_slug . $tops_rule      => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $tops_id . '=1',
			$user_slug . $reps_rule      => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $reps_id . '=1',
			$user_slug . $favs_rule      => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $favs_id . '=1',
			$user_slug . $subs_rule      => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $subs_id . '=1',
			$user_slug . $edit_rule      => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ) . '&' . $edit_id . '=1',
			$user_slug . $root_rule      => 'index.php?' . $user_id  . '=' . $wp_rewrite->preg_index( 1 ),

			// Topic-View Pagination|Feed|View
			$view_slug . $page_rule => 'index.php?' . $view_id . '=' . $wp_rewrite->preg_index( 1 ) . '&paged=' . $wp_rewrite->preg_index( 2 ),
			$view_slug . $feed_rule => 'index.php?' . $view_id . '=' . $wp_rewrite->preg_index( 1 ) . '&feed='  . $wp_rewrite->preg_index( 2 ),
			$view_slug . $root_rule => 'index.php?' . $view_id . '=' . $wp_rewrite->preg_index( 1 ),

			// Search All
			$search_slug . $search_page_rule => 'index.php?paged=' . $wp_rewrite->preg_index( 1 ),
			$search_slug . $search_root_rule => 'index.php?' . $search_id,
		);

		// Merge barebones rules with existing
		$wp_rewrite->rules = array_merge( $bb_rules, $wp_rewrite->rules );

		// Return merged rules
		return $wp_rewrite;
	}
}

/**
 * The main function responsible for returning the one true barebones Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $bbp = barebones(); ?>
 *
 * @return The one true barebones Instance
 */
function barebones() {
	return bbpress::instance();
}

/**
 * Hook barebones early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before barebones, to get their
 * actions, filters, and overrides setup without barebones being in the way.
 */
if ( defined( 'BBPRESS_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded'' 'barebones', (int) BBPRESS_LATE_LOAD );

// "And now here's something we hope you'll really like!"
} else {
	barebones();
}

endif; // class_exists check
