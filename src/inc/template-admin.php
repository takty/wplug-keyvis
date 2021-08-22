<?php
/**
 * Slider (Template Admin)
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-02
 */

namespace wplug\slider;

function add_meta_box_template_admin( string $label, string $screen, string $context = 'advanced' ) {
	$inst = _get_instance();
	\add_meta_box( "{$inst->_key}_mb", $label, '\wplug\slider\_cb_output_html_template_admin', $screen, $context );
}

function save_meta_box_template_admin( int $post_id ) {
	$inst = _get_instance();
	if ( ! isset( $_POST["{$inst->_key}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$inst->_key}_nonce"], $inst->_key ) ) return;
	_save_items( $post_id );
}


// -----------------------------------------------------------------------------


function _cb_output_html_template_admin( \WP_Post $post ) {
	$inst = _get_instance();
	wp_nonce_field( $inst->_key, "{$inst->_key}_nonce" );
	$its = _get_items( $post->ID );
?>
	<input type="hidden" <?php name_id( $inst->_id ) ?> value="">
	<div class="<?php echo $inst::CLS_BODY ?>">
		<div class="<?php echo $inst::CLS_TABLE ?>">
<?php
	_output_row_image( [], $inst::CLS_ITEM_TEMP_IMG );
	_output_row_video( [], $inst::CLS_ITEM_TEMP_VIDEO );
	foreach ( $its as $it ) {
		if ( $it['type'] === $inst::TYPE_IMAGE ) _output_row_image( $it, $inst::CLS_ITEM );
		else if ( $it['type'] === $inst::TYPE_VIDEO ) _output_row_video( $it, $inst::CLS_ITEM );
	}
?>
			<div class="<?php echo $inst::CLS_ADD_ROW ?>">
<?php
	if ( $inst->_is_video_enabled ) {
?>
				<a href="javascript:void(0);" class="<?php echo $inst::CLS_ADD_VIDEO ?> button"><?php _e( 'Add Video', 'default' ) ?></a>
<?php
	}
?>
				<a href="javascript:void(0);" class="<?php echo $inst::CLS_ADD_IMG ?> button"><?php _e( 'Add Media', 'default' ) ?></a>
			</div>
		</div>
		<script>window.addEventListener('load', function () {
			st_slide_show_initialize_admin('<?php echo $inst->_id ?>', <?php echo $inst->_is_dual ? 'true' : 'false' ?>);
		});</script>
	</div>
<?php
}

function _output_row_image( array $it, string $cls ) {
	$inst = _get_instance();
	if ( $inst->_is_dual ) {
		_output_row_dual( $it, $cls );
	} else {
		_output_row_single( $it, $cls );
	}
}

function _output_row_single( $it, $cls ) {
	$_cap   = isset( $it['caption'] ) ? esc_attr( $it['caption'] ) : '';
	$_url   = isset( $it['url'] )     ? esc_attr( $it['url'] )     : '';
	$_img   = isset( $it['image'] )   ? esc_url( $it['image'] )    : '';
	$_media = isset( $it['media'] )   ? esc_attr( $it['media'] )   : '';
	$_style = empty( $_img ) ? '' : " style=\"background-image:url($_img)\"";

	$_title = isset( $it['title'] )    ? esc_attr( $it['title'] )    : '';
	$_fn    = isset( $it['filename'] ) ? esc_attr( $it['filename'] ) : '';

	if ( ! empty( $_title ) && strlen( $_title ) < strlen( $_fn ) && strpos( $_fn, $_title ) === 0 ) $_title = '';
?>
	<div class="<?php echo $cls ?>">
		<div>
			<div class="<?php echo $inst::CLS_HANDLE ?>">=</div>
			<label class="widget-control-remove <?php echo $inst::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo $inst::CLS_DEL ?>"></label>
		</div>
		<div>
			<div class="<?php echo $inst::CLS_INFO ?>">
				<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
				<div><input type="text" class="<?php echo $inst::CLS_CAP ?>" value="<?php echo $_cap ?>"></div>
				<div><a href="javascript:void(0);" class="<?php echo $inst::CLS_URL_OPENER ?>">URL</a>:</div>
				<div>
					<input type="text" class="<?php echo $inst::CLS_URL ?>" value="<?php echo $_url ?>">
					<a href="javascript:void(0);" class="button <?php echo $inst::CLS_SEL_URL ?>"><?php _e( 'Select', 'default' ) ?></a>
				</div>
			</div>
			<div class="<?php echo $inst::CLS_TN ?>">
				<a href="javascript:void(0);" class="frame <?php echo $inst::CLS_SEL_IMG ?>" title="<?php echo "$_title&#x0A;$_fn" ?>">
					<div class="<?php echo $inst::CLS_TN_IMG ?>"<?php echo $_style ?>></div>
				</a>
				<div class="<?php echo $inst::CLS_TN_NAME ?>">
					<div class="<?php echo $inst::CLS_TITLE ?>"><?php echo $_title ?></div>
					<div class="<?php echo $inst::CLS_FILENAME ?>"><?php echo $_fn ?></div>
				</div>
			</div>
		</div>
		<input type="hidden" class="<?php echo $inst::CLS_MEDIA ?>" value="<?php echo $_media ?>">
		<input type="hidden" class="<?php echo $inst::CLS_TYPE ?>" value="image">
	</div>
<?php
}

function _output_row_dual( array $it, string $cls ) {
	$_cap     = isset( $it['caption'] )   ? esc_attr( $it['caption'] )   : '';
	$_url     = isset( $it['url'] )       ? esc_attr( $it['url'] )       : '';
	$_img     = isset( $it['image'] )     ? esc_url( $it['image'] )      : '';
	$_img_s   = isset( $it['image_sub'] ) ? esc_url( $it['image_sub'] )  : '';
	$_media   = isset( $it['media'] )     ? esc_attr( $it['media'] )     : '';
	$_media_s = isset( $it['media_sub'] ) ? esc_attr( $it['media_sub'] ) : '';
	$_style   = empty( $_img )    ? '' : " style=\"background-image:url($_img)\"";
	$_style_s = empty( $_img_s )  ? '' : " style=\"background-image:url($_img_s)\"";

	$_title   = isset( $it['title'] )        ? esc_attr( $it['title'] )        : '';
	$_title_s = isset( $it['title_sub'] )    ? esc_attr( $it['title_sub'] )    : '';
	$_fn      = isset( $it['filename'] )     ? esc_attr( $it['filename'] )     : '';
	$_fn_s    = isset( $it['filename_sub'] ) ? esc_attr( $it['filename_sub'] ) : '';

	if ( ! empty( $_title )   && strlen( $_title )   < strlen( $_fn )   && strpos( $_fn, $_title )     === 0 ) $_title = '';
	if ( ! empty( $_title_s ) && strlen( $_title_s ) < strlen( $_fn_s ) && strpos( $_fn_s, $_title_s ) === 0 ) $_title_s = '';
?>
	<div class="<?php echo $cls ?>">
		<div>
			<div class="<?php echo $inst::CLS_HANDLE ?>">=</div>
			<label class="widget-control-remove <?php echo $inst::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo $inst::CLS_DEL ?>"></label>
		</div>
		<div>
			<div class="<?php echo $inst::CLS_INFO ?>">
				<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
				<div><input type="text" class="<?php echo $inst::CLS_CAP ?>" value="<?php echo $_cap ?>"></div>
				<div><a href="javascript:void(0);" class="<?php echo $inst::CLS_URL_OPENER ?>">URL</a>:</div>
				<div><input type="text" class="<?php echo $inst::CLS_URL ?>" value="<?php echo $_url ?>">
				<a href="javascript:void(0);" class="button <?php echo $inst::CLS_SEL_URL ?>"><?php _e( 'Select', 'default' ) ?></a></div>
			</div>
			<div class="st-slide-show-thumbnail-wrap">
				<div class="<?php echo $inst::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo $inst::CLS_SEL_IMG ?>" title="<?php echo "$_title&#x0A;$_fn" ?>">
						<div class="<?php echo $inst::CLS_TN_IMG ?>"<?php echo $_style ?>></div>
					</a>
					<div class="<?php echo $inst::CLS_TN_NAME ?>">
						<div class="<?php echo $inst::CLS_TITLE ?>"><?php echo $_title ?></div>
						<div class="<?php echo $inst::CLS_FILENAME ?>"><?php echo $_fn ?></div>
					</div>
				</div>
				<div class="<?php echo $inst::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo $inst::CLS_SEL_IMG_SUB ?>" title="<?php echo "$_title_s&#x0A;$_fn_s" ?>">
						<div class="<?php echo $inst::CLS_TN_IMG_SUB ?>"<?php echo $_style_s ?>></div>
					</a>
					<div class="<?php echo $inst::CLS_TN_NAME_SUB ?>">
						<div class="<?php echo $inst::CLS_TITLE_SUB ?>"><?php echo $_title_s ?></div>
						<div class="<?php echo $inst::CLS_FILENAME_SUB ?>"><?php echo $_fn_s ?></div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" class="<?php echo $inst::CLS_MEDIA ?>" value="<?php echo $_media ?>">
		<input type="hidden" class="<?php echo $inst::CLS_MEDIA_SUB ?>" value="<?php echo $_media_s ?>">
		<input type="hidden" class="<?php echo $inst::CLS_TYPE ?>" value="image">
	</div>
<?php
}

function _output_row_video( array $it, string $cls ) {
	$inst = _get_instance();
	$_cap   = isset( $it['caption'] ) ? esc_attr( $it['caption'] ) : '';
	$_url   = isset( $it['url'] )     ? esc_attr( $it['url'] )     : '';
	$_media = isset( $it['media'] )   ? esc_attr( $it['media'] )   : '';
	$_video = isset( $it['video'] )   ? esc_url( $it['video'] )    : '';

	$_title = isset( $it['title'] )    ? esc_attr( $it['title'] )    : '';
	$_fn    = isset( $it['filename'] ) ? esc_attr( $it['filename'] ) : '';

	if ( ! empty( $_title ) && strlen( $_title ) < strlen( $_fn ) && strpos( $_fn, $_title ) === 0 ) $_title = '';
?>
	<div class="<?php echo $cls ?>">
		<div>
			<div class="<?php echo $inst::CLS_HANDLE ?>">=</div>
			<label class="widget-control-remove <?php echo $inst::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo $inst::CLS_DEL ?>"></label>
		</div>
		<div>
			<div class="<?php echo $inst::CLS_INFO ?>">
				<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
				<div><input type="text" class="<?php echo $inst::CLS_CAP ?>" value="<?php echo $_cap ?>"></div>
				<div><a href="javascript:void(0);" class="<?php echo $inst::CLS_URL_OPENER ?>">URL</a>:</div>
				<div>
					<input type="text" class="<?php echo $inst::CLS_URL ?>" value="<?php echo $_url ?>">
					<a href="javascript:void(0);" class="button <?php echo $inst::CLS_SEL_URL ?>"><?php _e( 'Select', 'default' ) ?></a>
				</div>
			</div>
			<div class="<?php echo $inst::CLS_TN ?>">
				<a href="javascript:void(0);" class="frame <?php echo $inst::CLS_SEL_VIDEO ?>" title="<?php echo "$_title&#x0A;$_fn" ?>">
					<video class="<?php echo $inst::CLS_TN_IMG ?>" src="<?php echo $_video ?>">
				</a>
				<div class="<?php echo $inst::CLS_TN_NAME ?>">
					<div class="<?php echo $inst::CLS_TITLE ?>"><?php echo $_title ?></div>
					<div class="<?php echo $inst::CLS_FILENAME ?>"><?php echo $_fn ?></div>
				</div>
			</div>
		</div>
		<input type="hidden" class="<?php echo $inst::CLS_MEDIA ?>" value="<?php echo $_media ?>">
		<input type="hidden" class="<?php echo $inst::CLS_TYPE ?>" value="video">
	</div>
<?php
}


// -----------------------------------------------------------------------------


function _save_items( int $post_id ) {
	$inst = _get_instance();
	$skeys = [ 'media', 'caption', 'url', 'type', 'delete' ];
	if ( $inst->_is_dual ) $skeys[] = 'media_sub';

	$its = get_multiple_post_meta_from_post( $inst->_key, $skeys );
	$its = array_filter( $its, function ( $it ) { return ! $it['delete']; } );
	$its = array_values( $its );

	foreach ( $its as &$it ) {
		$pid = url_to_postid( $it['url'] );
		if ( $pid !== 0 ) $it['url'] = $pid;
	}
	$skeys = [ 'media', 'caption', 'url', 'type' ];
	if ( $inst->_is_dual ) $skeys[] = 'media_sub';
	update_multiple_post_meta( $post_id, $inst->_key, $its, $skeys );
}

function _get_items( int $post_id, string $size = 'medium' ): array {
	$inst = _get_instance();
	$skeys = [ 'media', 'caption', 'url', 'type' ];
	if ( $inst->_is_dual ) $skeys[] = 'media_sub';

	$its = get_multiple_post_meta( $post_id, $inst->_key, $skeys );

	foreach ( $its as &$it ) {
		if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {
			$permalink = get_permalink( $it['url'] );
			if ( $permalink !== false ) {
				$it['post_id'] = $it['url'];
				$it['url'] = $permalink;
			}
		}
		if ( empty( $it['type'] ) ) $it['type'] = $inst::TYPE_IMAGE;
		$it['image'] = '';
		if ( $it['type'] === $inst::TYPE_IMAGE ) {
			if ( ! empty( $it['media'] ) ) {
				_get_images( $it, intval( $it['media'] ), $size );
			}
			if ( $inst->_is_dual ) {
				$it['image_sub'] = '';
				if ( ! empty( $it['media_sub'] ) ) {
					_get_images( $it, intval( $it['media_sub'] ), $size, '_sub' );
				}
			}
		} else if ( $it['type'] === $inst::TYPE_VIDEO ) {
			$it['video'] = wp_get_attachment_url( $it['media'] );
			$am = _get_image_meta( $it['media'] );
			if ( $am ) $it = array_merge( $it, $am );
		}
	}
	if ( ! is_admin() && $inst->_is_shuffled ) shuffle( $its );
	return $its;
}

function _get_images( array &$it, int $aid, string $size, string $pf = '' ) {
	if ( is_array( $size ) ) {
		$imgs = [];
		foreach ( $size as $sz ) {
			$img = wp_get_attachment_image_src( $aid, $sz );
			if ( $img ) $imgs[] = $img[0];
		}
		if ( ! empty( $imgs ) ) {
			$it["images$pf"] = $imgs;
			$it["image$pf" ] = $imgs[ count( $imgs ) - 1 ];
		}
	} else {
		$img = wp_get_attachment_image_src( $aid, $size );
		if ( $img ) {
			$it["images$pf"] = [ $img[0] ];
			$it["image$pf" ] = $img[0];
		}
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
