define(function() {
	'use strict';
	var launch = 0;

	function PreviewLauncher(editorModel) {
		this._editorModel = editorModel;
	};

	PreviewLauncher.prototype = {
		launch: function(generator) {
			if (window.previewWind)
				window.previewWind.close();

			this._editorModel.trigger('launch:preview', null);
			var editorModel = this._editorModel;

			var previewStr = generator.generate(this._editorModel.deck());

			$.ajax({
				type: 'POST',
				url: '/save-preview/' + editorModel.fileName(),
				data: {
					previewData: previewStr,
				},
			}).success(function() {
				window.previewWind = window.open(
					'/preview/' + editorModel.fileName() + '/' + generator.id + generator.getSlideHash(editorModel),
					window.location.href);

				var sourceWind = window;
			});

			localStorage.setItem('preview-string', previewStr);
			localStorage.setItem('preview-config', JSON.stringify({
				surface: this._editorModel.deck().get('surface')
			}));



		}
	};

	return PreviewLauncher;
});