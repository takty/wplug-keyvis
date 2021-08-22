<?php
/**
 * Functions and Definitions for Slider
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-02
 */

namespace wplug\slider;

require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../util/text.php';
require_once __DIR__ . '/../util/url.php';
require_once __DIR__ . '/../admin/ss-support.php';


function initialize( array $args = [] ) {
	$inst = _get_instance();

	$key_base = $args['key_base'] ?? '_slider_show_';
	_set_key_base( $key_base );

	$url_to = untrailingslashit( $args['url_to'] ?? get_file_uri( __DIR__ ) );

	$inst->_key = $key;
	$inst->_id  = $key;

	$inst->_effect_type           = $args['_effect_type']           ?? 'slide'; // 'scroll' or 'fade'
	$inst->_duration_time         = $args['_duration_time']         ?? 8; // [second]
	$inst->_transition_time       = $args['_transition_time']       ?? 1; // [second]
	$inst->_background_opacity    = $args['_background_opacity']    ?? 0.33;
	$inst->_is_picture_scroll     = $args['_is_picture_scroll']     ?? false;
	$inst->_is_random_timing      = $args['_is_random_timing']      ?? false;
	$inst->_is_background_visible = $args['_is_background_visible'] ?? true;
	$inst->_is_side_slide_visible = $args['_is_side_slide_visible'] ?? false;
	$inst->_zoom_rate             = $args['_zoom_rate']             ?? 1;

	$inst->_caption_type          = $args['_caption_type']     ?? 'subtitle'; // 'line' or 'circle'
	$inst->_is_dual               = $args['_is_dual']          ?? false;
	$inst->_is_video_enabled      = $args['_is_video_enabled'] ?? false;
	$inst->_is_shuffled           = $args['_is_shuffled']      ?? false;

	_register_script( $url_to );
}

function _register_script( string $url_to ) {
	$inst = _get_instance();
	if ( is_admin() ) {
		add_action( 'admin_enqueue_scripts', function () use ( $url_to ) {
			wp_enqueue_script( 'picker-link',  abs_url( $url_to, './asset/lib/picker-link.min.js' ), [ 'wplink', 'jquery-ui-autocomplete' ] );
			wp_enqueue_script( 'picker-media', abs_url( $url_to, './asset/lib/picker-media.min.js' ), [], 1.0, true );
			wp_enqueue_script( $inst::NS, abs_url( $url_to, './asset/slide-show.min.js' ), [ 'picker-media', 'jquery-ui-sortable' ] );
			wp_enqueue_style(  $inst::NS, abs_url( $url_to, './asset/slide-show.min.css' ) );
		} );
	} else {
		add_action( 'wp_enqueue_scripts', function () use ( $url_to ) {
			wp_register_style( $inst::NS, abs_url( $url_to, '/assets/css/show.min.css' ) );
			wp_register_script( $inst::NS, abs_url( $url_to, '/assets/js/show.min.js' ) );
		} );
	}
}

function _create_option_str() {
	$inst = _get_instance();
	$opts = [
		'effect_type'           => $inst->_effect_type,
		'duration_time'         => $inst->_duration_time,
		'transition_time'       => $inst->_transition_time,
		'background_opacity'    => $inst->_background_opacity,
		'is_picture_scroll'     => $inst->_is_picture_scroll,
		'is_random_timing'      => $inst->_is_random_timing,
		'is_background_visible' => $inst->_is_background_visible,
		'is_side_slide_visible' => $inst->_is_side_slide_visible,
		'zoom_rate'             => $inst->_zoom_rate,
	];
	return json_encode( $opts );
}


// -----------------------------------------------------------------------------


function add_meta_box( string $label, string $screen, string $context = 'advanced' ) {
	add_meta_box_template_admin( $label, $screen, $context );
}

function save_meta_box( int $post_id ) {
	save_meta_box_template_admin( $post_id );
}


// -----------------------------------------------------------------------------


function the_show( ?int $post_id = null, string $size = 'large', string $cls = '' ): bool {
	wp_enqueue_style( $inst::NS );
	wp_enqueue_script( $inst::NS );

	$post = get_post( $post_id );
	$its = _get_items( $post->ID, $size );
	if ( empty( $its ) ) return false;

	$inst = _get_instance();
	$dom_id   = "{$inst->_id}-$post->ID";
	$dom_cls  = empty( $cls ) ? '' : " $cls";
	$opts_str = _create_option_str();
	$_urls    = [];
?>
	<section class="gida-slider-show<?php echo $dom_cls ?>" id="<?php echo $dom_id ?>">
		<div class="gida-slider-show-frame">
			<ul class="gida-slider-show-slides">
<?php
	foreach ( $its as $it ) {
		if ( $it['type'] === $inst::TYPE_IMAGE ) _echo_slide_item_img( $it, $_urls );
		else if ( $it['type'] === $inst::TYPE_VIDEO ) _echo_slide_item_video( $it, $_urls );
	}
?>
			</ul>
			<div class="gida-slider-show-prev"></div>
			<div class="gida-slider-show-next"></div>
		</div>
		<div class="gida-slider-show-rivets"></div>
		<script>st_slide_show_initialize('<?php echo $dom_id ?>', <?php echo $opts_str ?>);</script>
		<?php if ( $inst::is_simply_static_active() ) echo _create_dummy_style( $_urls ); ?>
	</section>
<?php
	return true;
}

function the_items( int $post_id = null, string $size = 'medium' ) {
	$inst   = _get_instance();
	$post   = get_post( $post_id );
	$dom_id = "{$inst->_id}-$post->ID";
	$its    = _get_items( $post->ID, $size );

	foreach ( $its as $idx => $it ) {
		$event = "st_slide_show_page('$dom_id', $idx);";
		$id = $dom_id . "-$idx";
		if ( $it['type'] === $inst::TYPE_IMAGE ) {
			$_img   = esc_url( $it['image'] );
			$_style = "background-image: url('$_img');";
?>
			<li id="<?php echo $id ?>"><a href="javascript:void(0)" onclick="<?php echo $event ?>" style="<?php echo $_style ?>"></a></li>
<?php
		} else if ( $it['type'] === $inst::TYPE_VIDEO ) {
			$_video = esc_url( $it['video'] );
?>
			<li id="<?php echo $id ?>"><a href="javascript:void(0)" onclick="<?php echo $event ?>"><video><source src="<?php echo $_video ?>"></video></a></li>
<?php
		}
	}
}

function _echo_slide_item_img( array $it, array &$_urls ) {
	$imgs   = $it['images'];
	$imgs_s = isset( $it['images_sub'] ) ? $it['images_sub'] : false;
	$data = [];

	$inst = _get_instance();
	if ( $inst->_is_dual && $imgs_s !== false ) {
		_set_attrs( $data, 'img-sub', $imgs_s );
	}
	_set_attrs( $data, 'img', $imgs );
	$attr = '';
	foreach ( $data as $key => $val ) {
		$attr .= " data-$key=\"$val\"";
	}
	$cont = _create_slide_content( $it['caption'], $it['url'] );

	if ( is_simply_static_active() ) {  // for fallback
		foreach ( $data as $key => $val ) {
			$_urls[] = $val;
		}
	}
	echo "<li$attr>$cont</li>";
}

function _echo_slide_item_video( array $it, array &$_urls ) {
	$_url = esc_url( $it['video'] );
	$attr = " data-video=\"$_url\"";
	$cont = _create_slide_content( $it['caption'], $it['url'] );

	if ( is_simply_static_active() ) {  // for fallback
		$_urls[] = $_url;
	}
	echo "<li$attr>$cont</li>";
}

function _create_dummy_style( array $_urls ): string {
	$style = '<style>stinc{';
	foreach ( $_urls as $_url ) $style .= "p:url('$_url');";
	$style .= '}</style>';
	return $style;
}

function _set_attrs( array &$data, string $key, array $imgs ) {
	if ( 2 <= count( $imgs ) ) {
		$data["$key-phone"] = esc_url( $imgs[0] );
		$data[ $key ]       = esc_url( $imgs[1] );
	} else {
		$data[ $key ] = esc_url( $imgs[0] );
	}
}

function _create_slide_content( string $cap, string $url ) {
	$inst = _get_instance();
	$div = '';
	if ( ! empty( $cap ) ) {
		$ss  = separate_line( $cap );
		$str = '<div><span>' . implode( '</span></div><div><span>', $ss ) . '</span></div>';
		$div = '<div class="gida-slider-show-caption' . " {$inst->_caption_type}\">$str</div>";
	}
	if ( empty( $url ) ) return $div;
	$_url = esc_url( $url );
	return "<a href=\"$_url\">$div</a>";
}
