<?php
/**
 *
 * Admin for the Template for Slide Show Static Pages
 *
 * @author Takuto Yanagida
 * @version 2021-08-27
 *
 */


function setup_template_admin() {
	\wplug\keyvis\add_meta_box( __( 'Slide Show' ), 'page' );

	add_action( 'save_post_page', function ( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		\wplug\keyvis\save_meta_box( $post_id );
	} );
}
