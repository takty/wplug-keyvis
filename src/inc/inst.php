<?php
/**
 * Slider (Instance)
 *
 * @package Wplug Slider
 * @author Takuto Yanagida
 * @version 2021-08-02
 */

namespace wplug\slider;

/**
 * Gets instance.
 *
 * @access private
 *
 * @return object Instance.
 */
function _get_instance(): object {
	static $values = null;
	if ( $values ) {
		return $values;
	}
	$values = new class() {

		// Template Admin

		public $_key;
		public $_id;

		public $_effect_type           = 'slide'; // 'scroll' or 'fade'
		public $_duration_time         = 8; // [second]
		public $_transition_time       = 1; // [second]
		public $_background_opacity    = 0.33;
		public $_is_picture_scroll     = false;
		public $_is_random_timing      = false;
		public $_is_background_visible = true;
		public $_is_side_slide_visible = false;
		public $_zoom_rate             = 1;

		public $_caption_type          = 'subtitle'; // 'line' or 'circle'
		public $_is_dual               = false;
		public $_is_video_enabled      = false;
		public $_is_shuffled           = false;

		const NS = 'st-slide-show';

		// Admin
		const CLS_CAP    = 'gida-slider-show-caption';
		const CLS_BODY            = 'st-slide-show-body';
		const CLS_TABLE           = 'st-slide-show-table';
		const CLS_ITEM            = 'st-slide-show-item';
		const CLS_ITEM_TEMP_IMG   = 'st-slide-show-item-template-img';
		const CLS_ITEM_TEMP_VIDEO = 'st-slide-show-item-template-video';
		const CLS_HANDLE          = 'st-slide-show-handle';
		const CLS_ADD_ROW         = 'st-slide-show-add-row';
		const CLS_ADD_IMG         = 'st-slide-show-add-img';
		const CLS_ADD_VIDEO       = 'st-slide-show-add-video';
		const CLS_DEL_LAB         = 'st-slide-show-delete-label';
		const CLS_DEL             = 'st-slide-show-delete';
		const CLS_INFO            = 'st-slide-show-info';
		const CLS_URL_OPENER      = 'st-slide-show-url-opener';
		const CLS_SEL_URL         = 'st-slide-show-select-url';
		const CLS_SEL_IMG         = 'st-slide-show-select-img';
		const CLS_SEL_IMG_SUB     = 'st-slide-show-select-img-sub';
		const CLS_SEL_VIDEO       = 'st-slide-show-select-video';
		const CLS_TN              = 'st-slide-show-thumbnail';
		const CLS_TN_IMG          = 'st-slide-show-thumbnail-img';
		const CLS_TN_IMG_SUB      = 'st-slide-show-thumbnail-img-sub';
		const CLS_TN_NAME         = 'st-slide-show-thumbnail-name';
		const CLS_TN_NAME_SUB     = 'st-slide-show-thumbnail-name-sub';

		const CLS_URL             = 'st-slide-show-url';
		const CLS_TYPE            = 'st-slide-show-type';
		const CLS_MEDIA           = 'st-slide-show-media';
		const CLS_MEDIA_SUB       = 'st-slide-show-media-sub';
		const CLS_TITLE           = 'st-slide-show-title';
		const CLS_TITLE_SUB       = 'st-slide-show-title-sub';
		const CLS_FILENAME        = 'st-slide-show-filename';
		const CLS_FILENAME_SUB    = 'st-slide-show-filename-sub';

		const TYPE_IMAGE = 'image';
		const TYPE_VIDEO = 'video';

	};
	return $values;
}

function _set_key_base( string $key_base ) {
	$inst = _get_instance();

	$inst->FLD_LIST_ID                 = $key_base . 'list_id';
	$inst->FLD_YEAR_START              = $key_base . 'year_start';
	$inst->FLD_YEAR_END                = $key_base . 'year_end';
	$inst->FLD_COUNT                   = $key_base . 'count';
	$inst->FLD_SORT_BY_DATE_FIRST      = $key_base . 'sort_by_date_first';
	$inst->FLD_DUP_MULTI_CAT           = $key_base . 'duplicate_multi_category';
	$inst->FLD_SHOW_FILTER             = $key_base . 'show_filter';
	$inst->FLD_OMIT_HEAD_OF_SINGLE_CAT = $key_base . 'omit_head_of_single_cat';
	$inst->FLD_JSON_PARAMS             = $key_base . 'json_params';
}
