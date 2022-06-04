<?php
/**
 * The Template for Static Pages with Slide Show
 *
 * Template Name: Slide Show
 *
 * @package Theme
 * @author Takuto Yanagida
 * @version 2022-06-04
 */

get_header();
?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

<?php
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/entry', 'page' );
endwhile;
?>
			<section>
				<div class="entry-content">
					<?php \wplug\bimeson_list\the_filter(); ?>
					<?php \wplug\bimeson_list\the_list(); ?>
				</div>
			</section>

		</main>
	</div>
<?php
get_footer();
