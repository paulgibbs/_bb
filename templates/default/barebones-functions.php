<?php

/**
 * Functions of Barebones' default theme
 *
 * @package Barebones
 * @subpackage BB_Theme_Compat
 * @since Barebones (1.0)
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Theme Setup ***************************************************************/

if ( !class_exists( 'BB_Default' ) ) :

/**
 * Loads Barebones default theme functionality
 *
 * This is not a real theme by WordPress standards, and is instead used as the
 * fallback for any WordPress theme that does not have barebones templates in it.
 *
 * To make your custom theme Barebones compatible and customize the templates, you
 * can copy these files into your theme without needing to merge anything
 * together; Barebones should safely handle the rest.
 *
 * See @link BB_Theme_Compat() for more.
 *
 * @since Barebones (1.0)
 *
 * @package Barebones
 * @subpackage BB_Theme_Compat
 */
class BB_Default extends BB_Theme_Compat {

	/** Functions *************************************************************/

	/**
	 * The main Barebones (Default) Loader
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses BB_Default::setup_globals()
	 * @uses BB_Default::setup_actions()
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Component global variables
	 *
	 * Note that this function is currently commented out in the constructor.
	 * It will only be used if you copy this file into your current theme and
	 * uncomment the line above.
	 *
	 * You'll want to customize the values in here, so they match whatever your
	 * needs are.
	 *
	 * @since Barebones (1.0)
	 * @access private
	 */
	private function setup_globals() {
		$bbp           = barebones();
		$this->id      = 'default';
		$this->name    = __( 'Barebones Default', 'barebones' );
		$this->version = bb_get_version();
		$this->dir     = trailingslashit( $bb->themes_dir . 'default' );
		$this->url     = trailingslashit( $bb->themes_url . 'default' );
	}

	/**
	 * Setup the theme hooks
	 *
	 * @since Barebones (1.0)
	 * @access private
	 *
	 * @uses add_filter() To add various filters
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {

		/** Scripts ***********************************************************/

		add_action( 'bb_enqueue_scripts',   array( $this, 'enqueue_styles'        ) ); // Enqueue theme CSS
		add_action( 'bb_enqueue_scripts',   array( $this, 'enqueue_scripts'       ) ); // Enqueue theme JS


		/** Override **********************************************************/

		do_action_ref_array( 'bb_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Load the theme CSS
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses wp_enqueue_style() To enqueue the styles
	 */
	public function enqueue_styles() {

		// LTR or RTL
		$file = is_rtl() ? 'css/barebones-rtl.css' : 'css/barebones.css';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'bb-child-barebones';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'bb-parent-barebones';

		// Barebones theme compatibility
		} else {
			$location = trailingslashit( $this->url );
			$handle   = 'bb-default-barebones';
		}

		// Enqueue the Barebones styling
		wp_enqueue_style( $handle, $location . $file, array(), $this->version, 'screen' );
	}

	/**
	 * Enqueue the required Javascript files
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses bb_is_single_topic() To check if it's the topic page
	 * @uses bb_is_single_user_edit() To check if it's the profile edit page
	 * @uses wp_enqueue_script() To enqueue the scripts
	 */
	public function enqueue_scripts() {
		$file = 'js/barebones.js';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'bb-child-javascript';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'bb-parent-javascript';

		// Barebones theme compatibility
		} else {
			$location = trailingslashit( bb_get_theme_compat_url() );
			$handle   = 'bb-default-javascript';
		}

		// Enqueue the stylesheet
		wp_enqueue_script( $handle, $location . $file, array(), $this->version, 'screen', true );
	}
}
new BB_Default();
endif;
