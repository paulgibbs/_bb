<?php

/**
 * Template Name: bbPress - User Register
 *
 * @package bbPress
 * @subpackage Theme
 */

// No logged in users
bb_logged_in_redirect();

// Begin Template
get_header(); ?>

	<?php do_action( 'bb_before_main_content' ); ?>

	<?php do_action( 'bb_template_notices' ); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<div id="bbp-register" class="bbp-register">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-content">

				<?php the_content(); ?>

				<div id="bbpress-forums">

					<?php bb_breadcrumb(); ?>

					<?php bb_get_template_part( 'form', 'user-register' ); ?>

				</div>
			</div>
		</div><!-- #bbp-register -->

	<?php endwhile; ?>

	<?php do_action( 'bb_after_main_content' ); ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
