<?php
/**
 * Functions and Definitions for Keyvis
 *
 * @package Wplug Keyvis
 * @author Takuto Yanagida
 * @version 2022-07-25
 */

namespace wplug\keyvis;

require_once __DIR__ . '/assets/asset-url.php';
require_once __DIR__ . '/assets/multiple.php';
require_once __DIR__ . '/assets/util.php';
require_once __DIR__ . '/inc/template-admin.php';

/**
 * Initializes keyvis.
 *
 * @param array $args {
 *     (Optional) Array of arguments.
 *
 *     @type string 'url_to'                      Base URL.
 *
 *     @type string 'id'                          The ID of the output markup.
 *     @type string 'key'                         The base key of input.
 *     @type string 'class'                       CSS class name.
 *     @type string 'view_size'                   The width of view.
 *
 *     @type bool   'dual'                        Whether this item has dual images.
 *     @type bool   'do_enable_video'             Whether to enable video slides.
 *     @type bool   'do_scroll_picture'           Whether to scroll images.
 *     @type string 'caption_type'                Caption type ('line', 'circle', or 'subtitle').
 *     @type bool   'do_shuffle'                  Whether to shuffle slides.
 *
 *     @type string 'effect_type'                 Effect type ('fade', 'slide' or 'scroll').
 *     @type int    'duration_time'               Duration time [s].
 *     @type int    'transition_time'             Transition time [s].
 *     @type bool   'random_timing'               Whether changes the transition timing randomly.
 *     @type bool   'background_visible'          Whether the background images are visible.
 *     @type bool   'side_slide_visible'          Whether the side slides are visible.
 *
 *     @type bool   'do_show_caption_type_option' Whether to show caption type options. Default true.
 *     @type bool   'do_show_effect_type_option'  Whether to show the effect type option. Default true.
 *     @type bool   'do_show_shuffle_option'      Whether to show the shuffle option. Default true.
 * }
 */
function initialize( array $args = array() ) {
	$url_to = untrailingslashit( $args['url_to'] ?? get_file_uri( __DIR__ ) );
	_register_script( $url_to );
}

/**
 * Enqueues styles.
 */
function enqueue_style(): void {
	wp_enqueue_style( 'wplug-keyvis-show' );
	wp_enqueue_style( 'wplug-keyvis-hero' );
}

/**
 * Registers the scripts and styles.
 *
 * @access private
 *
 * @param string $url_to Base URL.
 */
function _register_script( string $url_to ) {
	if ( is_admin() ) {
		add_action(
			'admin_enqueue_scripts',
			function () use ( $url_to ) {
				wp_enqueue_script( 'wplug-keyvis-picker-link', abs_url( $url_to, './assets/js/picker-link.min.js' ), array( 'wplink', 'jquery-ui-autocomplete' ), '1.0', false );
				wp_enqueue_script( 'wplug-keyvis-picker-media', abs_url( $url_to, './assets/js/picker-media.min.js' ), array(), '1.0', true );
				wp_enqueue_script( 'wplug-keyvis-sortable', abs_url( $url_to, './assets/js/html5sortable.min.js' ), array(), '1.0', false );
				wp_enqueue_script( 'wplug-keyvis-template-admin', abs_url( $url_to, './assets/js/template-admin.min.js' ), array( 'wplug-keyvis-picker-link', 'wplug-keyvis-picker-media', 'wplug-keyvis-sortable' ), '1.0', false );
				wp_enqueue_style( 'wplug-keyvis-template-admin', abs_url( $url_to, './assets/css/template-admin.min.css' ), array(), '1.0' );
			}
		);
	} else {
		add_action(
			'wp_enqueue_scripts',
			function () use ( $url_to ) {
				wp_register_script( 'wplug-keyvis-show', abs_url( $url_to, './assets/js/show.min.js' ), array(), '1.0', false );
				wp_register_script( 'wplug-keyvis-hero', abs_url( $url_to, './assets/js/hero.min.js' ), array(), '1.0', false );
				wp_register_style( 'wplug-keyvis-show', abs_url( $url_to, './assets/css/show.min.css' ), array(), '1.0' );
				wp_register_style( 'wplug-keyvis-hero', abs_url( $url_to, './assets/css/hero.min.css' ), array(), '1.0' );
			}
		);
	}
}

/**
 * Assign default arguments.
 *
 * @access private
 *
 * @param array $args Array of arguments.
 * @return array Arguments.
 */
function _set_default_args( array $args ): array {
	// phpcs:disable
	$args['id']        = $args['id']        ?? 'keyvis';
	$args['key']       = $args['key']       ?? '_keyvis';
	$args['class']     = $args['class']     ?? '';
	$args['view_size'] = $args['view_size'] ?? '96rem';

	$args['dual']              = $args['dual']              ?? false;
	$args['do_enable_video']   = $args['do_enable_video']   ?? false;
	$args['do_scroll_picture'] = $args['do_scroll_picture'] ?? false;
	$args['caption_type']      = $args['caption_type']      ?? 'subtitle';  // 'line' or 'circle'
	$args['do_shuffle']        = $args['do_shuffle']        ?? false;

	$args['effect_type']        = $args['effect_type']        ?? 'slide';  // 'scroll' or 'fade'
	$args['duration_time']      = $args['duration_time']      ?? 8;  // [second]
	$args['transition_time']    = $args['transition_time']    ?? 1;  // [second]
	$args['random_timing']      = $args['random_timing']      ?? false;
	$args['background_visible'] = $args['background_visible'] ?? true;
	$args['side_slide_visible'] = $args['side_slide_visible'] ?? false;

	$args['do_show_caption_type_option'] = $args['do_show_caption_type_option'] ?? true;
	$args['do_show_effect_type_option']  = $args['do_show_effect_type_option']  ?? true;
	$args['do_show_shuffle_option']      = $args['do_show_shuffle_option']      ?? true;
	// phpcs:enable
	return $args;
}

/**
 * Create option strings.
 *
 * @access private
 *
 * @param array $args Array of arguments.
 * @param array $opts Array of options assigned in the admin screen.
 * @return string JSON string.
 */
function _create_option_str( array $args, array $opts ): string {
	$opts = array(
		'effect_type'        => $opts['effect_type'] ?? $args['effect_type'],
		'duration_time'      => $args['duration_time'],
		'transition_time'    => $args['transition_time'],
		'random_timing'      => $args['random_timing'],
		'background_visible' => $args['background_visible'],
		'side_slide_visible' => $args['side_slide_visible'],
	);
	return wp_json_encode( $opts );
}


// -----------------------------------------------------------------------------


/**
 * Adds the meta box for slider 'show' to template admin screen.
 *
 * @param array       $args     Array of arguments.
 * @param string      $title    Title of the meta box.
 * @param string|null $screen   (Optional) The screen or screens on which to show the box.
 * @param string      $context  (Optional) The context within the screen where the box should display.
 * @param string      $priority (Optional) The priority within the context where the box should show.
 */
function add_meta_box_show( array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	add_meta_box_template_admin( true, $args, $title, $screen, $context );
}

/**
 * Stores the data of the meta box for slider 'show' on template admin screen.
 *
 * @param array $args    Array of arguments.
 * @param int   $post_id Post ID.
 */
function save_meta_box_show( array $args, int $post_id ) {
	save_meta_box_template_admin( true, $args, $post_id );
}

/**
 * Adds the meta box for slider 'hero' to template admin screen.
 *
 * @param array       $args     Array of arguments.
 * @param string      $title    Title of the meta box.
 * @param string|null $screen   (Optional) The screen or screens on which to show the box.
 * @param string      $context  (Optional) The context within the screen where the box should display.
 * @param string      $priority (Optional) The priority within the context where the box should show.
 */
function add_meta_box_hero( array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	add_meta_box_template_admin( false, $args, $title, $screen, $context );
}

/**
 * Stores the data of the meta box for slider 'hero' on template admin screen.
 *
 * @param array $args    Array of arguments.
 * @param int   $post_id Post ID.
 */
function save_meta_box_hero( array $args, int $post_id ) {
	save_meta_box_template_admin( false, $args, $post_id );
}


// -----------------------------------------------------------------------------


/**
 * Displays the slider 'show'.
 *
 * @param array    $args    Array of arguments.
 * @param int|null $post_id (Optional) Post ID.
 * @return bool Whether the slider is shown.
 */
function the_show( array $args, ?int $post_id = null ): bool {
	wp_enqueue_style( 'wplug-keyvis-show' );
	wp_enqueue_script( 'wplug-keyvis-show' );

	$post = get_post( $post_id );
	if ( null === $post ) {
		return false;
	}
	$post_id = $post->ID;

	$args               = _set_default_args( $args );
	list( $its, $opts ) = _get_data( true, $args, $post_id );
	if ( empty( $its ) ) {
		return false;
	}
	$dom_id  = "{$args['id']}-$post_id";
	$dom_cls = empty( $args['class'] ) ? '' : " {$args['class']}";
	?>
	<section class="gida-slider-show<?php echo esc_attr( $dom_cls ); ?>" id="<?php echo esc_attr( $dom_id ); ?>">
		<div class="gida-slider-show-frame">
			<ul class="gida-slider-show-slides">
	<?php
	foreach ( $its as $it ) {
		$cap_type = $it['caption_type'] ?? $args['caption_type'];
		if ( 'image' === $it['type'] ) {
			_echo_slide_item_img( $it, $cap_type, true, $args['do_scroll_picture'] );
		} elseif ( 'video' === $it['type'] ) {
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

/**
 * Displays the slider 'hero'.
 *
 * @param array    $args    Array of arguments.
 * @param int|null $post_id (Optional) Post ID.
 * @return bool Whether the slider is shown.
 */
function the_hero( array $args, ?int $post_id = null ): bool {
	wp_enqueue_style( 'wplug-keyvis-hero' );
	wp_enqueue_script( 'wplug-keyvis-hero' );

	$post = get_post( $post_id );
	if ( null === $post ) {
		return false;
	}
	$post_id = $post->ID;

	$args               = _set_default_args( $args );
	list( $its, $opts ) = _get_data( false, $args, $post_id );
	if ( empty( $its ) ) {
		return false;
	}
	$dom_id  = "{$args['id']}-$post_id";
	$dom_cls = empty( $args['class'] ) ? '' : " {$args['class']}";
	?>
	<section class="gida-slider-hero<?php echo esc_attr( $dom_cls ); ?>" id="<?php echo esc_attr( $dom_id ); ?>">
		<div class="gida-slider-hero-frame">
			<ul class="gida-slider-hero-slides">
	<?php
	foreach ( $its as $it ) {
		if ( 'image' === $it['type'] ) {
			_echo_slide_item_img( $it, '', false, $args['do_scroll_picture'] );
		} elseif ( 'video' === $it['type'] ) {
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

/**
 * Displays a image slide item.
 *
 * @access private
 *
 * @param array  $it           The item.
 * @param string $caption_type Caption type.
 * @param bool   $is_show      Whether this slider is 'show'.
 * @param bool   $do_scroll    Whether to scroll images.
 */
function _echo_slide_item_img( array $it, string $caption_type, bool $is_show, bool $do_scroll ) {
	$cont = $it['img_tag'] . ( $it['img_tag_sub'] ?? '' );

	if ( $is_show ) {
		$cont .= _create_slide_caption( $it['caption'] ?? '', $caption_type );

		$_link = esc_url( $it['url'] ?? '' );
		$cont  = empty( $_link ) ? $cont : "<a href=\"$_link\">$cont</a>";
	}
	$cls = $do_scroll ? ' class="scroll"' : '';
	echo "<li$cls>$cont</li>\n";  // phpcs:ignore
}

/**
 * Displays a video slide item.
 *
 * @access private
 *
 * @param array  $it           The item.
 * @param string $caption_type Caption type.
 * @param bool   $is_show      Whether this slider is 'show'.
 */
function _echo_slide_item_video( array $it, string $caption_type, bool $is_show ) {
	$_src = esc_url( $it['video'] );
	$cont = "<video><source src=\"$_src\"></video>";

	if ( $is_show ) {
		$cont .= _create_slide_caption( $it['caption'] ?? '', $caption_type );

		$_link = esc_url( $it['url'] ?? '' );
		$cont  = empty( $_link ) ? $cont : "<a href=\"$_link\">$cont</a>";
	}
	echo "<li>$cont</li>\n";  // phpcs:ignore
}

/**
 * Creates the caption of slide item.
 *
 * @access private
 *
 * @param string $text Caption text.
 * @param string $type Caption type.
 * @return string Markup for caption.
 */
function _create_slide_caption( string $text, string $type ): string {
	$div = '';
	if ( ! empty( $text ) ) {
		$ss  = separate_line( $text );
		$tmp = '<div><span>' . implode( '</span></div><div><span>', $ss ) . '</span></div>';
		$div = "<div class=\"gida-slider-show-caption $type\">$tmp</div>\n";
	}
	return $div;
}


// -----------------------------------------------------------------------------


/**
 * Display the slider 'show' items as thumbnails.
 *
 * @param array $args    Array of arguments.
 * @param int   $post_id Post ID.
 */
function the_show_items( array $args, ?int $post_id = null ) {
	$post = get_post( $post_id );
	if ( null === $post ) {
		return false;
	}
	$post_id = $post->ID;

	$args               = _set_default_args( $args );
	list( $its, $opts ) = _get_data( true, $args, $post_id );
	$dom_id             = "{$args['id']}-$post_id";

	foreach ( $its as $idx => $it ) {
		$event = "GIDA.slider_show_page('$dom_id', $idx);";
		$id    = $dom_id . "-$idx";
		if ( 'image' === $it['type'] ) {
			$cont = $it['img_tag'];
		} elseif ( 'video' === $it['type'] ) {
			$_video = esc_url( $it['video'] );
			$cont   = "<video><source src=\"$_video\"></video>";
		}
		?>
		<li id="<?php echo esc_attr( $id ); ?>"><a href="javascript:void(0)" onclick="<?php echo esc_js( $event ); ?>"><?php echo $cont; // phpcs:ignore ?></a></li>
		<?php
	}
}


// -----------------------------------------------------------------------------


/**
 * Stores the data of the slider from a post.
 *
 * @access private
 *
 * @param bool  $is_show Whether this slider is 'show'.
 * @param array $args    Array of arguments.
 * @param int   $post_id Post ID.
 */
function _save_data( bool $is_show, array $args, int $post_id ) {
	$sub_keys = array_merge( array( 'media', 'type', 'delete' ), $is_show ? array( 'caption', 'caption_type', 'url' ) : array() );
	if ( $args['dual'] ) {
		$sub_keys[] = 'media_sub';
	}
	$its = get_multiple_post_meta_from_env( $args['key'], $sub_keys );
	$its = array_filter(
		$its,
		function ( $it ) {
			return ! $it['delete'] && 'template' !== $it['type'];
		}
	);
	$its = array_values( $its );

	foreach ( $its as &$it ) {
		$pid = url_to_postid( $it['url'] );
		if ( 0 !== $pid ) {
			$it['url'] = $pid;
		}
	}
	$sub_keys = array_merge( array( 'media', 'type' ), $is_show ? array( 'caption', 'caption_type', 'url' ) : array() );
	if ( $args['dual'] ) {
		$sub_keys[] = 'media_sub';
	}
	$its['options']                = array();
	$its['options']['do_shuffle']  = ( $_POST[ "{$args['key']}_do_shuffle" ] ?? false ) ? true : false;  // phpcs:ignore
	$its['options']['effect_type'] = 'slide';

	$et = $_POST[ "{$args['key']}_effect_type" ] ?? '';  // phpcs:ignore
	if ( in_array( $et, array( 'fade', 'slide', 'scroll' ), true ) ) {
		$its['options']['effect_type'] = $et;
	}
	set_multiple_post_meta( $post_id, $args['key'], $its, $sub_keys, 'options' );
}

/**
 * Retrieves the data of the slider from a post.
 *
 * @access private
 *
 * @param bool  $is_show Whether this slider is 'show'.
 * @param array $args    Array of arguments.
 * @param int   $post_id Post ID.
 */
function _get_data( bool $is_show, array $args, int $post_id ): array {
	$sub_keys = array_merge( array( 'media', 'type' ), $is_show ? array( 'caption', 'caption_type', 'url' ) : array() );
	if ( $args['dual'] ) {
		$sub_keys[] = 'media_sub';
	}
	$post = get_post( $post_id );
	$its  = get_multiple_post_meta( $post->ID, $args['key'], $sub_keys, 'options' );

	$opts = $its['options'] ?? array();
	unset( $its['options'] );

	foreach ( $its as &$it ) {
		if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {
			$permalink = get_permalink( $it['url'] );
			if ( false !== $permalink ) {
				$it['post_id'] = $it['url'];
				$it['url']     = $permalink;
			}
		}
		if ( empty( $it['type'] ) ) {
			$it['type'] = 'image';
		}
		if ( 'image' === $it['type'] ) {
			if ( ! empty( $it['media'] ) ) {
				_get_images( $it, intval( $it['media'] ), $args['view_size'] );
			}
			if ( $args['dual'] && ! empty( $it['media_sub'] ) ) {
				_get_images( $it, intval( $it['media_sub'] ), $args['view_size'], '_sub' );
			}
		} elseif ( 'video' === $it['type'] ) {
			$it['video'] = wp_get_attachment_url( $it['media'] );
			$it          = array_merge( $it, _get_image_meta( $it['media'] ) );
		}
	}
	if ( ! is_admin() && ( $opts['do_shuffle'] ?? $args['do_shuffle'] ) ) {
		shuffle( $its );
	}
	return array( $its, $opts );
}

/**
 * Gets the images of the item.
 *
 * @access private
 *
 * @param array  $it        The item.
 * @param int    $aid       Attachment ID.
 * @param string $view_size The width of view.
 * @param string $pf        Prefix of key.
 */
function _get_images( array &$it, int $aid, string $view_size, string $pf = '' ) {
	$tag = wp_get_attachment_image( $aid, 'full', false, array( 'sizes' => "(min-width: $view_size) $view_size, 100vw" ) );

	$it[ "img_tag$pf" ] = "$tag\n";
	$it                 = array_merge( $it, _get_image_meta( $aid, $pf ) );
}

/**
 * Gets image meta data.
 *
 * @access private
 *
 * @param int    $aid Attachment ID.
 * @param string $pf  Prefix of key.
 * @return array An array of image meta.
 */
function _get_image_meta( int $aid, string $pf = '' ): array {
	$p = get_post( $aid );
	if ( null === $p ) {
		return array();
	}
	$t  = $p->post_title;
	$fn = basename( $p->guid );
	return array(
		"title$pf"    => $t,
		"filename$pf" => $fn,
	);
}
