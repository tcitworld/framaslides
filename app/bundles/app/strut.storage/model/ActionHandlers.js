define(
function() {
	return {
		save: function(storageInterface, model, filename, cb) {
			console.log('exists status when saving');
			console.log(model.getExistStatus());
      storageInterface.savePresentation(filename, model.exportPresentation(filename), cb, true, model);
		},

    saveNew: function(storageInterface, model, filename, cb) {
      storageInterface.saveNewPresentation(filename, model.exportPresentation(filename), cb, true, model);
    },

		open: function(storageInterface, model, filename, cb) {
		  console.log('before opening : ');
		  console.log(model.getExistStatus());
      console.log(model.fileName());
			if (model.getExistStatus() === false) {
        storageInterface.load(filename, function(data, err) {
          if (!err) {
            console.log('data here !');
            console.log(data);
            model.importPresentation(data);
          } else {
            console.log(err);
            console.log(err.stack);
          }

          cb(null, err);
        });
			} else {
        storageInterface.savePresentation(
          model.fileName(),
          model.exportPresentation(model.fileName()),
          function () {
            storageInterface.load(filename, function (data, err) {
              if (!err) {
                console.log('data here !');
                console.log(data);
                model.importPresentation(data);
              } else {
                console.log(err);
                console.log(err.stack);
              }

              cb(null, err);
            });
          }, false, model);
      }
		},

		new_: function(model) {
		  model.setExistStatus(false);
			model.newPresentation();
		}
	};
});
