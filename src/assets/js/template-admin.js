/**
 * Template Admin (JS)
 *
 * @author Takuto Yanagida
 * @version 2023-10-10
 */

document.addEventListener('DOMContentLoaded', function () {
	const ts = document.getElementsByClassName('wplug-keyvis-admin');
	for (const t of ts) {
		wplug_keyvis_show_admin(t);
	}
} );

function wplug_keyvis_show_admin(t) {
	const NS = 'wplug-keyvis';

	const CLS_TABLE           = NS + '-table';
	const CLS_ITEM            = NS + '-item';
	const CLS_ITEM_TEMP_IMG   = NS + '-item-template-img';
	const CLS_ITEM_TEMP_VIDEO = NS + '-item-template-video';
	const CLS_ITEM_DEL        = NS + '-item-deleted';
	const CLS_HANDLE          = NS + '-handle';
	const CLS_DEL             = NS + '-delete';
	const CLS_URL_OPENER      = NS + '-url-opener';
	const CLS_SEL_MEDIA       = NS + '-select-media';
	const CLS_SEL_URL         = NS + '-select-url';
	const CLS_TN              = NS + '-thumbnail';
	const CLS_TN_MEDIA        = NS + '-thumbnail-media';
	const CLS_ADD_ROW         = NS + '-add-row';
	const CLS_ADD_IMG         = NS + '-add-img';
	const CLS_ADD_VIDEO       = NS + '-add-video';

	const CLS_URL          = NS + '-url';
	const CLS_TYPE         = NS + '-type';
	const CLS_CAP          = NS + '-caption';
	const CLS_MEDIA        = NS + '-media';
	const CLS_TITLE        = NS + '-title';
	const CLS_FILENAME     = NS + '-filename';

	const STR_ADD = document.getElementsByClassName(CLS_ADD_IMG)[0].innerText;

	const temp = document.getElementsByClassName(CLS_SEL_URL);
	const STR_SEL = temp.length ? temp[0].innerText : '';

	const key     = t.dataset.key;
	const is_show = t.classList.contains('show');

	const tbl       = t.getElementsByClassName(CLS_TABLE)[0];
	const items     = tbl.getElementsByClassName(CLS_ITEM);
	const tempImg   = t.getElementsByClassName(CLS_ITEM_TEMP_IMG)[0];
	const tempVideo = t.getElementsByClassName(CLS_ITEM_TEMP_VIDEO)[0];
	const addRow    = t.getElementsByClassName(CLS_ADD_ROW)[0];
	const addImg    = t.getElementsByClassName(CLS_ADD_IMG)[0];
	const addVideos = t.getElementsByClassName(CLS_ADD_VIDEO);
	const addVideo  = addVideos.length ? addVideos[0] : null;

	sortable(tbl, {
		handle              : '.' + CLS_HANDLE,
		forcePlaceholderSize: true,
	});

	for (let i = 0; i < items.length; i += 1) assign_event_listener(items[i]);

	setMediaPicker(addImg, false, (t, ms) => {
		ms.forEach((m) => { add_new_item_image(m); });
		sortable(tbl);
	}, { multiple: true, type: 'image', title: STR_ADD });
	if (addVideo) {
		setMediaPicker(addVideo, false, (t, ms) => {
			ms.forEach((m) => { add_new_item_video(m); });
			sortable(tbl);
		}, { multiple: true, type: 'video', title: STR_ADD });
	}


	// -------------------------------------------------------------------------


	function add_new_item_image(f) {
		const it = tempImg.cloneNode(true);
		it.getElementsByClassName(CLS_TN_MEDIA)[0].style.backgroundImage = "url('" + f.url + "')";
		it.getElementsByClassName(CLS_TYPE)[0].value = 'image';
		set_new_item(it, f);

		it.classList.remove(CLS_ITEM_TEMP_IMG);
		it.classList.add(CLS_ITEM);
		tbl.appendChild(it);
		assign_event_listener(it);
	}

	function add_new_item_video(f) {
		const it = tempVideo.cloneNode(true);
		it.getElementsByClassName(CLS_TN_MEDIA)[0].src = f.url;
		it.getElementsByClassName(CLS_TYPE)[0].value = 'video';
		set_new_item(it, f);

		it.classList.remove(CLS_ITEM_TEMP_VIDEO);
		it.classList.add(CLS_ITEM);
		tbl.appendChild(it, addRow);
		assign_event_listener(it);
	}

	function set_new_item(it, f) {
		if (is_show) it.getElementsByClassName(CLS_CAP)[0].value = f.caption;
		it.getElementsByClassName(CLS_MEDIA)[0].value        = f.id;
		it.getElementsByClassName(CLS_FILENAME)[0].innerText = f.filename;
		const tn = it.getElementsByClassName(CLS_TN)[0];

		if (f.title.length < f.filename.length && f.filename.indexOf(f.title) === 0) {
			it.getElementsByClassName(CLS_TITLE)[0].innerText = '';
			tn.getElementsByClassName(CLS_TN_MEDIA)[0].parentElement.setAttribute('title', f.filename);
		} else {
			it.getElementsByClassName(CLS_TITLE)[0].innerText = f.title;
			tn.getElementsByClassName(CLS_TN_MEDIA)[0].parentElement.setAttribute('title', f.title + '\n' + f.filename);
		}

		const idx = tbl.getElementsByClassName(CLS_ITEM).length;
		if (is_show) set_idx(it.getElementsByClassName(CLS_CAP), idx);
		if (is_show) set_idx(it.getElementsByClassName(CLS_URL), idx);
		set_idx(it.getElementsByClassName(CLS_DEL), idx);
		set_idx(it.getElementsByClassName(CLS_TYPE), idx);
		set_idx(it.getElementsByClassName(CLS_MEDIA), idx);
	}

	function set_idx(elms, idx) {
		for (const elm of elms) elm.name = `${key}[${idx}]` + elm.name;
	}

	function assign_event_listener(it) {
		const del     = it.getElementsByClassName(CLS_DEL)[0];
		const opener  = it.getElementsByClassName(CLS_URL_OPENER)[0];
		const sel_url = it.getElementsByClassName(CLS_SEL_URL)[0];

		del.addEventListener('click', (e) => {
			if (e.target.checked) {
				it.classList.add(CLS_ITEM_DEL);
			} else {
				it.classList.remove(CLS_ITEM_DEL);
			}
		});
		if (is_show) {
			opener.addEventListener('click', (e) => {
				e.preventDefault();
				const url = it.getElementsByClassName(CLS_URL)[0].value;
				if (url) window.open(url);
			});
			window.wplug.keyvis.setLinkPicker(sel_url, false, (t, f) => { it.getElementsByClassName(CLS_URL)[0].value = f.url; }, { parentGen: 1 });
		}

		const tns = it.getElementsByClassName(CLS_TN);
		if (it.getElementsByClassName(CLS_TYPE)[0].value === 'image') {
			for (const tn of tns) {
				const sel = tn.getElementsByClassName(CLS_SEL_MEDIA)[0];
				setMediaPicker(sel, false, (t, f) => {
					if (is_show && tn === tns[0]) it.getElementsByClassName(CLS_CAP)[0].value = f.caption;
					const img = tn.querySelector(':scope > a > img');
					if (img) img.parentElement.removeChild(img);

					tn.getElementsByClassName(CLS_TN_MEDIA)[0].style.backgroundImage = 'url(' + f.url + ')';
					set_item(tn, f);
				}, { multiple: false, type: 'image', title: STR_SEL });
			}
		} else {
			const tn = tns[0];
			const sel = tn.getElementsByClassName(CLS_SEL_MEDIA)[0];
			setMediaPicker(sel, false, (t, f) => {
				if (is_show) it.getElementsByClassName(CLS_CAP)[0].value = f.caption;
				tn.getElementsByClassName(CLS_TN_MEDIA)[0].src = f.url;
				set_item(tn, f);
			}, { multiple: false, type: 'video', parentGen: 1, title: STR_SEL });
			const v = tn.getElementsByClassName(CLS_TN_MEDIA)[0];
			v.loop  = true;
			v.muted = true;
			sel.addEventListener('mouseenter', () => { v.play(); });
			sel.addEventListener('mouseleave', () => { v.pause(); });
		}
	}

	function set_item(tn, f) {
		const t = (0 < f.title.length && f.filename.startsWith(f.title)) ? '' : f.title;
		tn.getElementsByClassName(CLS_TITLE   )[0].innerText = t;
		tn.getElementsByClassName(CLS_FILENAME)[0].innerText = f.filename;
		tn.getElementsByClassName(CLS_MEDIA   )[0].value     = f.id;
	}

}
