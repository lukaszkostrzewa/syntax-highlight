jQuery(function ($) {
	var textarea = $("#newcontent");

	if (textarea.length === 0) {
		// element doesn't exist
		return;
	}

	var editDiv = $('<div>', {
		position: 'absolute',
		width: textarea.width(),
		height: textarea.height(),
		'class': textarea.attr('class')
	}).insertBefore(textarea);

	textarea.css('display', 'none');

	var editor = ace.edit(editDiv[0]);
	editor.getSession().setValue(textarea.val());

	var modelist = ace.require('ace/ext/modelist');
	var filePath = $("input[name='file']").attr("value");
	var mode = modelist.getModeForPath(filePath).mode;
	editor.getSession().setMode(mode);

	editor.setStyle("ace-fix");
	
	// copy back to textarea on form submit...
	textarea.closest('form').submit(function () {
		textarea.val(editor.getSession().getValue());
	});

	// Apply settings
	editor.renderer.setShowGutter(shSettings['show_line_numbers'] == 1);
	editor.getSession().setUseWrapMode(shSettings['word_wrap'] == 1);
	editor.getSession().setTabSize(shSettings['tab_size']);
	editor.getSession().setUseSoftTabs(shSettings['use_soft_tabs'] == 1);
	editor.setHighlightActiveLine(shSettings['highlight_curr_line'] == 1);
	editor.setSelectionStyle(shSettings['full_line_selection'] == 1 ? "line" : "text");
	editor.setTheme("ace/theme/" + shSettings['theme']);

	switch(shSettings['key_bindings']) {
		case 'vim':
			editor.setKeyboardHandler("ace/keyboard/vim");
			break;
		case 'emacs':
			editor.setKeyboardHandler("ace/keyboard/emacs");
			break;
		default:
			break;
	}

	if (shSettings['unsaved_changes'] == 1) {
		var changed = false;
		editor.on("change", function (e) {
			changed = true;
		});

		$(window).bind('beforeunload', function (e) {
			if (changed) {
				return shSettings['unsaved_changes_txt'];
			}
		});
	}

	var dom = ace.require("ace/lib/dom");
	editor.commands.addCommand({
		name: "Toggle Fullscreen",
		bindKey: {win: "Ctrl-Enter", mac: "Command-Enter"},
		exec: function(editor) {
			console.log('Ctrl-Enter');
			dom.toggleCssClass(document.body, "fullScreen");
			dom.toggleCssClass(editor.container, "fullScreen-editor");
			editor.resize();
		}
	});

	var ajaxSave = function() {
		var $div = $('<div />').appendTo('body');
		$div.attr('id', 'save-spinner');
		$div.hide();
		$("#template").submit(function() {
			var url = $(this).attr("action"); // the script where you handle the form input.
			$.ajax({
				type: "POST",
				url: url,
				data: $("#template").serialize(), // serializes the form's elements.
				beforeSend: function () {
					$('#save-spinner').show();
				},
				success: function(data) {
					changed = false;
				},
				complete: function(data) {
					$('#save-spinner').hide();
				}
			});
			return false; // avoid to execute the actual submit of the form.
		});
	};

	if (shSettings['ctrls_save'] == 1) {
		ajaxSave();
		var editorFocused = false;

		editor.on("focus", function (e) {
			editorFocused = true;
		});

		editor.on("blur", function (e) {
			editorFocused = false;
		});

		$(window).bind('keydown', function(event) {
			if (editorFocused && (event.ctrlKey || event.metaKey)) {
				switch (String.fromCharCode(event.which).toLowerCase()) {
				case 's':
					event.preventDefault();
					$("#template").submit();
					break;
				}
			}
		});
	}
});
