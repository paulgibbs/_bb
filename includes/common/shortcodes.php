<?php

/**
 * Barebones Shortcodes
 *
 * @package Barebones
 * @subpackage Shortcodes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BB_Shortcodes' ) ) :
/**
 * Barebones Shortcode Class
 *
 * @since Barebones (1.0)
 */
class BB_Shortcodes {

	/** Vars ******************************************************************/

	/**
	 * @var array Shortcode => function
	 */
	public $codes = array();

	/** Functions *************************************************************/

	/**
	 * Add the register_shortcodes action to bb_init
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses setup_globals()
	 * @uses add_shortcodes()
	 */
	public function __construct() {
		$this->setup_globals();
		$this->add_shortcodes();
	}

	/**
	 * Shortcode globals
	 *
	 * @since Barebones (1.0)
	 * @access private
	 *
	 * @uses apply_filters()
	 */
	private function setup_globals() {

		// Setup the shortcodes
		$this->codes = apply_filters( 'bb_shortcodes', array(

			/** Forums ********************************************************/

			'bb-forum-index'      => array( $this, 'display_forum_index'   ), // Forum Index
			'bb-forum-form'       => array( $this, 'display_forum_form'    ), // Topic form
			'bb-single-forum'     => array( $this, 'display_forum'         ), // Specific forum - pass an 'id' attribute

			/** Topics ********************************************************/

			'bb-topic-index'      => array( $this, 'display_topic_index'   ), // Topic index
			'bb-topic-form'       => array( $this, 'display_topic_form'    ), // Topic form
			'bb-single-topic'     => array( $this, 'display_topic'         ), // Specific topic - pass an 'id' attribute

			/** Topic Tags ****************************************************/

			'bb-topic-tags'       => array( $this, 'display_topic_tags'    ), // All topic tags in a cloud
			'bb-single-tag'       => array( $this, 'display_topics_of_tag' ), // Topics of Tag

			/** Replies *******************************************************/

			'bb-reply-form'       => array( $this, 'display_reply_form'    ), // Reply form
			'bb-single-reply'     => array( $this, 'display_reply'         ), // Specific reply - pass an 'id' attribute

			/** Views *********************************************************/

			'bb-single-view'      => array( $this, 'display_view'          ), // Single view

			/** Search ********************************************************/

			'bb-search-form'      => array( $this, 'display_search_form'   ), // Search form
			'bb-search'           => array( $this, 'display_search'        ), // Search

			/** Account *******************************************************/

			'bb-login'            => array( $this, 'display_login'         ), // Login
			'bb-register'         => array( $this, 'display_register'      ), // Register
			'bb-lost-pass'        => array( $this, 'display_lost_pass'     ), // Lost Password

			/** Others *******************************************************/

			'bb-stats'            => array( $this, 'display_stats'         ), // Stats
		) );
	}

	/**
	 * Register the barebones shortcodes
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses add_shortcode()
	 * @uses do_action()
	 */
	private function add_shortcodes() {
		foreach( (array) $this->codes as $code => $function ) {
			add_shortcode( $code, $function );
		}
	}

	/**
	 * Unset some globals in the $bbp object that hold query related info
	 *
	 * @since Barebones (1.0)
	 */
	private function unset_globals() {
		$bbp = barebones();

		// Unset global queries
		$bb->forum_query  = new stdClass;
		$bb->topic_query  = new stdClass;
		$bb->reply_query  = new stdClass;
		$bb->search_query = new stdClass;

		// Unset global ID's
		$bb->current_forum_id     = 0;
		$bb->current_topic_id     = 0;
		$bb->current_reply_id     = 0;
		$bb->current_topic_tag_id = 0;

		// Reset the post data
		wp_reset_postdata();
	}

	/** Output Buffers ********************************************************/

	/**
	 * Start an output buffer.
	 *
	 * This is used to put the contents of the shortcode into a variable rather
	 * than outputting the HTML at run-time. This allows shortcodes to appear
	 * in the correct location in the_content() instead of when it's created.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param string $query_name
	 *
	 * @uses bb_set_query_name()
	 * @uses ob_start()
	 */
	private function start( $query_name = '' ) {

		// Set query name
		bb_set_query_name( $query_name );

		// Remove 'bb_replace_the_content' filter to prevent infinite loops
		remove_filter( 'the_content', 'bb_replace_the_content' );

		// Start output buffer
		ob_start();
	}

	/**
	 * Return the contents of the output buffer and flush its contents.
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses BB_Shortcodes::unset_globals() Cleans up global values
	 * @return string Contents of output buffer.
	 */
	private function end() {

		// Put output into usable variable
		$output = ob_get_contents();

		// Unset globals
		$this->unset_globals();

		// Flush the output buffer
		ob_end_clean();

		// Reset the query name
		bb_reset_query_name();

		// Add 'bb_replace_the_content' filter back (@see $this::start())
		add_filter( 'the_content', 'bb_replace_the_content' );

		return $output;
	}

	/** Forum shortcodes ******************************************************/

	/**
	 * Display an index of all visible root level forums in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses bb_has_forums()
	 * @uses get_template_part()
	 * @return string
	 */
	public function display_forum_index() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'bb_forum_archive' );

		bb_get_template_part( 'content', 'archive-forum' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the contents of a specific forum ID in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses get_template_part()
	 * @uses bb_single_forum_description()
	 * @return string
	 */
	public function display_forum( $attr, $content = '' ) {

		// Sanity check required info
		if ( !empty( $content ) || ( empty( $attr['id'] ) || !is_numeric( $attr['id'] ) ) )
			return $content;

		// Set passed attribute to $forum_id for clarity
		$forum_id = barebones()->current_forum_id = $attr['id'];

		// Bail if ID passed is not a forum
		if ( !bb_is_forum( $forum_id ) )
			return $content;

		// Start output buffer
		$this->start( 'bb_single_forum' );

		// Check forum caps
		if ( bb_user_can_view_forum( array( 'forum_id' => $forum_id ) ) ) {
			bb_get_template_part( 'content',  'single-forum' );

		// Forum is private and user does not have caps
		} elseif ( bb_is_forum_private( $forum_id, false ) ) {
			bb_get_template_part( 'feedback', 'no-access'    );
		}

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the forum form in an output buffer and return to ensure
	 * post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses get_template_part()
	 */
	public function display_forum_form() {

		// Start output buffer
		$this->start( 'bb_forum_form' );

		// Output templates
		bb_get_template_part( 'form', 'forum' );

		// Return contents of output buffer
		return $this->end();
	}

	/** Topic shortcodes ******************************************************/

	/**
	 * Display an index of all visible root level topics in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses bb_get_hidden_forum_ids()
	 * @uses get_template_part()
	 * @return string
	 */
	public function display_topic_index() {

		// Unset globals
		$this->unset_globals();

		// Filter the query
		if ( ! bb_is_topic_archive() ) {
			add_filter( 'bb_before_has_topics_parse_args', array( $this, 'display_topic_index_query' ) );
		}

		// Start output buffer
		$this->start( 'bb_topic_archive' );

		// Output template
		bb_get_template_part( 'content', 'archive-topic' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the contents of a specific topic ID in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses get_template_part()
	 * @return string
	 */
	public function display_topic( $attr, $content = '' ) {

		// Sanity check required info
		if ( !empty( $content ) || ( empty( $attr['id'] ) || !is_numeric( $attr['id'] ) ) )
			return $content;

		// Unset globals
		$this->unset_globals();

		// Set passed attribute to $forum_id for clarity
		$topic_id = barebones()->current_topic_id = $attr['id'];
		$forum_id = bb_get_topic_forum_id( $topic_id );

		// Bail if ID passed is not a topic
		if ( !bb_is_topic( $topic_id ) )
			return $content;

		// Reset the queries if not in theme compat
		if ( !bb_is_theme_compat_active() ) {

			$bbp = barebones();

			// Reset necessary forum_query attributes for topics loop to function
			$bb->forum_query->query_vars['post_type'] = bb_get_forum_post_type();
			$bb->forum_query->in_the_loop             = true;
			$bb->forum_query->post                    = get_post( $forum_id );

			// Reset necessary topic_query attributes for topics loop to function
			$bb->topic_query->query_vars['post_type'] = bb_get_topic_post_type();
			$bb->topic_query->in_the_loop             = true;
			$bb->topic_query->post                    = get_post( $topic_id );
		}

		// Start output buffer
		$this->start( 'bb_single_topic' );

		// Check forum caps
		if ( bb_user_can_view_forum( array( 'forum_id' => $forum_id ) ) ) {
			bb_get_template_part( 'content', 'single-topic' );

		// Forum is private and user does not have caps
		} elseif ( bb_is_forum_private( $forum_id, false ) ) {
			bb_get_template_part( 'feedback', 'no-access'    );
		}

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the topic form in an output buffer and return to ensure
	 * post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses get_template_part()
	 */
	public function display_topic_form() {

		// Start output buffer
		$this->start( 'bb_topic_form' );

		// Output templates
		bb_get_template_part( 'form', 'topic' );

		// Return contents of output buffer
		return $this->end();
	}

	/** Replies ***************************************************************/

	/**
	 * Display the contents of a specific reply ID in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses get_template_part()
	 * @return string
	 */
	public function display_reply( $attr, $content = '' ) {

		// Sanity check required info
		if ( !empty( $content ) || ( empty( $attr['id'] ) || !is_numeric( $attr['id'] ) ) )
			return $content;

		// Unset globals
		$this->unset_globals();

		// Set passed attribute to $reply_id for clarity
		$reply_id = barebones()->current_reply_id = $attr['id'];
		$forum_id = bb_get_reply_forum_id( $reply_id );

		// Bail if ID passed is not a reply
		if ( !bb_is_reply( $reply_id ) )
			return $content;

		// Reset the queries if not in theme compat
		if ( !bb_is_theme_compat_active() ) {

			$bbp = barebones();

			// Reset necessary forum_query attributes for replys loop to function
			$bb->forum_query->query_vars['post_type'] = bb_get_forum_post_type();
			$bb->forum_query->in_the_loop             = true;
			$bb->forum_query->post                    = get_post( $forum_id );

			// Reset necessary reply_query attributes for replys loop to function
			$bb->reply_query->query_vars['post_type'] = bb_get_reply_post_type();
			$bb->reply_query->in_the_loop             = true;
			$bb->reply_query->post                    = get_post( $reply_id );
		}

		// Start output buffer
		$this->start( 'bb_single_reply' );

		// Check forum caps
		if ( bb_user_can_view_forum( array( 'forum_id' => $forum_id ) ) ) {
			bb_get_template_part( 'content',  'single-reply' );

		// Forum is private and user does not have caps
		} elseif ( bb_is_forum_private( $forum_id, false ) ) {
			bb_get_template_part( 'feedback', 'no-access'    );
		}

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the reply form in an output buffer and return to ensure
	 * post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses get_template_part()
	 */
	public function display_reply_form() {

		// Start output buffer
		$this->start( 'bb_reply_form' );

		// Output templates
		bb_get_template_part( 'form', 'reply' );

		// Return contents of output buffer
		return $this->end();
	}

	/** Topic Tags ************************************************************/

	/**
	 * Display a tag cloud of all topic tags in an output buffer and return to
	 * ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @return string
	 */
	public function display_topic_tags() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'bb_topic_tags' );

		// Output the topic tags
		wp_tag_cloud( array(
			'smallest' => 9,
			'largest'  => 38,
			'number'   => 80,
			'taxonomy' => bb_get_topic_tag_tax_id()
		) );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the contents of a specific topic tag in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses get_template_part()
	 * @return string
	 */
	public function display_topics_of_tag( $attr, $content = '' ) {

		// Sanity check required info
		if ( !empty( $content ) || ( empty( $attr['id'] ) || !is_numeric( $attr['id'] ) ) )
			return $content;

		// Unset globals
		$this->unset_globals();

		// Filter the query
		if ( ! bb_is_topic_tag() ) {
			add_filter( 'bb_before_has_topics_parse_args', array( $this, 'display_topics_of_tag_query' ) );
		}

		// Start output buffer
		$this->start( 'bb_topic_tag' );

		// Set passed attribute to $ag_id for clarity
		barebones()->current_topic_tag_id = $tag_id = $attr['id'];

		// Output template
		bb_get_template_part( 'content', 'archive-topic' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the contents of a specific topic tag in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses get_template_part()
	 * @return string
	 */
	public function display_topic_tag_form() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'bb_topic_tag_edit' );

		// Output template
		bb_get_template_part( 'content', 'topic-tag-edit' );

		// Return contents of output buffer
		return $this->end();
	}

	/** Views *****************************************************************/

	/**
	 * Display the contents of a specific view in an output buffer and return to
	 * ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses get_template_part()
	 * @uses bb_single_forum_description()
	 * @return string
	 */
	public function display_view( $attr, $content = '' ) {

		// Sanity check required info
		if ( empty( $attr['id'] ) )
			return $content;

		// Set passed attribute to $view_id for clarity
		$view_id = $attr['id'];

		// Start output buffer
		$this->start( 'bb_single_view' );

		// Unset globals
		$this->unset_globals();

		// Load the view
		bb_view_query( $view_id );

		// Output template
		bb_get_template_part( 'content', 'single-view' );

		// Return contents of output buffer
		return $this->end();
	}

	/** Search ****************************************************************/

	/**
	 * Display the search form in an output buffer and return to ensure
	 * post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses get_template_part()
	 */
	public function display_search_form() {

		// Start output buffer
		$this->start( 'bb_search_form' );

		// Output templates
		bb_get_template_part( 'form', 'search' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the contents of search results in an output buffer and return to
	 * ensure that post/page contents are displayed first.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $attr
	 * @param string $content
	 * @uses bb_search_query()
	 * @uses get_template_part()
	 */
	public function display_search( $attr, $content = '' ) {

		// Sanity check required info
		if ( !empty( $content ) )
			return $content;

		// Set passed attribute to $search_terms for clarity
		$search_terms = empty( $attr['search'] ) ? bb_get_search_terms() : $attr['search'];

		// Unset globals
		$this->unset_globals();

		// Set terms for query
		set_query_var( bb_get_search_rewrite_id(), $search_terms );

		// Start output buffer
		$this->start( 'bb_search' );

		// Output template
		bb_get_template_part( 'content', 'search' );

		// Return contents of output buffer
		return $this->end();
	}

	/** Account ***************************************************************/

	/**
	 * Display a login form
	 *
	 * @since Barebones (1.0)
	 *
	 * @return string
	 */
	public function display_login() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'bb_login' );

		// Output templates
		if ( !is_user_logged_in() )
			bb_get_template_part( 'form',     'user-login' );
		else
			bb_get_template_part( 'feedback', 'logged-in'  );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display a register form
	 *
	 * @since Barebones (1.0)
	 *
	 * @return string
	 */
	public function display_register() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'bb_register' );

		// Output templates
		if ( !is_user_logged_in() )
			bb_get_template_part( 'form',     'user-register' );
		else
			bb_get_template_part( 'feedback', 'logged-in'     );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display a lost password form
	 *
	 * @since Barebones (1.0)
	 *
	 * @return string
	 */
	public function display_lost_pass() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'bb_lost_pass' );

		// Output templates
		if ( !is_user_logged_in() )
			bb_get_template_part( 'form',     'user-lost-pass' );
		else
			bb_get_template_part( 'feedback', 'logged-in'      );
	
		// Return contents of output buffer
		return $this->end();
	}

	/** Other *****************************************************************/

	/**
	 * Display forum statistics
	 *
	 * @since Barebones (1.0)
	 *
	 * @return shring
	 */
	public function display_stats() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start();

		// Output statistics
		bb_get_template_part( 'content', 'statistics' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display a breadcrumb
	 *
	 * @since Barebones (1.0)
	 *
	 * @return string
	 */
	public function display_breadcrumb() {

		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start();

		// Output breadcrumb
		bb_breadcrumb();

		// Return contents of output buffer
		return $this->end();
	}

	/** Query Filters *********************************************************/

	/**
	 * Filter the query for the topic index
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $args
	 * @return array
	 */
	public function display_topic_index_query( $args = array() ) {
		$args['author']        = 0;
		$args['show_stickies'] = true;
		$args['order']         = 'DESC';
		return $args;
	}

	/**
	 * Filter the query for topic tags
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $args
	 * @return array
	 */
	public function display_topics_of_tag_query( $args = array() ) {
		$args['tax_query'] = array( array(
			'taxonomy' => bb_get_topic_tag_tax_id(),
			'field'    => 'id',
			'terms'    => barebones()->current_topic_tag_id
		) );

		return $args;
	}
}
endif;
