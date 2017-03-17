define(function() {
	'use strict';
	var launch = 0;

	function PreviewLauncher(editorModel) {
		this._editorModel = editorModel;
	};

	PreviewLauncher.prototype = {
		launch: function(generator, _openWindow) {
		  var openWindow = _openWindow || false;
			if (window.previewWind)
				window.previewWind.close();

			this._editorModel.trigger('launch:preview', null);
			var editorModel = this._editorModel;

			if (!openWindow) {
          window.previewWind = window.open(
            '/slides/preview/' + editorModel.fileName() + generator.getSlideHash(editorModel),
            window.location.href);

          var sourceWind = window;
        }
		}
	};

	return PreviewLauncher;
});
