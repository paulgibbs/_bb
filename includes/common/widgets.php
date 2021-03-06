<?php

/**
 * Barebones Widgets
 *
 * Contains the forum list, topic list, reply list and login form widgets.
 *
 * @package Barebones
 * @subpackage Widgets
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Barebones Login Widget
 *
 * Adds a widget which displays the login form
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Widget
 */
class BB_Login_Widget extends WP_Widget {

	/**
	 * Barebones Login Widget
	 *
	 * Registers the login widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bb_login_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bb_login_widget_options', array(
			'classname'   => 'bb_widget_login',
			'description' => __( 'A simple login form with optional links to sign-up and lost password pages.', 'barebones' )
		) );

		parent::__construct( false, __( '(barebones) Login Widget', 'barebones' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'BB_Login_Widget' );
	}

	/**
	 * Displays the output, the login form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param mixed $args Arguments
	 * @param array $instance Instance
	 * @uses apply_filters() Calls 'bb_login_widget_title' with the title
	 * @uses get_template_part() To get the login/logged in form
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title', $settings['title'], $instance, $this->id_base );

		// barebones filters
		$settings['title']    = apply_filters( 'bb_login_widget_title',    $settings['title'],    $instance, $this->id_base );
		$settings['register'] = apply_filters( 'bb_login_widget_register', $settings['register'], $instance, $this->id_base );
		$settings['lostpass'] = apply_filters( 'bb_login_widget_lostpass', $settings['lostpass'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		}

		if ( !is_user_logged_in() ) : ?>

			<form method="post" action="<?php bb_wp_login_action( array( 'context' => 'login_post' ) ); ?>" class="bb-login-form">
				<fieldset>
					<legend><?php _e( 'Log In', 'barebones' ); ?></legend>

					<div class="bb-username">
						<label for="user_login"><?php _e( 'Username', 'barebones' ); ?>: </label>
						<input type="text" name="log" value="<?php bb_sanitize_val( 'user_login', 'text' ); ?>" size="20" id="user_login" tabindex="<?php bb_tab_index(); ?>" />
					</div>

					<div class="bb-password">
						<label for="user_pass"><?php _e( 'Password', 'barebones' ); ?>: </label>
						<input type="password" name="pwd" value="<?php bb_sanitize_val( 'user_pass', 'password' ); ?>" size="20" id="user_pass" tabindex="<?php bb_tab_index(); ?>" />
					</div>

					<div class="bb-remember-me">
						<input type="checkbox" name="rememberme" value="forever" <?php checked( bb_get_sanitize_val( 'rememberme', 'checkbox' ), true, true ); ?> id="rememberme" tabindex="<?php bb_tab_index(); ?>" />
						<label for="rememberme"><?php _e( 'Remember Me', 'barebones' ); ?></label>
					</div>

					<div class="bb-submit-wrapper">

						<?php do_action( 'login_form' ); ?>

						<button type="submit" name="user-submit" id="user-submit" tabindex="<?php bb_tab_index(); ?>" class="button submit user-submit"><?php _e( 'Log In', 'barebones' ); ?></button>

						<?php bb_user_login_fields(); ?>

					</div>

					<?php if ( !empty( $settings['register'] ) || !empty( $settings['lostpass'] ) ) : ?>

						<div class="bb-login-links">

							<?php if ( !empty( $settings['register'] ) ) : ?>

								<a href="<?php echo esc_url( $settings['register'] ); ?>" title="<?php esc_attr_e( 'Register', 'barebones' ); ?>" class="bb-register-link"><?php _e( 'Register', 'barebones' ); ?></a>

							<?php endif; ?>

							<?php if ( !empty( $settings['lostpass'] ) ) : ?>

								<a href="<?php echo esc_url( $settings['lostpass'] ); ?>" title="<?php esc_attr_e( 'Lost Password', 'barebones' ); ?>" class="bb-lostpass-link"><?php _e( 'Lost Password', 'barebones' ); ?></a>

							<?php endif; ?>

						</div>

					<?php endif; ?>

				</fieldset>
			</form>

		<?php else : ?>

			<div class="bb-logged-in">
				<a href="<?php bb_user_profile_url( bb_get_current_user_id() ); ?>" class="submit user-submit"><?php echo get_avatar( bb_get_current_user_id(), '40' ); ?></a>
				<h4><?php bb_user_profile_link( bb_get_current_user_id() ); ?></h4>

				<?php bb_logout_link(); ?>
			</div>

		<?php endif;

		echo $args['after_widget'];
	}

	/**
	 * Update the login widget options
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$instance['title']    = strip_tags( $new_instance['title'] );
		$instance['register'] = esc_url( $new_instance['register'] );
		$instance['lostpass'] = esc_url( $new_instance['lostpass'] );

		return $instance;
	}

	/**
	 * Output the login widget options form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses BB_Login_Widget::get_field_id() To output the field id
	 * @uses BB_Login_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'barebones' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" /></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'register' ); ?>"><?php _e( 'Register URI:', 'barebones' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'register' ); ?>" name="<?php echo $this->get_field_name( 'register' ); ?>" type="text" value="<?php echo esc_url( $settings['register'] ); ?>" /></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'lostpass' ); ?>"><?php _e( 'Lost Password URI:', 'barebones' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'lostpass' ); ?>" name="<?php echo $this->get_field_name( 'lostpass' ); ?>" type="text" value="<?php echo esc_url( $settings['lostpass'] ); ?>" /></label>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses bb_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bb_parse_args( $instance, array(
			'title'    => '',
			'register' => '',
			'lostpass' => ''
		), 'login_widget_settings' );
	}
}

/**
 * Barebones Views Widget
 *
 * Adds a widget which displays the view list
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Widget
 */
class BB_Views_Widget extends WP_Widget {

	/**
	 * Barebones View Widget
	 *
	 * Registers the view widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bb_views_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bb_views_widget_options', array(
			'classname'   => 'widget_display_views',
			'description' => __( 'A list of registered optional topic views.', 'barebones' )
		) );

		parent::__construct( false, __( '(barebones) Topic Views List', 'barebones' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'BB_Views_Widget' );
	}

	/**
	 * Displays the output, the view list
	 *
	 * @since Barebones (1.0)
	 *
	 * @param mixed $args Arguments
	 * @param array $instance Instance
	 * @uses apply_filters() Calls 'bb_view_widget_title' with the title
	 * @uses bb_get_views() To get the views
	 * @uses bb_view_url() To output the view url
	 * @uses bb_view_title() To output the view title
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Only output widget contents if views exist
		if ( ! bb_get_views() ) {
			return;
		}

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',          $settings['title'], $instance, $this->id_base );

		// barebones filter
		$settings['title'] = apply_filters( 'bb_view_widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php foreach ( array_keys( bb_get_views() ) as $view ) : ?>

				<li><a class="bb-view-title" href="<?php bb_view_url( $view ); ?>" title="<?php bb_view_title( $view ); ?>"><?php bb_view_title( $view ); ?></a></li>

			<?php endforeach; ?>

		</ul>

		<?php echo $args['after_widget'];
	}

	/**
	 * Update the view widget options
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the view widget options form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses BB_Views_Widget::get_field_id() To output the field id
	 * @uses BB_Views_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'barebones' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
			</label>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses bb_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bb_parse_args( $instance, array(
			'title' => ''
		), 'view_widget_settings' );
	}
}

/**
 * Barebones Search Widget
 *
 * Adds a widget which displays the forum search form
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Widget
 */
class BB_Search_Widget extends WP_Widget {

	/**
	 * Barebones Search Widget
	 *
	 * Registers the search widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bb_search_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bb_search_widget_options', array(
			'classname'   => 'widget_display_search',
			'description' => __( 'The barebones forum search form.', 'barebones' )
		) );

		parent::__construct( false, __( '(barebones) Forum Search Form', 'barebones' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'BB_Search_Widget' );
	}

	/**
	 * Displays the output, the search form
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bb_search_widget_title' with the title
	 * @uses get_template_part() To get the search form
	 */
	public function widget( $args, $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',            $settings['title'], $instance, $this->id_base );

		// barebones filter
		$settings['title'] = apply_filters( 'bb_search_widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		}

		bb_get_template_part( 'form', 'search' );

		echo $args['after_widget'];
	}

	/**
	 * Update the widget options
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the search widget options form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses BB_Search_Widget::get_field_id() To output the field id
	 * @uses BB_Search_Widget::get_field_name() To output the field name
	 */
	public function form( $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'barebones' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
			</label>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses bb_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bb_parse_args( $instance, array(
			'title' => __( 'Search Forums', 'barebones' )
		), 'search_widget_settings' );
	}
}

/**
 * Barebones Forum Widget
 *
 * Adds a widget which displays the forum list
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Widget
 */
class BB_Forums_Widget extends WP_Widget {

	/**
	 * Barebones Forum Widget
	 *
	 * Registers the forum widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bb_forums_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bb_forums_widget_options', array(
			'classname'   => 'widget_display_forums',
			'description' => __( 'A list of forums with an option to set the parent.', 'barebones' )
		) );

		parent::__construct( false, __( '(barebones) Forums List', 'barebones' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'BB_Forums_Widget' );
	}

	/**
	 * Displays the output, the forum list
	 *
	 * @since Barebones (1.0)
	 *
	 * @param mixed $args Arguments
	 * @param array $instance Instance
	 * @uses apply_filters() Calls 'bb_forum_widget_title' with the title
	 * @uses get_option() To get the forums per page option
	 * @uses current_user_can() To check if the current user can read
	 *                           private() To resety name
	 * @uses bb_has_forums() The main forum loop
	 * @uses bb_forums() To check whether there are more forums available
	 *                     in the loop
	 * @uses bb_the_forum() Loads up the current forum in the loop
	 * @uses bb_forum_permalink() To display the forum permalink
	 * @uses bb_forum_title() To display the forum title
	 */
	public function widget( $args, $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// barebones filter
		$settings['title'] = apply_filters( 'bb_forum_widget_title', $settings['title'], $instance, $this->id_base );

		// Note: private and hidden forums will be excluded via the
		// bb_pre_get_posts_exclude_forums filter and function.
		$widget_query = new WP_Query( array(
			'post_type'      => bb_get_forum_post_type(),
			'post_parent'    => $settings['parent_forum'],
			'post_status'    => bb_get_public_status_id(),
			'posts_per_page' => get_option( '_bb_forums_per_page', 50 ),
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		) );

		// Bail if no posts
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php while ( $widget_query->have_posts() ) : $widget_query->the_post(); ?>

				<li><a class="bb-forum-title" href="<?php bb_forum_permalink( $widget_query->post->ID ); ?>" title="<?php bb_forum_title( $widget_query->post->ID ); ?>"><?php bb_forum_title( $widget_query->post->ID ); ?></a></li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the forum widget options
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['parent_forum'] = $new_instance['parent_forum'];

		// Force to any
		if ( !empty( $instance['parent_forum'] ) && !is_numeric( $instance['parent_forum'] ) ) {
			$instance['parent_forum'] = 'any';
		}

		return $instance;
	}

	/**
	 * Output the forum widget options form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses BB_Forums_Widget::get_field_id() To output the field id
	 * @uses BB_Forums_Widget::get_field_name() To output the field name
	 */
	public function form( $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'barebones' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'parent_forum' ); ?>"><?php _e( 'Parent Forum ID:', 'barebones' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'parent_forum' ); ?>" name="<?php echo $this->get_field_name( 'parent_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['parent_forum'] ); ?>" />
			</label>

			<br />

			<small><?php _e( '"0" to show only root - "any" to show all', 'barebones' ); ?></small>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses bb_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bb_parse_args( $instance, array(
			'title'        => __( 'Forums', 'barebones' ),
			'parent_forum' => 0
		), 'forum_widget_settings' );
	}
}

/**
 * Barebones Topic Widget
 *
 * Adds a widget which displays the topic list
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Widget
 */
class BB_Topics_Widget extends WP_Widget {

	/**
	 * Barebones Topic Widget
	 *
	 * Registers the topic widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bb_topics_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bb_topics_widget_options', array(
			'classname'   => 'widget_display_topics',
			'description' => __( 'A list of recent topics, sorted by popularity or freshness.', 'barebones' )
		) );

		parent::__construct( false, __( '(barebones) Recent Topics', 'barebones' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'BB_Topics_Widget' );
	}

	/**
	 * Displays the output, the topic list
	 *
	 * @since Barebones (1.0)
	 *
	 * @param mixed $args
	 * @param array $instance
	 * @uses apply_filters() Calls 'bb_topic_widget_title' with the title
	 * @uses bb_topic_permalink() To display the topic permalink
	 * @uses bb_topic_title() To display the topic title
	 * @uses bb_get_topic_last_active_time() To get the topic last active
	 *                                         time
	 * @uses bb_get_topic_id() To get the topic id
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// barebones filter
		$settings['title'] = apply_filters( 'bb_topic_widget_title', $settings['title'], $instance, $this->id_base );

		// How do we want to order our results?
		switch ( $settings['order_by'] ) {

			// Order by most recent replies
			case 'freshness' :
				$topics_query = array(
					'post_type'      => bb_get_topic_post_type(),
					'post_parent'    => $settings['parent_forum'],
					'posts_per_page' => (int) $settings['max_shown'],
					'post_status'    => array( bb_get_public_status_id(), bb_get_closed_status_id() ),
					'show_stickies'  => false,
					'meta_key'       => '_bb_last_active_time',
					'orderby'        => 'meta_value',
					'order'          => 'DESC',
				);
				break;

			// Order by total number of replies
			case 'popular' :
				$topics_query = array(
					'post_type'      => bb_get_topic_post_type(),
					'post_parent'    => $settings['parent_forum'],
					'posts_per_page' => (int) $settings['max_shown'],
					'post_status'    => array( bb_get_public_status_id(), bb_get_closed_status_id() ),
					'show_stickies'  => false,
					'meta_key'       => '_bb_reply_count',
					'orderby'        => 'meta_value',
					'order'          => 'DESC'
				);
				break;

			// Order by which topic was created most recently
			case 'newness' :
			default :
				$topics_query = array(
					'post_type'      => bb_get_topic_post_type(),
					'post_parent'    => $settings['parent_forum'],
					'posts_per_page' => (int) $settings['max_shown'],
					'post_status'    => array( bb_get_public_status_id(), bb_get_closed_status_id() ),
					'show_stickies'  => false,
					'order'          => 'DESC'
				);
				break;
		}

		// Note: private and hidden forums will be excluded via the
		// bb_pre_get_posts_exclude_forums filter and function.
		$widget_query = new WP_Query( $topics_query );

		// Bail if no topics are found
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php while ( $widget_query->have_posts() ) :

				$widget_query->the_post();
				$topic_id    = bb_get_topic_id( $widget_query->post->ID );
				$author_link = '';

				// Maybe get the topic author
				if ( 'on' == $settings['show_user'] ) :
					$author_link = bb_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => 'both', 'size' => 14 ) );
				endif; ?>

				<li>
					<a class="bb-forum-title" href="<?php echo esc_url( bb_get_topic_permalink( $topic_id ) ); ?>" title="<?php echo esc_attr( bb_get_topic_title( $topic_id ) ); ?>"><?php bb_topic_title( $topic_id ); ?></a>

					<?php if ( ! empty( $author_link ) ) : ?>

						<?php printf( _x( 'by %1$s', 'widgets', 'barebones' ), '<span class="topic-author">' . $author_link . '</span>' ); ?>

					<?php endif; ?>

					<?php if ( 'on' == $settings['show_date'] ) : ?>

						<div><?php bb_topic_last_active_time( $topic_id ); ?></div>

					<?php endif; ?>

				</li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the topic widget options
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance              = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['order_by']  = strip_tags( $new_instance['order_by'] );
		$instance['show_date'] = (bool) $new_instance['show_date'];
		$instance['show_user'] = (bool) $new_instance['show_user'];
		$instance['max_shown'] = (int) $new_instance['max_shown'];

		// Force to any
		if ( !empty( $instance['parent_forum'] ) || !is_numeric( $instance['parent_forum'] ) ) {
			$instance['parent_forum'] = 'any';
		} else {
			$instance['parent_forum'] = (int) $new_instance['parent_forum'];
		}

		return $instance;
	}

	/**
	 * Output the topic widget options form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses BB_Topics_Widget::get_field_id() To output the field id
	 * @uses BB_Topics_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p><label for="<?php echo $this->get_field_id( 'title'     ); ?>"><?php _e( 'Title:',                  'barebones' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title'     ); ?>" name="<?php echo $this->get_field_name( 'title'     ); ?>" type="text" value="<?php echo esc_attr( $settings['title']     ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_shown' ); ?>"><?php _e( 'Maximum topics to show:', 'barebones' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_shown' ); ?>" name="<?php echo $this->get_field_name( 'max_shown' ); ?>" type="text" value="<?php echo esc_attr( $settings['max_shown'] ); ?>" /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id( 'parent_forum' ); ?>"><?php _e( 'Parent Forum ID:', 'barebones' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'parent_forum' ); ?>" name="<?php echo $this->get_field_name( 'parent_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['parent_forum'] ); ?>" />
			</label>

			<br />

			<small><?php _e( '"0" to show only root - "any" to show all', 'barebones' ); ?></small>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Show post date:',    'barebones' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" <?php checked( 'on', $settings['show_date'] ); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_user' ); ?>"><?php _e( 'Show topic author:', 'barebones' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_user' ); ?>" name="<?php echo $this->get_field_name( 'show_user' ); ?>" <?php checked( 'on', $settings['show_user'] ); ?> /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Order By:',        'barebones' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'order_by' ); ?>" id="<?php echo $this->get_field_name( 'order_by' ); ?>">
				<option <?php selected( $settings['order_by'], 'newness' );   ?> value="newness"><?php _e( 'Newest Topics',                'barebones' ); ?></option>
				<option <?php selected( $settings['order_by'], 'popular' );   ?> value="popular"><?php _e( 'Popular Topics',               'barebones' ); ?></option>
				<option <?php selected( $settings['order_by'], 'freshness' ); ?> value="freshness"><?php _e( 'Topics With Recent Replies', 'barebones' ); ?></option>
			</select>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses bb_parse_args() To merge widget options into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bb_parse_args( $instance, array(
			'title'        => __( 'Recent Topics', 'barebones' ),
			'max_shown'    => 5,
			'show_date'    => false,
			'show_user'    => false,
			'parent_forum' => 'any',
			'order_by'     => false
		), 'topic_widget_settings' );
	}
}

/**
 * Barebones Stats Widget
 *
 * Adds a widget which displays the forum statistics
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Widget
 */
class BB_Stats_Widget extends WP_Widget {

	/**
	 * Barebones Stats Widget
	 *
	 * Registers the stats widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses  apply_filters() Calls 'bb_stats_widget_options' with the
	 *        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bb_stats_widget_options', array(
			'classname'   => 'widget_display_stats',
			'description' => __( 'Some statistics from your forum.', 'barebones' )
		) );

		parent::__construct( false, __( '(barebones) Statistics', 'barebones' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'BB_Stats_Widget' );
	}

	/**
	 * Displays the output, the statistics
	 *
	 * @since Barebones (1.0)
	 *
	 * @param mixed $args     Arguments
	 * @param array $instance Instance
	 *
	 * @uses apply_filters() Calls 'bb_stats_widget_title' with the title
	 * @uses bb_get_template_part() To get the content-forum-statistics template
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// barebones widget title filter
		$settings['title'] = apply_filters( 'bb_stats_widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		}

		bb_get_template_part( 'content', 'statistics' );

		echo $args['after_widget'];
	}

	/**
	 * Update the stats widget options
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the stats widget options form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'barebones' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>"/>
			</label>
		</p>

	<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses bb_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bb_parse_args( $instance, array(
			'title' => __( 'Forum Statistics', 'barebones' )
		),
		'stats_widget_settings' );
	}
}

/**
 * Barebones Replies Widget
 *
 * Adds a widget which displays the replies list
 *
 * @since Barebones (1.0)
 *
 * @uses WP_Widget
 */
class BB_Replies_Widget extends WP_Widget {

	/**
	 * Barebones Replies Widget
	 *
	 * Registers the replies widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses apply_filters() Calls 'bb_replies_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'bb_replies_widget_options', array(
			'classname'   => 'widget_display_replies',
			'description' => __( 'A list of the most recent replies.', 'barebones' )
		) );

		parent::__construct( false, __( '(barebones) Recent Replies', 'barebones' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Barebones (1.0)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'BB_Replies_Widget' );
	}

	/**
	 * Displays the output, the replies list
	 *
	 * @since Barebones (1.0)
	 *
	 * @param mixed $args
	 * @param array $instance
	 * @uses apply_filters() Calls 'bb_reply_widget_title' with the title
	 * @uses bb_get_reply_author_link() To get the reply author link
	 * @uses bb_get_reply_author() To get the reply author name
	 * @uses bb_get_reply_id() To get the reply id
	 * @uses bb_get_reply_url() To get the reply url
	 * @uses bb_get_reply_excerpt() To get the reply excerpt
	 * @uses bb_get_reply_topic_title() To get the reply topic title
	 * @uses get_the_date() To get the date of the reply
	 * @uses get_the_time() To get the time of the reply
	 */
	public function widget( $args, $instance ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',             $settings['title'], $instance, $this->id_base );

		// barebones filter
		$settings['title'] = apply_filters( 'bb_replies_widget_title', $settings['title'], $instance, $this->id_base );

		// Note: private and hidden forums will be excluded via the
		// bb_pre_get_posts_exclude_forums filter and function.
		$widget_query = new WP_Query( array(
			'post_type'      => bb_get_reply_post_type(),
			'post_status'    => array( bb_get_public_status_id(), bb_get_closed_status_id() ),
			'posts_per_page' => (int) $settings['max_shown']
		) );

		// Bail if no replies
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php while ( $widget_query->have_posts() ) : $widget_query->the_post(); ?>

				<li>

					<?php

					// Verify the reply ID
					$reply_id   = bb_get_reply_id( $widget_query->post->ID );
					$reply_link = '<a class="bb-reply-topic-title" href="' . esc_url( bb_get_reply_url( $reply_id ) ) . '" title="' . esc_attr( bb_get_reply_excerpt( $reply_id, 50 ) ) . '">' . bb_get_reply_topic_title( $reply_id ) . '</a>';

					// Only query user if showing them
					if ( 'on' == $settings['show_user'] ) :
						$author_link = bb_get_reply_author_link( array( 'post_id' => $reply_id, 'type' => 'both', 'size' => 14 ) );
					else :
						$author_link = false;
					endif;

					// Reply author, link, and timestamp
					if ( ( 'on' == $settings['show_date'] ) && !empty( $author_link ) ) :

						// translators: 1: reply author, 2: reply link, 3: reply timestamp
						printf( _x( '%1$s on %2$s %3$s', 'widgets', 'barebones' ), $author_link, $reply_link, '<div>' . bb_get_time_since( get_the_time( 'U' ) ) . '</div>' );

					// Reply link and timestamp
					elseif ( 'on' == $settings['show_date'] ) :

						// translators: 1: reply link, 2: reply timestamp
						printf( _x( '%1$s %2$s',         'widgets', 'barebones' ), $reply_link,  '<div>' . bb_get_time_since( get_the_time( 'U' ) ) . '</div>'              );

					// Reply author and title
					elseif ( !empty( $author_link ) ) :

						// translators: 1: reply author, 2: reply link
						printf( _x( '%1$s on %2$s',      'widgets', 'barebones' ), $author_link, $reply_link                                                                 );

					// Only the reply title
					else :

						// translators: 1: reply link
						printf( _x( '%1$s',              'widgets', 'barebones' ), $reply_link                                                                               );

					endif;

					?>

				</li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the reply widget options
	 *
	 * @since Barebones (1.0)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance              = $old_instance;
		$instance['title']     = strip_tags( $new_instance['title'] );
		$instance['show_date'] = (bool) $new_instance['show_date'];
		$instance['show_user'] = (bool) $new_instance['show_user'];
		$instance['max_shown'] = (int) $new_instance['max_shown'];

		return $instance;
	}

	/**
	 * Output the reply widget options form
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses BB_Replies_Widget::get_field_id() To output the field id
	 * @uses BB_Replies_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p><label for="<?php echo $this->get_field_id( 'title'     ); ?>"><?php _e( 'Title:',                   'barebones' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title'     ); ?>" name="<?php echo $this->get_field_name( 'title'     ); ?>" type="text" value="<?php echo esc_attr( $settings['title']     ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_shown' ); ?>"><?php _e( 'Maximum replies to show:', 'barebones' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_shown' ); ?>" name="<?php echo $this->get_field_name( 'max_shown' ); ?>" type="text" value="<?php echo esc_attr( $settings['max_shown'] ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Show post date:',          'barebones' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" <?php checked( 'on', $settings['show_date'] ); ?> /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_user' ); ?>"><?php _e( 'Show reply author:',       'barebones' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_user' ); ?>" name="<?php echo $this->get_field_name( 'show_user' ); ?>" <?php checked( 'on', $settings['show_user'] ); ?> /></label></p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since Barebones (1.0)
	 *
	 * @param $instance Instance
	 * @uses bb_parse_args() To merge widget settings into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bb_parse_args( $instance, array(
			'title'     => __( 'Recent Replies', 'barebones' ),
			'max_shown' => 5,
			'show_date' => false,
			'show_user' => false
		),
		'replies_widget_settings' );
	}
}