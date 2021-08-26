<?php
/**
 * Slider (Template Admin)
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-26
 */

namespace wplug\slider;

function add_meta_box_template_admin( bool $is_show, array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	$args = _set_default_args( $args );
	\add_meta_box(
		"{$args['key']}_mb",
		$title,
		function ( \WP_Post $post ) use ( $is_show, $args ) {
			\wplug\slider\_cb_output_html_template_admin( $is_show, $args, $post );
		},
		$screen,
		$context
	);
}

function save_meta_box_template_admin( bool $is_show, array $args, int $post_id ) {
	$args = _set_default_args( $args );
	if ( ! isset( $_POST["{$args['key']}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$args['key']}_nonce"], $args['key'] ) ) return;
	_save_data( $is_show, $args, $post_id );
}


// -----------------------------------------------------------------------------


function _cb_output_html_template_admin( bool $is_show, array $args, \WP_Post $post ) {
	wp_nonce_field( $args['key'], "{$args['key']}_nonce" );
	[ $its, $opts ] = _get_data( $is_show, $args, $post->ID );

	$is_shuffled = $opts['is_shuffled'] ?? $args['is_shuffled'];
	$effect_type = $opts['effect_type'] ?? $args['effect_type'];

	?>
	<div class="wplug-slider-admin <?php echo $is_show ? 'show' : 'hero'; ?>" data-key="<?php echo $args['key']; ?>">
		<div class="wplug-slider-body">
	<?php
	_output_item_image( $is_show, $args, '', [], 'wplug-slider-item-template-img', $args['is_dual'] );
	_output_item_video( $is_show, $args, '', [], 'wplug-slider-item-template-video' );
	?>
	<?php if ( 0 === count( $its ) ) : ?>
			<div class="wplug-slider-table"></div>
	<?php else : ?>
			<div class="wplug-slider-table">
<?php
	foreach ( $its as $idx => $it ) {
		if ( $it['type'] === 'image' ) {
			_output_item_image( $is_show, $args, $args['key'] . "[$idx]", $it, 'wplug-slider-item', $args['is_dual'] );
		} else if ( $it['type'] === 'video' ) {
			_output_item_video( $is_show, $args, $args['key'] . "[$idx]", $it, 'wplug-slider-item' );
		}
	}
?>
			</div>
	<?php endif; ?>
			<div class="wplug-slider-add-row">
				<div>
					<label class="select">
						<?php _e( 'Effect Type', 'wplug_slider' ) ?>
						<select name="<?php echo $args['key']; ?>_effect_type">
							<option value="fade"<?php selected( $effect_type, 'fade' ); ?>><?php _e( 'Fade', 'wplug_slider' ) ?></option>
							<option value="slide"<?php selected( $effect_type, 'slide' ); ?>><?php _e( 'Slide', 'wplug_slider' ) ?></option>
							<option value="scroll"<?php selected( $effect_type, 'scroll' ); ?>><?php _e( 'Scroll', 'wplug_slider' ) ?></option>
						</select>
					</label>
					<label><input type="checkbox" value="1" name="<?php echo $args['key']; ?>_is_shuffled"<?php echo $is_shuffled ? 'checked' : ''; ?>><?php _e( 'Shuffled', 'wplug_slider' ) ?></label>
				</div>
				<div>
	<?php if ( $args['is_video_enabled'] ) : ?>
					<button type="button" class="wplug-slider-add-video button"><?php _e( 'Add Video', 'wplug_slider' ) ?></button>
	<?php endif; ?>
					<button type="button" class="wplug-slider-add-img button"><?php _e( 'Add Images', 'wplug_slider' ) ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
}

function _output_item_image( bool $is_show, array $args, string $key, array $it, string $cls, bool $is_dual ) {
	_output_row_common( $is_show, $args, $key, $it, $cls );
	if ( $is_dual ) {
		echo '<div class="wplug-slider-thumbnail-wrap">';
		_output_row_tn( $key, $it, '' );
		_output_row_tn( $key, $it, '_sub' );
		echo '</div>';
	} else {
		_output_row_tn( $key, $it, '' );
	}
?>
		</div>
		<input type="hidden" name="<?php echo $key; ?>[type]"  class="wplug-slider-type"  value="<?php echo empty( $it ) ? 'template' : 'image'; ?>">
	</div>
<?php
}

function _output_row_tn( string $key, array $it, string $name_pf ) {
	$_media = esc_attr( $it["media$name_pf"]    ?? '' );
	$_title = esc_attr( $it["title$name_pf"]    ?? '' );
	$_fn    = esc_attr( $it["filename$name_pf"] ?? '' );
	$img    = $it["img_tag$name_pf"] ?? '';

	if ( ! empty( $_title ) && strlen( $_title ) < strlen( $_fn ) && strpos( $_fn, $_title ) === 0 ) $_title = '';
?>
				<div class="wplug-slider-thumbnail">
					<a href="javascript:void(0);" class="frame wplug-slider-select-media" title="<?php echo "$_title&#x0A;$_fn" ?>">
						<?php echo $img; ?>
						<div class="wplug-slider-thumbnail-media"></div>
					</a>
					<div class="wplug-slider-thumbnail-label">
						<div class="wplug-slider-title"><?php echo $_title ?></div>
						<div class="wplug-slider-filename"><?php echo $_fn ?></div>
					</div>
					<input type="hidden" name="<?php echo $key; ?>[media<?php echo $name_pf; ?>]" class="wplug-slider-media" value="<?php echo $_media ?>">
				</div>
<?php
}

function _output_item_video( bool $is_show, array $args, string $key, array $it, string $cls ) {
	$_media = esc_attr( $it['media']    ?? '' );
	$_title = esc_attr( $it['title']    ?? '' );
	$_fn    = esc_attr( $it['filename'] ?? '' );
	$_video = esc_url( $it['video']     ?? '' );

	if ( ! empty( $_title ) && strlen( $_title ) < strlen( $_fn ) && strpos( $_fn, $_title ) === 0 ) $_title = '';
	_output_row_common( $is_show, $args, $key, $it, $cls );
?>
			<div class="wplug-slider-thumbnail">
				<a href="javascript:void(0);" class="frame wplug-slider-select-media" title="<?php echo "$_title&#x0A;$_fn" ?>">
					<video class="wplug-slider-thumbnail-media" src="<?php echo $_video ?>">
				</a>
				<div class="wplug-slider-thumbnail-label">
					<div class="wplug-slider-title"><?php echo $_title ?></div>
					<div class="wplug-slider-filename"><?php echo $_fn ?></div>
				</div>
				<input type="hidden" name="<?php echo $key; ?>[media]" class="wplug-slider-media" value="<?php echo $_media ?>">
			</div>
		</div>
		<input type="hidden" name="<?php echo $key; ?>[type]"  class="wplug-slider-type"  value="<?php echo empty( $it ) ? 'template' : 'video'; ?>">
	</div>
<?php
}

function _output_row_common( bool $is_show, array $args, string $key, array $it, string $cls ) {
	$_cap = esc_attr( $it['caption'] ?? '' );
	$_url = esc_attr( $it['url']     ?? '' );

	$cap_type = $it['caption_type'] ?? $args['caption_type'];
?>
	<div class="<?php echo $cls ?>">
		<div>
			<div class="wplug-slider-handle">=</div>
			<label class="widget-control-remove wplug-slider-delete-label"><?php _e( 'Remove', 'wplug_slider' ) ?><br>
			<input type="checkbox" name="<?php echo $key; ?>[delete]" class="wplug-slider-delete" value="1"></label>
		</div>
		<div>
<?php if ( $is_show ) : ?>
			<div class="wplug-slider-info">
				<div><?php esc_html_e( 'Caption', 'wplug_slider' ) ?>:</div>
				<div>
					<input type="text" name="<?php echo $key; ?>[caption]" class="wplug-slider-caption" value="<?php echo $_cap ?>">
					<select name="<?php echo $key; ?>[caption_type]">
						<option value="line"<?php selected( $cap_type, 'line' ); ?>><?php _e( 'Line', 'wplug_slider' ) ?></option>
						<option value="circle"<?php selected( $cap_type, 'circle' ); ?>><?php _e( 'Circle', 'wplug_slider' ) ?></option>
						<option value="subtitle"<?php selected( $cap_type, 'subtitle' ); ?>><?php _e( 'Subtitle', 'wplug_slider' ) ?></option>
					</select>
				</div>
				<div><a href="javascript:void(0);" class="wplug-slider-url-opener">URL</a>:</div>
				<div>
					<input type="text" name="<?php echo $key; ?>[url]" class="wplug-slider-url" value="<?php echo $_url ?>">
					<button type="button" class="button wplug-slider-select-url"><?php _e( 'Select', 'wplug_slider' ) ?></button>
				</div>
			</div>
<?php endif; ?>
<?php
}
