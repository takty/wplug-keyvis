<?php
/**
 * Functions and Definitions for Slider
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-25
 */

namespace wplug\slider;

require_once __DIR__ . '/assets/field.php';
require_once __DIR__ . '/assets/util.php';
require_once __DIR__ . '/inc/template-admin.php';

function initialize( array $args = [] ) {
	$url_to = untrailingslashit( $args['url_to'] ?? get_file_uri( __DIR__ ) );
	_register_script( $url_to );
}

function _register_script( string $url_to ) {
	if ( is_admin() ) {
		add_action( 'admin_enqueue_scripts', function () use ( $url_to ) {
			wp_enqueue_script( 'picker-link',  abs_url( $url_to, './asset/lib/picker-link.min.js' ), [ 'wplink', 'jquery-ui-autocomplete' ] );
			wp_enqueue_script( 'picker-media', abs_url( $url_to, './asset/lib/picker-media.min.js' ), [], 1.0, true );
			wp_enqueue_style(  'wplug-slider-show-template-admin', $url_to . '/assets/css/template-admin.min.css' );
			wp_enqueue_script( 'wplug-slider-show-template-admin', $url_to . '/assets/js/template-admin.min.js' );
		} );
	} else {
		add_action( 'wp_enqueue_scripts', function () use ( $url_to ) {
			wp_register_style( 'wplug-slider-show', abs_url( $url_to, './assets/css/show.min.css' ) );
			wp_register_script( 'wplug-slider-show', abs_url( $url_to, './assets/js/show.min.js' ) );
		} );
	}
}

function _set_default_args( array $args ): array {
	$args['id']         = $args['id']         ?? 'slider-show';
	$args['key']        = $args['key']        ?? '_slider_show';
	$args['image_size'] = $args['image_size'] ?? 'large';
	$args['class']      = $args['class']      ?? '';

	$args['effect_type']           = $args['effect_type']           ?? 'slide';  // 'scroll' or 'fade'
	$args['duration_time']         = $args['duration_time']         ?? 8;  // [second]
	$args['transition_time']       = $args['transition_time']       ?? 1;  // [second]
	$args['background_opacity']    = $args['background_opacity']    ?? 0.33;
	$args['is_picture_scroll']     = $args['is_picture_scroll']     ?? false;
	$args['is_random_timing']      = $args['is_random_timing']      ?? false;
	$args['is_background_visible'] = $args['is_background_visible'] ?? true;
	$args['is_side_slide_visible'] = $args['is_side_slide_visible'] ?? false;
	$args['zoom_rate']             = $args['zoom_rate']             ?? 1;

	$args['caption_type']     = $args['caption_type']     ?? 'subtitle';  // 'line' or 'circle'
	$args['is_dual']          = $args['is_dual']          ?? false;
	$args['is_video_enabled'] = $args['is_video_enabled'] ?? false;
	$args['is_shuffled']      = $args['is_shuffled']      ?? false;
	return $args;
}

function _create_option_str( array $args ): string {
	$opts = [
		'effect_type'           => $args['effect_type'],
		'duration_time'         => $args['duration_time'],
		'transition_time'       => $args['transition_time'],
		'background_opacity'    => $args['background_opacity'],
		'is_picture_scroll'     => $args['is_picture_scroll'],
		'is_random_timing'      => $args['is_random_timing'],
		'is_background_visible' => $args['is_background_visible'],
		'is_side_slide_visible' => $args['is_side_slide_visible'],
		'zoom_rate'             => $args['zoom_rate'],
	];
	return json_encode( $opts );
}


// -----------------------------------------------------------------------------


function add_meta_box( array $args, string $label, string $screen, string $context = 'advanced' ) {
	add_meta_box_template_admin( $args, $label, $screen, $context );
}

function save_meta_box( array $args, int $post_id ) {
	save_meta_box_template_admin( $args, $post_id );
}


// -----------------------------------------------------------------------------


function the_show( array $args, ?int $post_id = null ): bool {
	wp_enqueue_style( 'wplug-slider-show' );
	wp_enqueue_script( 'wplug-slider-show' );

	$post = get_post( $post_id );
	$its = _get_items( $args, $post->ID );
	if ( empty( $its ) ) return false;

	$args     = _set_default_args( $args );
	$dom_id   = "{$args['id']}-$post->ID";
	$dom_cls  = empty( $args['class'] ) ? '' : " {$args['class']}";
	$opts_str = _create_option_str( $args );
?>
	<section class="gida-slider-show<?php echo $dom_cls ?>" id="<?php echo $dom_id ?>">
		<div class="gida-slider-show-frame">
			<ul class="gida-slider-show-slides">
<?php
	foreach ( $its as $it ) {
		if ( $it['type'] === 'image' ) {
			_echo_slide_item_img( $it, $args['caption_type'] , $args['is_dual'] );
		} else if ( $it['type'] === 'video' ) {
			_echo_slide_item_video( $it, $args['caption_type'] );
		}
	}
?>
			</ul>
			<div class="gida-slider-show-prev"></div>
			<div class="gida-slider-show-next"></div>
		</div>
		<div class="gida-slider-show-rivets"></div>
	</section>
<?php
	wp_add_inline_script( 'wplug-slider-show', "GIDA.slider_show('$dom_id', $opts_str);" );
	return true;
}

function the_items( array $args, ?int $post_id = null ) {
	$args   = _set_default_args( $args );
	$post   = get_post( $post_id );
	$dom_id = "{$args['id']}-$post->ID";
	$its    = _get_items( $args, $post->ID );

	foreach ( $its as $idx => $it ) {
		$event = "GIDA.slider_show_page('$dom_id', $idx);";
		$id = $dom_id . "-$idx";
		if ( $it['type'] === 'image' ) {
			$_img   = esc_url( $it['image'] );
			$_style = "background-image: url('$_img');";
?>
			<li id="<?php echo $id; ?>"><a href="javascript:void(0)" onclick="<?php echo $event; ?>" style="<?php echo $_style; ?>"></a></li>
<?php
		} else if ( $it['type'] === 'video' ) {
			$_video = esc_url( $it['video'] );
?>
			<li id="<?php echo $id; ?>"><a href="javascript:void(0)" onclick="<?php echo $event ?>"><video><source src="<?php echo $_video; ?>"></video></a></li>
<?php
		}
	}
}

function _echo_slide_item_img( array $it, string $caption_type, bool $is_dual ) {
	$imgs   = $it['images'];
	$imgs_s = isset( $it['images_sub'] ) ? $it['images_sub'] : false;
	$data   = [];

	if ( $is_dual && $imgs_s !== false ) {
		_set_attrs( $data, 'img-sub', $imgs_s );
	}
	_set_attrs( $data, 'img', $imgs );
	$attr = '';
	foreach ( $data as $key => $val ) {
		$attr .= " data-$key=\"$val\"";
	}
	$cont = _create_slide_content( $it['caption'], $it['url'], $caption_type );
	echo "<li$attr>$cont</li>\n";
}

function _echo_slide_item_video( array $it, string $caption_type ) {
	$_url = esc_url( $it['video'] );
	$attr = " data-video=\"$_url\"";
	$cont = _create_slide_content( $it['caption'], $it['url'], $caption_type );
	echo "<li$attr>$cont</li>\n";
}

function _set_attrs( array &$data, string $key, array $imgs ) {
	if ( 2 <= count( $imgs ) ) {
		$data["$key-phone"] = esc_url( $imgs[0] );
		$data[ $key ]       = esc_url( $imgs[1] );
	} else {
		$data[ $key ] = esc_url( $imgs[0] );
	}
}

function _create_slide_content( string $cap, string $url, string $caption_type ) {
	$div = '';
	if ( ! empty( $cap ) ) {
		$ss  = separate_line( $cap );
		$str = '<div><span>' . implode( '</span></div><div><span>', $ss ) . '</span></div>';
		$div = '<div class="gida-slider-show-caption' . " $caption_type\">$str</div>";
	}
	if ( empty( $url ) ) return $div;
	$_url = esc_url( $url );
	return "<a href=\"$_url\">$div</a>";
}


// -----------------------------------------------------------------------------


function _save_items( array $args, int $post_id ) {
	$sub_keys = [ 'media', 'caption', 'url', 'type', 'delete' ];
	if ( $args['is_dual'] ) $sub_keys[] = 'media_sub';

	$its = get_multiple_post_meta_from_post( $args['key'], $sub_keys );
	$its = array_filter( $its, function ( $it ) { return ! $it['delete'] && $it['type'] !== 'template'; } );
	$its = array_values( $its );

	foreach ( $its as &$it ) {
		$pid = url_to_postid( $it['url'] );
		if ( $pid !== 0 ) $it['url'] = $pid;
	}
	$sub_keys = [ 'media', 'caption', 'url', 'type' ];
	if ( $args['is_dual'] ) $sub_keys[] = 'media_sub';
	update_multiple_post_meta( $post_id, $args['key'], $its, $sub_keys );
}

function _get_items( array $args, int $post_id ): array {
	$sub_keys = [ 'media', 'caption', 'url', 'type' ];
	if ( $args['is_dual'] ) $sub_keys[] = 'media_sub';

	$its = get_multiple_post_meta( $post_id, $args['key'], $sub_keys );

	foreach ( $its as &$it ) {
		if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {
			$permalink = get_permalink( $it['url'] );
			if ( $permalink !== false ) {
				$it['post_id'] = $it['url'];
				$it['url'] = $permalink;
			}
		}
		if ( empty( $it['type'] ) ) $it['type'] = 'image';
		$it['image'] = '';
		$img_sizes   = is_array( $args['image_size'] ) ? $args['image_size'] : [ $args['image_size'] ];
		if ( $it['type'] === 'image' ) {
			if ( ! empty( $it['media'] ) ) {
				_get_images( $it, intval( $it['media'] ), $img_sizes );
			}
			if ( $args['is_dual'] ) {
				$it['image_sub'] = '';
				if ( ! empty( $it['media_sub'] ) ) {
					_get_images( $it, intval( $it['media_sub'] ), $img_sizes, '_sub' );
				}
			}
		} else if ( $it['type'] === 'video' ) {
			$it['video'] = wp_get_attachment_url( $it['media'] );
			$am = _get_image_meta( $it['media'] );
			if ( $am ) $it = array_merge( $it, $am );
		}
	}
	if ( ! is_admin() && $args['is_shuffled'] ) shuffle( $its );
	return $its;
}

function _get_images( array &$it, int $aid, array $img_sizes, string $pf = '' ) {
	$imgs = [];
	foreach ( $img_sizes as $s ) {
		$img = wp_get_attachment_image_src( $aid, $s );
		if ( $img ) $imgs[] = $img[0];
	}
	if ( ! empty( $imgs ) ) {
		$it["images$pf"] = $imgs;
		$it["image$pf" ] = $imgs[ count( $imgs ) - 1 ];
	}
	$am = _get_image_meta( $aid, $pf );
	if ( $am ) $it = array_merge( $it, $am );
}

function _get_image_meta( int $aid, string $pf = '' ): array {
	$p = get_post( $aid );
	if ( $p === null ) return null;
	$t  = $p->post_title;
	$fn = basename( $p->guid );
	return [ "title$pf" => $t, "filename$pf" => $fn ];
}
