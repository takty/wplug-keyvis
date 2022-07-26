<?php
/**
 * Template Admin
 *
 * @package Wplug Keyvis
 * @author Takuto Yanagida
 * @version 2022-06-04
 */

namespace wplug\keyvis;

/**
 * Adds the meta box to template admin screen.
 *
 * @param bool        $is_show  Whether this slider is 'show'.
 * @param array       $args     Array of arguments.
 * @param string      $title    Title of the meta box.
 * @param string|null $screen   (Optional) The screen or screens on which to show the box.
 * @param string      $context  (Optional) The context within the screen where the box should display.
 * @param string      $priority (Optional) The priority within the context where the box should show.
 */
function add_meta_box_template_admin( bool $is_show, array $args, string $title, ?string $screen = null, string $context = 'advanced', string $priority = 'default' ) {
	$args = _set_default_args( $args );
	\add_meta_box(
		"{$args['key']}_mb",
		$title,
		function ( \WP_Post $post ) use ( $is_show, $args ) {
			\wplug\keyvis\_cb_output_html_template_admin( $is_show, $args, $post );
		},
		$screen,
		$context,
		$priority
	);
}

/**
 * Stores the data of the meta box on template admin screen.
 *
 * @param bool  $is_show Whether this slider is 'show'.
 * @param array $args    Array of arguments.
 * @param int   $post_id Post ID.
 */
function save_meta_box_template_admin( bool $is_show, array $args, int $post_id ) {
	$args = _set_default_args( $args );
	if ( ! isset( $_POST[ "{$args['key']}_nonce" ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST[ "{$args['key']}_nonce" ] ), $args['key'] ) ) {
		return;
	}
	_save_data( $is_show, $args, $post_id );
}


// -----------------------------------------------------------------------------


/**
 * Callback function for 'add_meta_box'.
 *
 * @access private
 *
 * @param bool     $is_show Whether this slider is 'show'.
 * @param array    $args    Array of arguments.
 * @param \WP_Post $post    Current post.
 */
function _cb_output_html_template_admin( bool $is_show, array $args, \WP_Post $post ) {
	wp_nonce_field( $args['key'], "{$args['key']}_nonce" );
	list( $its, $opts ) = _get_data( $is_show, $args, $post->ID );

	$do_shuffle  = $opts['do_shuffle'] ?? $args['do_shuffle'];
	$effect_type = $opts['effect_type'] ?? $args['effect_type'];

	$key = $args['key'];
	?>
	<div class="wplug-keyvis-admin <?php echo esc_attr( $is_show ? 'show' : 'hero' ); ?>" data-key="<?php echo esc_attr( $args['key'] ); ?>">
		<div class="wplug-keyvis-body">
	<?php
	_output_item_image( $is_show, $args, '', array(), 'wplug-keyvis-item-template-img' );
	_output_item_video( $is_show, $args, '', array(), 'wplug-keyvis-item-template-video' );
	?>
	<?php if ( 0 === count( $its ) ) : ?>
			<div class="wplug-keyvis-table"></div>
	<?php else : ?>
			<div class="wplug-keyvis-table">
		<?php
		foreach ( $its as $idx => $it ) {
			if ( 'image' === $it['type'] ) {
				_output_item_image( $is_show, $args, $args['key'] . "[$idx]", $it, 'wplug-keyvis-item' );
			} elseif ( 'video' === $it['type'] ) {
				_output_item_video( $is_show, $args, $args['key'] . "[$idx]", $it, 'wplug-keyvis-item' );
			}
		}
		?>
			</div>
	<?php endif; ?>
			<div class="wplug-keyvis-add-row">
				<div>
		<?php if ( $args['do_show_effect_type_option'] ) : ?>
					<label class="select">
						<?php esc_html_e( 'Effect Type', 'wplug_keyvis' ); ?>
						<select name="<?php echo esc_attr( $key ); ?>_effect_type">
							<option value="fade"<?php selected( $effect_type, 'fade' ); ?>><?php esc_html_e( 'Fade', 'wplug_keyvis' ); ?></option>
							<option value="slide"<?php selected( $effect_type, 'slide' ); ?>><?php esc_html_e( 'Slide', 'wplug_keyvis' ); ?></option>
							<option value="scroll"<?php selected( $effect_type, 'scroll' ); ?>><?php esc_html_e( 'Scroll', 'wplug_keyvis' ); ?></option>
						</select>
					</label>
		<?php endif; ?>
		<?php if ( $args['do_show_shuffle_option'] ) : ?>
					<label><input type="checkbox" value="1" name="<?php echo esc_attr( $key ); ?>_do_shuffle"<?php echo $do_shuffle ? 'checked' : ''; ?>><?php esc_html_e( 'Shuffled', 'wplug_keyvis' ); ?></label>
		<?php endif; ?>
				</div>
				<div>
	<?php if ( $args['do_enable_video'] ) : ?>
					<button type="button" class="wplug-keyvis-add-video button"><?php esc_html_e( 'Add Video', 'wplug_keyvis' ); ?></button>
	<?php endif; ?>
					<button type="button" class="wplug-keyvis-add-img button"><?php esc_html_e( 'Add Images', 'wplug_keyvis' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Outputs the row of an image item.
 *
 * @access private
 *
 * @param bool   $is_show Whether this slider is 'show'.
 * @param array  $args    Array of arguments.
 * @param string $key     The base key of input.
 * @param array  $it      The item.
 * @param string $cls     CSS class name.
 */
function _output_item_image( bool $is_show, array $args, string $key, array $it, string $cls ) {
	_output_row_common( $is_show, $args, $key, $it, $cls );
	if ( $args['dual'] ) {
		echo '<div class="wplug-keyvis-thumbnail-wrap">';
		_output_row_tn( $key, $it, '' );
		_output_row_tn( $key, $it, '_sub' );
		echo '</div>';
	} else {
		_output_row_tn( $key, $it, '' );
	}
	?>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $key ); ?>[type]"  class="wplug-keyvis-type"  value="<?php echo empty( $it ) ? 'template' : 'image'; ?>">
	</div>
	<?php
}

/**
 * Outputs the thumbnail row of image items.
 *
 * @access private
 *
 * @param string $key     The base key of input.
 * @param array  $it      The item.
 * @param string $name_pf Input name postfix.
 */
function _output_row_tn( string $key, array $it, string $name_pf ) {
	$media = esc_attr( $it[ "media$name_pf" ] ?? '' );
	$title = $it[ "title$name_pf" ] ?? '';
	$fn    = $it[ "filename$name_pf" ] ?? '';
	$img   = $it[ "img_tag$name_pf" ] ?? '';

	if ( ! empty( $title ) && strlen( $title ) < strlen( $fn ) && strpos( $fn, $title ) === 0 ) {
		$title = '';
	}
	?>
				<div class="wplug-keyvis-thumbnail">
					<a href="javascript:void(0);" class="frame wplug-keyvis-select-media" title="<?php echo esc_attr( "$title&#x0A;$fn" ); ?>">
						<?php echo $img; // phpcs:ignore ?>
						<div class="wplug-keyvis-thumbnail-media"></div>
					</a>
					<div class="wplug-keyvis-thumbnail-label">
						<div class="wplug-keyvis-title"><?php echo esc_html( $title ); ?></div>
						<div class="wplug-keyvis-filename"><?php echo esc_html( $fn ); ?></div>
					</div>
					<input type="hidden" name="<?php echo esc_attr( $key ); ?>[media<?php echo esc_attr( $name_pf ); ?>]" class="wplug-keyvis-media" value="<?php echo esc_attr( $media ); ?>">
				</div>
	<?php
}

/**
 * Outputs the row of a video item.
 *
 * @access private
 *
 * @param bool   $is_show Whether this slider is 'show'.
 * @param array  $args    Array of arguments.
 * @param string $key     The base key of input.
 * @param array  $it      The item.
 * @param string $cls     CSS class name.
 */
function _output_item_video( bool $is_show, array $args, string $key, array $it, string $cls ) {
	$media = $it['media'] ?? '';
	$title = $it['title'] ?? '';
	$fn    = $it['filename'] ?? '';
	$video = $it['video'] ?? '';

	if ( ! empty( $title ) && strlen( $title ) < strlen( $fn ) && strpos( $fn, $title ) === 0 ) {
		$title = '';
	}
	_output_row_common( $is_show, $args, $key, $it, $cls );
	?>
			<div class="wplug-keyvis-thumbnail">
				<a href="javascript:void(0);" class="frame wplug-keyvis-select-media" title="<?php echo esc_attr( "$title&#x0A;$fn" ); ?>">
					<video class="wplug-keyvis-thumbnail-media" src="<?php echo esc_url( $video ); ?>">
				</a>
				<div class="wplug-keyvis-thumbnail-label">
					<div class="wplug-keyvis-title"><?php echo esc_attr( $title ); ?></div>
					<div class="wplug-keyvis-filename"><?php echo esc_attr( $fn ); ?></div>
				</div>
				<input type="hidden" name="<?php echo esc_attr( $key ); ?>[media]" class="wplug-keyvis-media" value="<?php echo esc_attr( $media ); ?>">
			</div>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $key ); ?>[type]"  class="wplug-keyvis-type"  value="<?php echo empty( $it ) ? 'template' : 'video'; ?>">
	</div>
	<?php
}

/**
 * Outputs the common row of items.
 *
 * @access private
 *
 * @param bool   $is_show Whether this slider is 'show'.
 * @param array  $args    Array of arguments.
 * @param string $key     The base key of input.
 * @param array  $it      The item.
 * @param string $cls     CSS class name.
 */
function _output_row_common( bool $is_show, array $args, string $key, array $it, string $cls ) {
	$cap = $it['caption'] ?? '';
	$url = $it['url'] ?? '';

	$cap_type = $it['caption_type'] ?? $args['caption_type'];
	?>
	<div class="<?php echo esc_attr( $cls ); ?>">
		<div>
			<div class="wplug-keyvis-handle">=</div>
			<label class="widget-control-remove wplug-keyvis-delete-label"><?php esc_html_e( 'Remove', 'wplug_keyvis' ); ?><br>
			<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[delete]" class="wplug-keyvis-delete" value="1"></label>
		</div>
		<div>
	<?php if ( $is_show ) : ?>
			<div class="wplug-keyvis-info">
				<div><?php esc_html_e( 'Caption', 'wplug_keyvis' ); ?>:</div>
				<div>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[caption]" class="wplug-keyvis-caption" value="<?php echo esc_attr( $cap ); ?>">
		<?php if ( $args['do_show_caption_type_option'] ) : ?>
					<select name="<?php echo esc_attr( $key ); ?>[caption_type]">
						<option value="line"<?php selected( $cap_type, 'line' ); ?>><?php esc_html_e( 'Line', 'wplug_keyvis' ); ?></option>
						<option value="circle"<?php selected( $cap_type, 'circle' ); ?>><?php esc_html_e( 'Circle', 'wplug_keyvis' ); ?></option>
						<option value="subtitle"<?php selected( $cap_type, 'subtitle' ); ?>><?php esc_html_e( 'Subtitle', 'wplug_keyvis' ); ?></option>
					</select>
		<?php endif; ?>
				</div>
				<div><a href="javascript:void(0);" class="wplug-keyvis-url-opener">URL</a>:</div>
				<div>
					<input type="text" name="<?php echo esc_attr( $key ); ?>[url]" class="wplug-keyvis-url" value="<?php echo esc_attr( $url ); ?>">
					<button type="button" class="button wplug-keyvis-select-url"><?php esc_html_e( 'Select', 'wplug_keyvis' ); ?></button>
				</div>
			</div>
	<?php endif; ?>
	<?php
}
