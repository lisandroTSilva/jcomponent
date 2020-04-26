(function () {
	"use strict";

	window.jSelectContact = function (id, title, catid, object, link, lang) {
		var hreflang = '', tag, editor;
		if (!Joomla.getOptions('xtd-contatomodal')) {
			window.parent.jModalClose();
			return false;
		}

		editor = Joomla.getOptions('xtd-contatomodal').editor;

		tag = "{form-contact:" + id + "}"

		if (window.parent.Joomla && window.parent.Joomla.editors && window.parent.Joomla.editors.instances && window.parent.Joomla.editors.instances.hasOwnProperty(editor)) {
			window.parent.Joomla.editors.instances[editor].replaceSelection(tag);
		} else {
			window.parent.jInsertEditorText(tag, editor);
		}
		window.parent.jModalClose();
	};

	document.addEventListener('DOMContentLoaded', function () {
		var elements = document.querySelectorAll('.select-link');
		for (var i = 0, l = elements.length; l > i; i++) {
			elements[i].addEventListener('click', function (event) {
				event.preventDefault();
				var functionName = event.target.getAttribute('data-function');
				if (functionName === 'jSelectContact') {
					window[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);
				} else {
					window.parent[functionName](event.target.getAttribute('data-id'), event.target.getAttribute('data-title'), null, null, event.target.getAttribute('data-uri'), event.target.getAttribute('data-language'), null);
				}
			})
		}
	});
})();
