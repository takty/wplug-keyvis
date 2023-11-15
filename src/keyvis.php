<?php
/**
 * Functions and Definitions for Keyvis
 *
 * @package Wplug Keyvis
 * @author Takuto Yanagida
 * @version 2023-11-15
 */

declare(strict_types=1);

namespace wplug\keyvis;

require_once __DIR__ . '/assets/asset-url.php';
require_once __DIR__ . '/assets/multiple.php';
require_once __DIR__ . '/inc/template-admin.php';
require_once __DIR__ . '/inc/util.php';

/** phpcs:ignore
 * Initializes keyvis.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args (Optional) Array of arguments.
 * $args {
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
function initialize( array $args = array() ): void {
	$url_to = untrailingslashit( $args['url_to'] ?? \wplug\get_file_uri( __DIR__ ) );
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
function _register_script( string $url_to ): void {
	if ( is_admin() ) {
		add_action(
			'admin_enqueue_scripts',
			function () use ( $url_to ) {
				wp_enqueue_script( 'wplug-keyvis-picker-link', \wplug\abs_url( $url_to, './assets/js/picker-link.min.js' ), array( 'wplink', 'jquery-ui-autocomplete' ), '1.0', false );
				wp_enqueue_script( 'wplug-keyvis-picker-media', \wplug\abs_url( $url_to, './assets/js/picker-media.min.js' ), array(), '1.0', true );
				wp_enqueue_script( 'wplug-keyvis-sortable', \wplug\abs_url( $url_to, './assets/js/html5sortable.min.js' ), array(), '1.0', false );
				wp_enqueue_script( 'wplug-keyvis-template-admin', \wplug\abs_url( $url_to, './assets/js/template-admin.min.js' ), array( 'wplug-keyvis-picker-link', 'wplug-keyvis-picker-media', 'wplug-keyvis-sortable' ), '1.0', false );
				wp_enqueue_style( 'wplug-keyvis-template-admin', \wplug\abs_url( $url_to, './assets/css/template-admin.min.css' ), array(), '1.0' );
			}
		);
	} else {
		add_action(
			'wp_enqueue_scripts',
			function () use ( $url_to ) {
				wp_register_script( 'wplug-keyvis-show', \wplug\abs_url( $url_to, './assets/js/show.min.js' ), array(), '1.0', false );
				wp_register_script( 'wplug-keyvis-hero', \wplug\abs_url( $url_to, './assets/js/hero.min.js' ), array(), '1.0', false );
				wp_register_style( 'wplug-keyvis-show', \wplug\abs_url( $url_to, './assets/css/show.min.css' ), array(), '1.0' );
				wp_register_style( 'wplug-keyvis-hero', \wplug\abs_url( $url_to, './assets/css/hero.min.css' ), array(), '1.0' );
			}
		);
	}
}

/** phpcs:ignore
 * Assign default arguments.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @return array{
 *     url_to?                    : string,
 *     id                         : string,
 *     key                        : string,
 *     class                      : string,
 *     view_size                  : string,
 *     dual                       : bool,
 *     do_enable_video            : bool,
 *     do_scroll_picture          : bool,
 *     caption_type               : string,
 *     do_shuffle                 : bool,
 *     effect_type                : string,
 *     duration_time              : int,
 *     transition_time            : int,
 *     random_timing              : bool,
 *     background_visible         : bool,
 *     side_slide_visible         : bool,
 *     do_show_caption_type_option: bool,
 *     do_show_effect_type_option : bool,
 *     do_show_shuffle_option     : bool,
 * } Arguments.
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

/** phpcs:ignore
 * Create option strings.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     effect_type       : string,
 *     duration_time     : int,
 *     transition_time   : int,
 *     random_timing     : bool,
 *     background_visible: bool,
 *     side_slide_visible: bool,
 * } $args Array of arguments.
 * @param array{ effect_type?: string } $opts Array of options assigned in the admin screen.
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
	$str  = wp_json_encode( $opts );
	return $str ? $str : '';
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Adds the meta box for slider 'show' to template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param string                        $title    Title of the meta box.
 * @param string|null                   $screen   (Optional) The screen or screens on which to show the box.
 * @param 'advanced'|'normal'|'side'    $context  (Optional) The context within the screen where the box should display.
 * @param 'core'|'default'|'high'|'low' $priority (Optional) The priority within the context where the box should show.
 */
function add_meta_box_show( array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ): void {
	add_meta_box_template_admin( true, $args, $title, $screen, $context, $priority );
}

/** phpcs:ignore
 * Stores the data of the meta box for slider 'show' on template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param int    $post_id Post ID.
 */
function save_meta_box_show( array $args, int $post_id ): void {
	save_meta_box_template_admin( true, $args, $post_id );
}

/** phpcs:ignore
 * Adds the meta box for slider 'hero' to template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param string                        $title    Title of the meta box.
 * @param string|null                   $screen   (Optional) The screen or screens on which to show the box.
 * @param 'advanced'|'normal'|'side'    $context  (Optional) The context within the screen where the box should display.
 * @param 'core'|'default'|'high'|'low' $priority (Optional) The priority within the context where the box should show.
 */
function add_meta_box_hero( array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ): void {
	add_meta_box_template_admin( false, $args, $title, $screen, $context, $priority );
}

/** phpcs:ignore
 * Stores the data of the meta box for slider 'hero' on template admin screen.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param int    $post_id Post ID.
 */
function save_meta_box_hero( array $args, int $post_id ): void {
	save_meta_box_template_admin( false, $args, $post_id );
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Displays the slider 'show'.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param int|null $post_id (Optional) Post ID.
 * @return bool Whether the slider is shown.
 */
function the_show( array $args, ?int $post_id = null ): bool {
	wp_enqueue_style( 'wplug-keyvis-show' );
	wp_enqueue_script( 'wplug-keyvis-show' );

	$post = get_post( $post_id );
	if ( ! ( $post instanceof \WP_Post ) ) {
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
		$type     = isset( $it['type'] ) ? $it['type'] : '';
		$cap_type = isset( $it['caption_type'] ) ? $it['caption_type'] : $args['caption_type'];
		if ( 'image' === $type ) {
			_echo_slide_item_img( $it, $cap_type, true, $args['do_scroll_picture'] );
		} elseif ( 'video' === $type ) {
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

/** phpcs:ignore
 * Displays the slider 'hero'.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param int|null $post_id (Optional) Post ID.
 * @return bool Whether the slider is shown.
 */
function the_hero( array $args, ?int $post_id = null ): bool {
	wp_enqueue_style( 'wplug-keyvis-hero' );
	wp_enqueue_script( 'wplug-keyvis-hero' );

	$post = get_post( $post_id );
	if ( ! ( $post instanceof \WP_Post ) ) {
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
		$type = isset( $it['type'] ) ? $it['type'] : '';
		if ( 'image' === $type ) {
			_echo_slide_item_img( $it, '', false, $args['do_scroll_picture'] );
		} elseif ( 'video' === $type ) {
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

/** phpcs:ignore
 * Displays a image slide item.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     img_tag?    : string,
 *     img_tag_sub?: string,
 *     caption?    : string,
 *     url?        : string,
 * } $it The item.
 * @param string $caption_type Caption type.
 * @param bool   $is_show      Whether this slider is 'show'.
 * @param bool   $do_scroll    Whether to scroll images.
 */
function _echo_slide_item_img( array $it, string $caption_type, bool $is_show, bool $do_scroll ): void {
	$cont = '';
	if ( isset( $it['img_tag'] ) ) {
		$cont = $it['img_tag'];
	}
	if ( isset( $it['img_tag_sub'] ) ) {
		$cont .= $it['img_tag_sub'];
	}
	if ( $is_show ) {
		$cont .= _create_slide_caption( $it['caption'] ?? '', $caption_type );

		$_link = esc_url( $it['url'] ?? '' );
		$cont  = empty( $_link ) ? $cont : "<a href=\"$_link\">$cont</a>";
	}
	$cls = $do_scroll ? ' class="scroll"' : '';
	echo "<li$cls>$cont</li>\n";  // phpcs:ignore
}

/** phpcs:ignore
 * Displays a video slide item.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     video?  : string,
 *     caption?: string,
 *     url?    : string,
 * } $it The item.
 * @param string $caption_type Caption type.
 * @param bool   $is_show      Whether this slider is 'show'.
 */
function _echo_slide_item_video( array $it, string $caption_type, bool $is_show ): void {
	$cont = '';
	if ( isset( $it['video'] ) ) {
		$_src = esc_url( $it['video'] );
		$cont = '<video><source src="' . esc_url( $it['video'] ) . '"></video>';
	}
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


/** phpcs:ignore
 * Retrieves the slider items from a post.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param int|null $post_id (Optional) Post ID.
 * @return array{
 *     type?        : string,
 *     media?       : string,
 *     title?       : string,
 *     filename?    : string,
 *     img_tag?     : string,
 *     media_sub?   : string,
 *     title_sub?   : string,
 *     filename_sub?: string,
 *     img_tag_sub? : string,
 *     video?       : string,
 *     caption?     : string,
 *     url?         : string,
 *     caption_type?: string,
 * }[] Array of slider items.
 */
function get_items( array $args, ?int $post_id = null ): array {
	$post = get_post( $post_id );
	if ( ! ( $post instanceof \WP_Post ) ) {
		return array();
	}
	$post_id = $post->ID;

	$args                = _set_default_args( $args );
	list( $its, $_opts ) = _get_data( true, $args, $post_id );
	return $its;
}

/** phpcs:ignore
 * Displays the slider items as thumbnails.
 *
 * phpcs:ignore
 * @param array{
 *     url_to?                     : string,
 *     id?                         : string,
 *     key?                        : string,
 *     class?                      : string,
 *     view_size?                  : string,
 *     dual?                       : bool,
 *     do_enable_video?            : bool,
 *     do_scroll_picture?          : bool,
 *     caption_type?               : string,
 *     do_shuffle?                 : bool,
 *     effect_type?                : string,
 *     duration_time?              : int,
 *     transition_time?            : int,
 *     random_timing?              : bool,
 *     background_visible?         : bool,
 *     side_slide_visible?         : bool,
 *     do_show_caption_type_option?: bool,
 *     do_show_effect_type_option? : bool,
 *     do_show_shuffle_option?     : bool,
 * } $args Array of arguments.
 * @param int|null $post_id (Optional) Post ID.
 * @return bool Whether the items exist.
 */
function the_items( array $args, ?int $post_id = null ): bool {
	$post = get_post( $post_id );
	if ( ! ( $post instanceof \WP_Post ) ) {
		return false;
	}
	$post_id = $post->ID;

	$args                = _set_default_args( $args );
	list( $its, $_opts ) = _get_data( true, $args, $post_id );
	$dom_id              = "{$args['id']}-$post_id";

	foreach ( $its as $idx => $it ) {
		$event = "GIDA.sliders['$dom_id'].move($idx);";
		$id    = $dom_id . "-$idx";
		$cont  = '';
		$type  = isset( $it['type'] ) ? $it['type'] : '';
		if ( 'image' === $type && isset( $it['img_tag'] ) ) {
			$cont = $it['img_tag'];
		} elseif ( 'video' === $type && isset( $it['video'] ) ) {
			$_video = esc_url( $it['video'] );
			$cont   = "<video><source src=\"$_video\"></video>";
		}
		?>
		<li id="<?php echo esc_attr( $id ); ?>"><button type="button" onclick="<?php echo esc_js( $event ); ?>"><?php echo $cont; // phpcs:ignore ?></button></li>
		<?php
	}
	return true;
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Stores the data of the slider from a post.
 *
 * @access private
 *
 * @param bool   $is_show Whether this slider is 'show'.
 * phpcs:ignore
 * @param array{
 *     url_to?                    : string,
 *     id                         : string,
 *     key                        : string,
 *     class                      : string,
 *     view_size                  : string,
 *     dual                       : bool,
 *     do_enable_video            : bool,
 *     do_scroll_picture          : bool,
 *     caption_type               : string,
 *     do_shuffle                 : bool,
 *     effect_type                : string,
 *     duration_time              : int,
 *     transition_time            : int,
 *     random_timing              : bool,
 *     background_visible         : bool,
 *     side_slide_visible         : bool,
 *     do_show_caption_type_option: bool,
 *     do_show_effect_type_option : bool,
 *     do_show_shuffle_option     : bool,
 * } $args Array of arguments.
 * @param int    $post_id Post ID.
 */
function _save_data( bool $is_show, array $args, int $post_id ): void {
	$sub_keys = array_merge( array( 'media', 'type', 'delete' ), $is_show ? array( 'caption', 'url' ) : array() );
	if ( $is_show && $args['do_show_caption_type_option'] ) {
		$sub_keys[] = 'caption_type';
	}
	if ( $args['dual'] ) {
		$sub_keys[] = 'media_sub';
	}
	$its = \wplug\get_multiple_post_meta_from_env( $args['key'], $sub_keys );
	$its = array_filter(
		$its,
		function ( $it ) {
			return ! $it['delete'] && 'template' !== $it['type'];
		}
	);
	$its = array_values( $its );

	foreach ( $its as &$it ) {
		$pid = 0;
		if ( is_string( $it['url'] ?? null ) ) {
			$pid = url_to_postid( $it['url'] );
		}
		if ( 0 !== $pid ) {
			$it['post_id'] = $pid;
		}
	}
	$sub_keys = array_merge( array( 'media', 'type' ), $is_show ? array( 'caption', 'url', 'post_id' ) : array() );
	if ( $is_show && $args['do_show_caption_type_option'] ) {
		$sub_keys[] = 'caption_type';
	}
	if ( $args['dual'] ) {
		$sub_keys[] = 'media_sub';
	}
	$its['options']               = array();
	$its['options']['do_shuffle'] = ( $_POST[ "{$args['key']}_do_shuffle" ] ?? false ) ? true : false;  // phpcs:ignore

	if ( $args['do_show_effect_type_option'] ) {
		$its['options']['effect_type'] = 'slide';

		$et = $_POST[ "{$args['key']}_effect_type" ] ?? '';  // phpcs:ignore
		if ( in_array( $et, array( 'fade', 'slide', 'scroll' ), true ) ) {
			$its['options']['effect_type'] = $et;
		}
	}
	\wplug\set_multiple_post_meta( $post_id, $args['key'], $its, $sub_keys, 'options' );
}

/** phpcs:ignore
 * Retrieves the data of the slider from a post.
 *
 * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
 * @access private
 *
 * @param bool   $is_show Whether this slider is 'show'.
 * phpcs:ignore
 * @param array{
 *     url_to?                    : string,
 *     id                         : string,
 *     key                        : string,
 *     class                      : string,
 *     view_size                  : string,
 *     dual                       : bool,
 *     do_enable_video            : bool,
 *     do_scroll_picture          : bool,
 *     caption_type               : string,
 *     do_shuffle                 : bool,
 *     effect_type                : string,
 *     duration_time              : int,
 *     transition_time            : int,
 *     random_timing              : bool,
 *     background_visible         : bool,
 *     side_slide_visible         : bool,
 *     do_show_caption_type_option: bool,
 *     do_show_effect_type_option : bool,
 *     do_show_shuffle_option     : bool,
 * } $args    Array of arguments.
 * @param int    $post_id Post ID.
 * @return array{
 *     array{
 *         type?        : string,
 *         media?       : string,
 *         title?       : string,
 *         filename?    : string,
 *         img_tag?     : string,
 *         media_sub?   : string,
 *         title_sub?   : string,
 *         filename_sub?: string,
 *         img_tag_sub? : string,
 *         video?       : string,
 *         caption?     : string,
 *         url?         : string,
 *         caption_type?: string,
 *     }[],
 *     array{effect_type?: string, do_shuffle?: string}
 * } Items and options.
 */
function _get_data( bool $is_show, array $args, int $post_id ): array {
	$sub_keys = array_merge( array( 'media', 'type' ), $is_show ? array( 'caption', 'caption_type', 'url', 'post_id' ) : array() );
	if ( $args['dual'] ) {
		$sub_keys[] = 'media_sub';
	}
	$post = get_post( $post_id );
	if ( ! ( $post instanceof \WP_Post ) ) {
		return array(
			array(),
			array(),
		);
	}
	$its = \wplug\get_multiple_post_meta( $post->ID, $args['key'], $sub_keys, 'options' );

	$opts = $its['options'] ?? array();
	unset( $its['options'] );

	foreach ( $its as &$it ) {
		if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {
			$permalink = get_permalink( (int) $it['url'] );
			if ( false !== $permalink ) {
				$it['post_id'] = $it['url'];
				$it['url']     = $permalink;
			}
		}
		if ( empty( $it['type'] ) ) {
			$it['type'] = 'image';
		}
		if ( 'image' === $it['type'] ) {
			if ( is_string( $it['media'] ) && ! empty( $it['media'] ) ) {
				_get_images( $it, $it['media'], $args['view_size'] );
			}
			if ( $args['dual'] && is_string( $it['media_sub'] ) && ! empty( $it['media_sub'] ) ) {
				_get_images( $it, $it['media_sub'], $args['view_size'], '_sub' );
			}
		} elseif ( 'video' === $it['type'] ) {
			if ( is_string( $it['media'] ) && ! empty( $it['media'] ) && is_numeric( $it['media'] ) ) {
				$it['video'] = wp_get_attachment_url( (int) $it['media'] );
				$it          = array_merge( $it, _get_image_meta( $it['media'] ) );
			}
		}
	}
	if ( ! is_admin() && ( $opts['do_shuffle'] ?? $args['do_shuffle'] ) ) {
		shuffle( $its );
	}
	return array( $its, $opts );  // @phpstan-ignore-line
}

/**
 * Gets the images of the item.
 *
 * @access private
 *
 * @param array<string, string> $it        The item.
 * @param string                $aid       Attachment ID.
 * @param string                $view_size The width of view.
 * @param string                $pf        Prefix of key.
 */
function _get_images( array &$it, string $aid, string $view_size, string $pf = '' ): void {
	if ( ! is_numeric( $aid ) ) {
		return;
	}
	$tag = wp_get_attachment_image( (int) $aid, 'full', false, array( 'sizes' => "(min-width: $view_size) $view_size, 100vw" ) );

	$it[ "img_tag$pf" ] = "$tag\n";
	$it                 = array_merge( $it, _get_image_meta( $aid, $pf ) );
}

/**
 * Gets image meta data.
 *
 * @access private
 *
 * @param string $aid Attachment ID.
 * @param string $pf  Prefix of key.
 * @return array<string, string> An array of image meta.
 */
function _get_image_meta( string $aid, string $pf = '' ): array {
	if ( ! is_numeric( $aid ) ) {
		return array();
	}
	$p = get_post( (int) $aid );
	if ( ! ( $p instanceof \WP_Post ) ) {
		return array();
	}
	$t  = $p->post_title;
	$fn = basename( $p->guid );
	return array(
		"title$pf"    => $t,
		"filename$pf" => $fn,
	);
}
