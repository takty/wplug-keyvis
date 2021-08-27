<?php
/**
 * Template Admin
 *
 * @package Wplug Keyvis
 * @author Takuto Yanagida
 * @version 2021-08-27
 */

namespace wplug\keyvis;

function add_meta_box_template_admin( bool $is_show, array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	$args = _set_default_args( $args );
	\add_meta_box(
		"{$args['key']}_mb",
		$title,
		function ( \WP_Post $post ) use ( $is_show, $args ) {
			\wplug\keyvis\_cb_output_html_template_admin( $is_show, $args, $post );
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
	<div class="wplug-keyvis-admin <?php echo $is_show ? 'show' : 'hero'; ?>" data-key="<?php echo $args['key']; ?>">
		<div class="wplug-keyvis-body">
	<?php
	_output_item_image( $is_show, $args, '', [], 'wplug-keyvis-item-template-img', $args['is_dual'] );
	_output_item_video( $is_show, $args, '', [], 'wplug-keyvis-item-template-video' );
	?>
	<?php if ( 0 === count( $its ) ) : ?>
			<div class="wplug-keyvis-table"></div>
	<?php else : ?>
			<div class="wplug-keyvis-table">
<?php
	foreach ( $its as $idx => $it ) {
		if ( $it['type'] === 'image' ) {
			_output_item_image( $is_show, $args, $args['key'] . "[$idx]", $it, 'wplug-keyvis-item', $args['is_dual'] );
		} else if ( $it['type'] === 'video' ) {
			_output_item_video( $is_show, $args, $args['key'] . "[$idx]", $it, 'wplug-keyvis-item' );
		}
	}
?>
			</div>
	<?php endif; ?>
			<div class="wplug-keyvis-add-row">
				<div>
					<label class="select">
						<?php _e( 'Effect Type', 'wplug_keyvis' ) ?>
						<select name="<?php echo $args['key']; ?>_effect_type">
							<option value="fade"<?php selected( $effect_type, 'fade' ); ?>><?php _e( 'Fade', 'wplug_keyvis' ) ?></option>
							<option value="slide"<?php selected( $effect_type, 'slide' ); ?>><?php _e( 'Slide', 'wplug_keyvis' ) ?></option>
							<option value="scroll"<?php selected( $effect_type, 'scroll' ); ?>><?php _e( 'Scroll', 'wplug_keyvis' ) ?></option>
						</select>
					</label>
					<label><input type="checkbox" value="1" name="<?php echo $args['key']; ?>_is_shuffled"<?php echo $is_shuffled ? 'checked' : ''; ?>><?php _e( 'Shuffled', 'wplug_keyvis' ) ?></label>
				</div>
				<div>
	<?php if ( $args['is_video_enabled'] ) : ?>
					<button type="button" class="wplug-keyvis-add-video button"><?php _e( 'Add Video', 'wplug_keyvis' ) ?></button>
	<?php endif; ?>
					<button type="button" class="wplug-keyvis-add-img button"><?php _e( 'Add Images', 'wplug_keyvis' ) ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
}

function _output_item_image( bool $is_show, array $args, string $key, array $it, string $cls, bool $is_dual ) {
	_output_row_common( $is_show, $args, $key, $it, $cls );
	if ( $is_dual ) {
		echo '<div class="wplug-keyvis-thumbnail-wrap">';
		_output_row_tn( $key, $it, '' );
		_output_row_tn( $key, $it, '_sub' );
		echo '</div>';
	} else {
		_output_row_tn( $key, $it, '' );
	}
?>
		</div>
		<input type="hidden" name="<?php echo $key; ?>[type]"  class="wplug-keyvis-type"  value="<?php echo empty( $it ) ? 'template' : 'image'; ?>">
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
				<div class="wplug-keyvis-thumbnail">
					<a href="javascript:void(0);" class="frame wplug-keyvis-select-media" title="<?php echo "$_title&#x0A;$_fn" ?>">
						<?php echo $img; ?>
						<div class="wplug-keyvis-thumbnail-media"></div>
					</a>
					<div class="wplug-keyvis-thumbnail-label">
						<div class="wplug-keyvis-title"><?php echo $_title ?></div>
						<div class="wplug-keyvis-filename"><?php echo $_fn ?></div>
					</div>
					<input type="hidden" name="<?php echo $key; ?>[media<?php echo $name_pf; ?>]" class="wplug-keyvis-media" value="<?php echo $_media ?>">
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
			<div class="wplug-keyvis-thumbnail">
				<a href="javascript:void(0);" class="frame wplug-keyvis-select-media" title="<?php echo "$_title&#x0A;$_fn" ?>">
					<video class="wplug-keyvis-thumbnail-media" src="<?php echo $_video ?>">
				</a>
				<div class="wplug-keyvis-thumbnail-label">
					<div class="wplug-keyvis-title"><?php echo $_title ?></div>
					<div class="wplug-keyvis-filename"><?php echo $_fn ?></div>
				</div>
				<input type="hidden" name="<?php echo $key; ?>[media]" class="wplug-keyvis-media" value="<?php echo $_media ?>">
			</div>
		</div>
		<input type="hidden" name="<?php echo $key; ?>[type]"  class="wplug-keyvis-type"  value="<?php echo empty( $it ) ? 'template' : 'video'; ?>">
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
			<div class="wplug-keyvis-handle">=</div>
			<label class="widget-control-remove wplug-keyvis-delete-label"><?php _e( 'Remove', 'wplug_keyvis' ) ?><br>
			<input type="checkbox" name="<?php echo $key; ?>[delete]" class="wplug-keyvis-delete" value="1"></label>
		</div>
		<div>
<?php if ( $is_show ) : ?>
			<div class="wplug-keyvis-info">
				<div><?php esc_html_e( 'Caption', 'wplug_keyvis' ) ?>:</div>
				<div>
					<input type="text" name="<?php echo $key; ?>[caption]" class="wplug-keyvis-caption" value="<?php echo $_cap ?>">
					<select name="<?php echo $key; ?>[caption_type]">
						<option value="line"<?php selected( $cap_type, 'line' ); ?>><?php _e( 'Line', 'wplug_keyvis' ) ?></option>
						<option value="circle"<?php selected( $cap_type, 'circle' ); ?>><?php _e( 'Circle', 'wplug_keyvis' ) ?></option>
						<option value="subtitle"<?php selected( $cap_type, 'subtitle' ); ?>><?php _e( 'Subtitle', 'wplug_keyvis' ) ?></option>
					</select>
				</div>
				<div><a href="javascript:void(0);" class="wplug-keyvis-url-opener">URL</a>:</div>
				<div>
					<input type="text" name="<?php echo $key; ?>[url]" class="wplug-keyvis-url" value="<?php echo $_url ?>">
					<button type="button" class="button wplug-keyvis-select-url"><?php _e( 'Select', 'wplug_keyvis' ) ?></button>
				</div>
			</div>
<?php endif; ?>
<?php
}
