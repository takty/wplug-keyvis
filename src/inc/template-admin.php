<?php
/**
 * Slider (Template Admin)
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-25
 */

namespace wplug\slider;

function add_meta_box_template_admin( array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	$args = _set_default_args( $args );
	\add_meta_box(
		"{$args['key']}_mb",
		$title,
		function ( \WP_Post $post ) use ( $args ) {
			\wplug\slider\_cb_output_html_template_admin( $args, $post );
		},
		$screen,
		$context
	);
}

function save_meta_box_template_admin( array $args, int $post_id ) {
	$args = _set_default_args( $args );
	if ( ! isset( $_POST["{$args['key']}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$args['key']}_nonce"], $args['key'] ) ) return;
	_save_items( $args, $post_id );
}


// -----------------------------------------------------------------------------


function _cb_output_html_template_admin( array $args, \WP_Post $post ) {
	wp_nonce_field( $args['key'], "{$args['key']}_nonce" );
	$its = _get_items( $args, $post->ID );
?>
	<div class="wplug-slider-show-admin" data-key="<?php echo $args['key']; ?>">
		<div class="wplug-slider-show-body">
			<div class="wplug-slider-show-table">
<?php
	_output_item_image( '', [], 'wplug-slider-show-item-template-img', $args['is_dual'] );
	_output_item_video( '', [], 'wplug-slider-show-item-template-video' );
	foreach ( $its as $idx => $it ) {
		if ( $it['type'] === 'image' ) {
			_output_item_image( $args['key'] . "[$idx]", $it, 'wplug-slider-show-item', $args['is_dual'] );
		} else if ( $it['type'] === 'video' ) {
			_output_item_video( $args['key'] . "[$idx]", $it, 'wplug-slider-show-item' );
		}
	}
?>
				<div class="wplug-slider-show-add-row">
<?php if ( $args['is_video_enabled'] ) : ?>
					<button type="button" class="wplug-slider-show-add-video button"><?php _e( 'Add Video', 'default' ) ?></button>
<?php endif; ?>
					<button type="button" class="wplug-slider-show-add-img button"><?php _e( 'Add Media', 'default' ) ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
}

function _output_item_image( string $key, array $it, string $cls, bool $is_dual ) {
	_output_row_common( $key, $it, $cls );
	if ( $is_dual ) {
		echo '<div class="wplug-slider-show-thumbnail-wrap">';
		_output_row_tn( $key, $it, '' );
		_output_row_tn( $key, $it, '_sub' );
		echo '</div>';
	} else {
		_output_row_tn( $key, $it, '' );
	}
?>
		</div>
		<input type="hidden" name="<?php echo $key; ?>[type]"  class="wplug-slider-show-type"  value="<?php echo empty( $it ) ? 'template' : 'image'; ?>">
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
				<div class="wplug-slider-show-thumbnail">
					<a href="javascript:void(0);" class="frame wplug-slider-show-select-media" title="<?php echo "$_title&#x0A;$_fn" ?>">
						<?php echo $img; ?>
						<div class="wplug-slider-show-thumbnail-media"></div>
					</a>
					<div class="wplug-slider-show-thumbnail-name">
						<div class="wplug-slider-show-title"><?php echo $_title ?></div>
						<div class="wplug-slider-show-filename"><?php echo $_fn ?></div>
					</div>
					<input type="hidden" name="<?php echo $key; ?>[media<?php echo $name_pf; ?>]" class="wplug-slider-show-media" value="<?php echo $_media ?>">
				</div>
<?php
}

function _output_item_video( string $key, array $it, string $cls ) {
	$_media = esc_attr( $it['media']    ?? '' );
	$_title = esc_attr( $it['title']    ?? '' );
	$_fn    = esc_attr( $it['filename'] ?? '' );
	$_video = esc_url( $it['video']     ?? '' );

	if ( ! empty( $_title ) && strlen( $_title ) < strlen( $_fn ) && strpos( $_fn, $_title ) === 0 ) $_title = '';
	_output_row_common( $key, $it, $cls );
?>
			<div class="wplug-slider-show-thumbnail">
				<a href="javascript:void(0);" class="frame wplug-slider-show-select-media" title="<?php echo "$_title&#x0A;$_fn" ?>">
					<video class="wplug-slider-show-thumbnail-media" src="<?php echo $_video ?>">
				</a>
				<div class="wplug-slider-show-thumbnail-name">
					<div class="wplug-slider-show-title"><?php echo $_title ?></div>
					<div class="wplug-slider-show-filename"><?php echo $_fn ?></div>
				</div>
				<input type="hidden" name="<?php echo $key; ?>[media]" class="wplug-slider-show-media" value="<?php echo $_media ?>">
			</div>
		</div>
		<input type="hidden" name="<?php echo $key; ?>[type]"  class="wplug-slider-show-type"  value="<?php echo empty( $it ) ? 'template' : 'video'; ?>">
	</div>
<?php
}

function _output_row_common( string $key, array $it, string $cls ) {
	$_cap = esc_attr( $it['caption'] ?? '' );
	$_url = esc_attr( $it['url']     ?? '' );
?>
	<div class="<?php echo $cls ?>">
		<div>
			<div class="wplug-slider-show-handle">=</div>
			<label class="widget-control-remove wplug-slider-show-delete-label"><?php _e( 'Remove', 'default' ) ?><br>
			<input type="checkbox" name="<?php echo $key; ?>[delete]" class="wplug-slider-show-delete" value="1"></label>
		</div>
		<div>
			<div class="wplug-slider-show-info">
				<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
				<div><input type="text" name="<?php echo $key; ?>[caption]" class="wplug-slider-show-caption" value="<?php echo $_cap ?>"></div>
				<div><a href="javascript:void(0);" class="wplug-slider-show-url-opener">URL</a>:</div>
				<div>
					<input type="text" name="<?php echo $key; ?>[url]" class="wplug-slider-show-url" value="<?php echo $_url ?>">
					<button type="button" class="button wplug-slider-show-select-url"><?php _e( 'Select', 'default' ) ?></button>
				</div>
			</div>
<?php
}
