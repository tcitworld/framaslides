define(
function() {
	return {
		save: function(storageInterface, model, filename, cb) {
			storageInterface.savePresentation(filename, model.exportPresentation(filename), cb, true, model);
		},

		open: function(storageInterface, model, filename, cb) {
			storageInterface.savePresentation(
				model.fileName(),
				model.exportPresentation(model.fileName()),
				function () {
					storageInterface.load(filename, function(data, err) {
						if (!err) {
							console.log(data);
							model.importPresentation(data);
						} else {
							console.log(err);
							console.log(err.stack);
						}

						cb(null, err);
					});
				}, false, model);
		},

		new_: function(model) {
			model.newPresentation();
		}
	};
});
