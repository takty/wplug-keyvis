<?php
/**
 * Functions and Definitions for Keyvis
 *
 * @package Wplug Keyvis
 * @author Takuto Yanagida
 * @version 2021-08-27
 */

namespace wplug\keyvis;

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
			wp_enqueue_script( 'wplug-keyvis-picker-link',  abs_url( $url_to, './assets/js/picker-link.min.js' ), [ 'wplink', 'jquery-ui-autocomplete' ] );
			wp_enqueue_script( 'wplug-keyvis-picker-media', abs_url( $url_to, './assets/js/picker-media.min.js' ), [], 1.0, true );
			wp_enqueue_script( 'wplug-keyvis-sortable', abs_url( $url_to, './assets/js/html5sortable.min.js' ) );
			wp_enqueue_script( 'wplug-keyvis-template-admin', abs_url( $url_to, './assets/js/template-admin.min.js' ), [ 'wplug-keyvis-picker-link', 'wplug-keyvis-picker-media', 'wplug-keyvis-sortable' ] );
			wp_enqueue_style( 'wplug-keyvis-template-admin', abs_url( $url_to, './assets/css/template-admin.min.css' ) );
		} );
	} else {
		add_action( 'wp_enqueue_scripts', function () use ( $url_to ) {
			wp_register_script( 'wplug-keyvis-show', abs_url( $url_to, './assets/js/show.min.js' ) );
			wp_register_script( 'wplug-keyvis-hero', abs_url( $url_to, './assets/js/hero.min.js' ) );
			wp_register_style( 'wplug-keyvis-show', abs_url( $url_to, './assets/css/show.min.css' ) );
			wp_register_style( 'wplug-keyvis-hero', abs_url( $url_to, './assets/css/hero.min.css' ) );
		} );
	}
}

function _set_default_args( array $args ): array {
	$args['id']        = $args['id']        ?? 'keyvis';
	$args['key']       = $args['key']       ?? '_keyvis';
	$args['class']     = $args['class']     ?? '';
	$args['view_size'] = $args['view_size'] ?? '96rem';

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

function _create_option_str( array $args, array $opts ): string {
	$opts = [
		'effect_type'           => $opts['effect_type'] ?? $args['effect_type'],
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


function add_meta_box_show( array $args, string $label, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	add_meta_box_template_admin( true, $args, $label, $screen, $context );
}

function save_meta_box_show( array $args, int $post_id ) {
	save_meta_box_template_admin( true, $args, $post_id );
}

function add_meta_box_hero( array $args, string $label, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	add_meta_box_template_admin( false, $args, $label, $screen, $context );
}

function save_meta_box_hero( array $args, int $post_id ) {
	save_meta_box_template_admin( false, $args, $post_id );
}


// -----------------------------------------------------------------------------


function the_show( array $args, ?int $post_id = null ): bool {
	wp_enqueue_style( 'wplug-keyvis-show' );
	wp_enqueue_script( 'wplug-keyvis-show' );

	$post = get_post( $post_id );
	if ( null === $post ) return false;
	$post_id = $post->ID;

	$args           = _set_default_args( $args );
	[ $its, $opts ] = _get_data( true, $args, $post_id );
	if ( empty( $its ) ) return false;

	$dom_id  = "{$args['id']}-$post_id";
	$dom_cls = empty( $args['class'] ) ? '' : " {$args['class']}";
?>
	<section class="gida-slider-show<?php echo $dom_cls ?>" id="<?php echo $dom_id ?>">
		<div class="gida-slider-show-frame">
			<ul class="gida-slider-show-slides">
<?php
	foreach ( $its as $it ) {
		$cap_type = $it['caption_type'] ?? $args['caption_type'];
		if ( 'image' === $it['type'] ) {
			_echo_slide_item_img( $it, $cap_type, true );
		} else if ( 'video' === $it['type'] ) {
			_echo_slide_item_video( $it, $cap_type, true );
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
	$opts_str = _create_option_str( $args, $opts );
	wp_add_inline_script( 'wplug-keyvis-show', "GIDA.slider_show('$dom_id', $opts_str);" );
	return true;
}

function the_hero( array $args, ?int $post_id = null ): bool {
	wp_enqueue_style( 'wplug-keyvis-hero' );
	wp_enqueue_script( 'wplug-keyvis-hero' );

	$post = get_post( $post_id );
	if ( null === $post ) return false;
	$post_id = $post->ID;

	$args           = _set_default_args( $args );
	[ $its, $opts ] = _get_data( false, $args, $post_id );
	if ( empty( $its ) ) return false;

	$dom_id  = "{$args['id']}-$post_id";
	$dom_cls = empty( $args['class'] ) ? '' : " {$args['class']}";
?>
	<section class="gida-slider-hero<?php echo $dom_cls ?>" id="<?php echo $dom_id ?>">
		<div class="gida-slider-hero-frame">
			<ul class="gida-slider-hero-slides">
<?php
	foreach ( $its as $it ) {
		if ( 'image' === $it['type'] ) {
			_echo_slide_item_img( $it, '', false );
		} else if ( 'video' === $it['type'] ) {
			_echo_slide_item_video( $it, '', false );
		}
	}
?>
			</ul>
		</div>
	</section>
<?php
	$opts_str = _create_option_str( $args, $opts );
	wp_add_inline_script( 'wplug-keyvis-hero', "GIDA.slider_hero('$dom_id', $opts_str);" );
	return true;
}

function _echo_slide_item_img( array $it, string $caption_type, bool $is_show ) {
	$cont = $it['img_tag'] . ( $it['img_tag_sub'] ?? '' );

	if ( $is_show ) {
		$cont .= _create_slide_caption( $it['caption'] ?? '', $caption_type );

		$_link = esc_url( $it['url'] ?? '' );
		$cont  = empty( $_link ) ? $cont : "<a href=\"$_link\">$cont</a>";
	}
	echo "<li>$cont</li>\n";
}

function _echo_slide_item_video( array $it, string $caption_type, bool $is_show ) {
	$_src = esc_url( $it['video'] );
	$cont = "<video><source src=\"$_src\"></video>";

	if ( $is_show ) {
		$cont .= _create_slide_caption( $it['caption'] ?? '', $caption_type );

		$_link = esc_url( $it['url'] ?? '' );
		$cont  = empty( $_link ) ? $cont : "<a href=\"$_link\">$cont</a>";
	}
	echo "<li>$cont</li>\n";
}

function _create_slide_caption( string $text, string $type ) {
	$div = '';
	if ( ! empty( $text ) ) {
		$ss  = separate_line( $text );
		$tmp = '<div><span>' . implode( '</span></div><div><span>', $ss ) . '</span></div>';
		$div = "<div class=\"gida-slider-show-caption $type\">$tmp</div>\n";
	}
	return $div;
}


// -----------------------------------------------------------------------------


function the_show_items( array $args, ?int $post_id = null ) {
	$post = get_post( $post_id );
	if ( null === $post ) return false;
	$post_id = $post->ID;

	$args           = _set_default_args( $args );
	[ $its, $opts ] = _get_data( true, $args, $post_id );
	$dom_id = "{$args['id']}-$post_id";

	foreach ( $its as $idx => $it ) {
		$event = "GIDA.slider_show_page('$dom_id', $idx);";
		$id    = $dom_id . "-$idx";
		if ( $it['type'] === 'image' ) {
			$cont = $it['img_tag'];
		} else if ( $it['type'] === 'video' ) {
			$_video = esc_url( $it['video'] );
			$cont = "<video><source src=\"$_video\"></video>";
		}
?>
		<li id="<?php echo $id; ?>"><a href="javascript:void(0)" onclick="<?php echo $event ?>"><?php echo $cont; ?></a></li>
<?php
	}
}


// -----------------------------------------------------------------------------


function _save_data( bool $is_show, array $args, int $post_id ) {
	$sub_keys = array_merge( [ 'media', 'type', 'delete' ], $is_show ? [ 'caption', 'caption_type', 'url' ] : [] );
	if ( $args['is_dual'] ) $sub_keys[] = 'media_sub';

	$its = get_multiple_post_meta_from_env( $args['key'], $sub_keys );
	$its = array_filter( $its, function ( $it ) { return ! $it['delete'] && $it['type'] !== 'template'; } );
	$its = array_values( $its );

	foreach ( $its as &$it ) {
		$pid = url_to_postid( $it['url'] );
		if ( $pid !== 0 ) $it['url'] = $pid;
	}
	$sub_keys = array_merge( [ 'media', 'type' ], $is_show ? [ 'caption', 'caption_type', 'url' ] : [] );
	if ( $args['is_dual'] ) $sub_keys[] = 'media_sub';

	$its['options'] = [];
	$its['options']['is_shuffled'] = $_POST["{$args['key']}_is_shuffled"] ? true : false;
	$its['options']['effect_type'] = $_POST["{$args['key']}_effect_type"];

	update_multiple_post_meta( $post_id, $args['key'], $its, $sub_keys, 'options' );
}

function _get_data( bool $is_show, array $args, int $post_id ): array {
	$sub_keys = array_merge( [ 'media', 'type' ], $is_show ? [ 'caption', 'caption_type', 'url' ] : [] );
	if ( $args['is_dual'] ) $sub_keys[] = 'media_sub';

	$post = get_post( $post_id );
	$its  = get_multiple_post_meta( $post->ID, $args['key'], $sub_keys, 'options' );

	$opts = $its['options'];
	unset( $its['options'] );

	foreach ( $its as &$it ) {
		if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {
			$permalink = get_permalink( $it['url'] );
			if ( $permalink !== false ) {
				$it['post_id'] = $it['url'];
				$it['url'] = $permalink;
			}
		}
		if ( empty( $it['type'] ) ) $it['type'] = 'image';
		if ( $it['type'] === 'image' ) {
			if ( ! empty( $it['media'] ) ) {
				_get_images( $it, intval( $it['media'] ), $args['view_size'] );
			}
			if ( $args['is_dual'] && ! empty( $it['media_sub'] ) ) {
				_get_images( $it, intval( $it['media_sub'] ), $args['view_size'], '_sub' );
			}
		} else if ( $it['type'] === 'video' ) {
			$it['video'] = wp_get_attachment_url( $it['media'] );
			$it = array_merge( $it, _get_image_meta( $it['media'] ) );
		}
	}
	if ( ! is_admin() && ( $opts['is_shuffled'] ?? $args['is_shuffled'] ) ) shuffle( $its );
	return [ $its, $opts ];
}

function _get_images( array &$it, int $aid, string $view_size, string $pf = '' ) {
	$tag = wp_get_attachment_image( $aid, 'full', false, [ 'sizes' => "(min-width: $view_size) $view_size, 100vw" ] );
	$it["img_tag$pf"] = "$tag\n";
	$it = array_merge( $it, _get_image_meta( $aid, $pf ) );
}

function _get_image_meta( int $aid, string $pf = '' ): array {
	$p = get_post( $aid );
	if ( $p === null ) return [];
	$t  = $p->post_title;
	$fn = basename( $p->guid );
	return [ "title$pf" => $t, "filename$pf" => $fn ];
}
