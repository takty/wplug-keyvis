<?php
/**
 * Admin for the Template for Static Pages with Slide Show
 *
 * @package Theme
 * @author Takuto Yanagida
 * @version 2022-06-04
 */

/**
 * Setup template admin for static pages with slide show.
 */
function setup_template_admin() {
	\wplug\keyvis\add_meta_box( __( 'Slide Show' ), 'page' );

	add_action(
		'save_post_page',
		function ( $post_id ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
			\wplug\keyvis\save_meta_box( $post_id );
		}
	);
}
