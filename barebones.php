<?php
/**
 * The barebones plugin
 *
 * @package barebones
 */

/**
 * Plugin Name: barebones
 * Plugin URI:  http://example.org
 * Description: barebones helps you quickly start building a powerful, modern WordPress plugin.
 * Author:      You
 * Author URI:  http://example.org
 * Version:     1.0
 * Text Domain: barebones
 * Domain Path: ../../languages/plugins/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'barebones' ) ) :
/**
 * Main barebones class
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
	 * @var obj Add-ons append to this (Akismet, BuddyPress, etc...)
	 */
	public $extend;

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
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'barebones' ), '1.0' ); }

	/**
	 * A dummy magic method to prevent barebones from being unserialized
	 *
	 * @since barebones (1.0)
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'barebones' ), '1.0' ); }

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

		$this->version    = '1.0-bleeding-1';
		$this->db_version = '117';


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
		$this->topic_tag_tax_id  = apply_filters( 'bb_topic_tag_tax_id', 'topic-tag' );

		// Other identifiers
		$this->edit_id           = apply_filters( 'bb_edit_id', 'edit' );


		/** Queries ***********************************************************/

		$this->current_forum_id  = 0; // Current forum id
		$this->forum_query       = new stdClass(); // Main forum query


		/** Theme Compat ******************************************************/

		$this->theme_compat   = new stdClass(); // Base theme compatibility class
		$this->filters        = new stdClass(); // Used when adding/removing filters


		/** Misc **************************************************************/

		$this->domain         = 'barebones';      // Unique identifier for retrieving translated strings
		$this->extend         = new stdClass(); // Plugins add data here
		$this->errors         = new WP_Error(); // Feedback
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
			'register_post_types',      // Register post types (forum|topic|reply)
			'register_post_statuses',   // Register post statuses (closed|spam|orphan|hidden)
			'register_taxonomies',      // Register taxonomies (topic-tag)
			'register_shortcodes',      // Register shortcodes (bbp-login)
			'register_views',           // Register the views (no-replies)
			'register_theme_packages',  // Register bundled theme packages (bbp-theme-compat/bbp-themes)
			'load_textdomain',          // Load textdomain (barebones)
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
			'name'    => __( 'barebones Default', 'barebones' ),
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
	 * Load the translation file for current language. Checks the default languages folder.
	 *
	 * @since barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'barebones_locale' with the
	 *                        {@link get_locale()} value
	 * @uses load_textdomain() To load the textdomain
	 * @return bool True on success, false on failure
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale',  get_locale(), $this->domain );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Look in global /wp-content/languages/plugins/ folder
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

		if ( file_exists( $mofile_global ) )
			load_textdomain( $this->domain, $mofile_global );
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
			'name'               => __( 'Forums',                   'barebones' ),
			'menu_name'          => __( 'Forums',                   'barebones' ),
			'singular_name'      => __( 'Forum',                    'barebones' ),
			'all_items'          => __( 'All Forums',               'barebones' ),
			'add_new'            => __( 'New Forum',                'barebones' ),
			'add_new_item'       => __( 'Create New Forum',         'barebones' ),
			'edit'               => __( 'Edit',                     'barebones' ),
			'edit_item'          => __( 'Edit Forum',               'barebones' ),
			'new_item'           => __( 'New Forum',                'barebones' ),
			'view'               => __( 'View Forum',               'barebones' ),
			'view_item'          => __( 'View Forum',               'barebones' ),
			'search_items'       => __( 'Search Forums',            'barebones' ),
			'not_found'          => __( 'No forums found',          'barebones' ),
			'not_found_in_trash' => __( 'No forums found in Trash', 'barebones' ),
			'parent_item_colon'  => __( 'Parent Forum:',            'barebones' )
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
				'description'         => __( 'barebones Forums', 'barebones' ),
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
			'name'          => __( 'Topic Tags',     'barebones' ),
			'singular_name' => __( 'Topic Tag',      'barebones' ),
			'search_items'  => __( 'Search Tags',    'barebones' ),
			'popular_items' => __( 'Popular Tags',   'barebones' ),
			'all_items'     => __( 'All Tags',       'barebones' ),
			'edit_item'     => __( 'Edit Tag',       'barebones' ),
			'update_item'   => __( 'Update Tag',     'barebones' ),
			'add_new_item'  => __( 'Add New Tag',    'barebones' ),
			'new_item_name' => __( 'New Tag Name',   'barebones' ),
			'view_item'     => __( 'View Topic Tag', 'barebones' )
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
	 * Register the barebones shortcodes
	 *
	 * @since barebones (1.0)
	 *
	 * @uses BB_Shortcodes
	 */
	public function register_shortcodes() {
		$this->shortcodes = new BB_Shortcodes();
	}


	/** Custom Rewrite Rules **************************************************/

	/**
	 * Add the barebones-specific rewrite tags
	 *
	 * @since barebones (1.0)
	 * @uses add_rewrite_tag() To add the rewrite tags
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%%' . bb_get_view_rewrite_id() . '%%', '([^/]+)'   ); // View Page tag
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
	return barebones::instance();
}

/**
 * Hook barebones early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before barebones, to get their
 * actions, filters, and overrides setup without barebones being in the way.
 */
if ( defined( 'BAREBONES_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'barebones', (int) BAREBONES_LATE_LOAD );

} else {
	barebones();
}

endif; // class_exists check
