<script>
	window.tinyConfig = {
		selector: '.js-wysiwyg',
		height: "70",
		theme: 'wezom',
		language: 'en',
		default_language: 'en',
		plugins: [
			"advlist autolink lists link image charmap preview print hr",
			"searchreplace wordcount visualblocks visualchars code fullscreen",
			"insertdatetime media nonbreaking save table contextmenu directionality",
			"emoticons paste textcolor colorpicker textpattern"
		],
		table_class_list: [
			{title: '{{ __('cms-core::admin.js.tinymce.Default') }}', value: ''},
			{title: '{{ __('cms-core::admin.js.tinymce.Without border') }}', value: 'table-null'},
			{title: '{{ __('cms-core::admin.js.tinymce.Zebra') }}', value: 'table-zebra'},
			{title: '{{ __('cms-core::admin.js.tinymce.Design') }}', value: 'table-design'}
		],
		toolbar1: "undo redo pastetext | bold italic forecolor backcolor fontselect fontsizeselect styleselect | alignleft aligncenter alignright alignjustify",
		toolbar2: 'bullist numlist outdent indent | link unlink image media fullscreen currentdate PreviewButton',
		image_advtab: true,
		convert_urls: false,
		relative_urls: false,
		body_class: "wysiwyg",
		toolbar: "preview",
		mobile: {
			theme: 'mobile',
			height: "200",
			plugins: ['autosave', 'lists', 'autolink'],
			toolbar: ['undo', 'bold', 'italic', 'styleselect']
		},
		//validate content in tag
		fix_list_elements: true,
		valid_children: 'li[span|a]',
		extended_valid_elements: "svg[*],defs[*],pattern[*],desc[*],metadata[*],g[*],mask[*],path[*],line[*],marker[*],rect[*],circle[*],ellipse[*],polygon[*],polyline[*],linearGradient[*],radialGradient[*],stop[*],image[*],view[*],text[*],textPath[*],title[*],tspan[*],glyph[*],symbol[*],switch[*],use[*]",
		//show block type
		visualblocks_default_state: false,
		//styles
        content_css: "{{ (is_file(public_path('css/editor.css')) ? asset('css/editor.css') . ', ' : '') . (is_file(public_path('fonts/fonts.css')) ? asset('fonts/fonts.css') : '') }}",
		content_css_cors: true,
		setup: function (editor) {
			function toTimeHtml (date) {
				return '<time datetime="' + date.toString() + '">' + date + '</time>';
			}

			function insertDate () {
				var options = {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
				var date = new Date();
				var selectorLang = $(editor.targetElm).data('lang');
				if (selectorLang == 'ua') {
					selectorLang = 'uk';
				}
				selectorLang = selectorLang || language;
				var html = toTimeHtml(date.toLocaleDateString(selectorLang, options));
				editor.insertContent(html);
			}

			editor.addButton('currentdate', {
				icon: 'insertdatetime',
				tooltip: "Insert date\/time",
				onclick: insertDate
			});

			editor.addButton('PreviewButton', {
				icon: "preview",
				tooltip: "Preview",
				onclick: function () {
					editor.execCommand('mcePreview');
					$('html').addClass('mce-open');
					return;
				}
			});

			editor.on('CloseWindow', function (e) {
				$('html').removeClass('mce-open');
				return;
			});
		},
		file_browser_callback: function (field_name, url, type, win) {
			var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
			var y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

			var cmsURL = '/filemanager?field_name=' + field_name;
			if (type == 'image') {
				cmsURL = cmsURL + "&type=Images";
			} else {
				cmsURL = cmsURL + "&type=Files";
			}

			tinyMCE.activeEditor.windowManager.open({
				file: cmsURL,
				title: 'Filemanager',
				width: x * 0.8,
				height: y * 0.8,
				resizable: "yes",
				close_previous: "no"
			});
		}
	};

	//update options tinyMCE
	// (function() {
	// 	window.tinyConfig = Object.assign(window.tinyConfig, {
	// 		//style formats
	// 		style_formats_merge: true,
    //         //https://www.tiny.cloud/docs/configure/editor-appearance/#style_formats
	// 		style_formats: [],
    //         //Установка базового цвета, размера шрифта и т.д.
	// 		content_style: 'body, td, pre {color:#414140; font-family:Helvetica,sans-serif; font-size:14px;line-height: 1.6}',
	// 		//font config
	// 		fontsize_formats: "0.5em 0.7em 0.938em 1em 1.025em 1.125em 1.325em 1.5em 1.7em 1.8em 2em 2.2em 2.3em 2.5em 2.8em 3em",
	// 		font_formats: "Helvetica=Helvetica,sans-serif;",
	// 		theme_advanced_fonts: "Helvetica=Helvetica,sans-serif;",
	// 	});
	// })();
</script>
