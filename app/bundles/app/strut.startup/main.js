define(['strut/editor/EditorView',
        'strut/editor/EditorModel'],
function(EditorView, EditorModel) {

  if (!String.prototype.startsWith) {
    String.prototype.startsWith = function (searchString, position) {
      position = position || 0;
      return this.substr(position, searchString.length) === searchString;
    };
  }

	var registry = null;
	var editorStartup = {
		run: function() {
			var model = new EditorModel(registry);
    		var editor = new EditorView({model: model, registry: registry});
    		editor.render();
    		$('body').append(editor.$el);

    		if (window.location.hash.startsWith('#')) {
    			// Load it up.
    			var storageInterface = registry.getBest('strut.StorageInterface');
    			storageInterface.load(window.location.hash.slice(1), function(pres, err) {
    				if (!err) {
    					model.importPresentation(pres);
    				} else {
    					console.log(err);
    					console.log(err.stack);
    				}
    			});
    		}
		}
	};

	var welcome = {
		run: function() {
			// If no previous presentation was detected, show the welcome screen.
		}
	};

	return {
		initialize: function(reg) {
			registry = reg;
			registry.register({
				interfaces: 'strut.StartupTask'
			}, editorStartup);

			registry.register({
				interfaces: 'strut.StartupTask'
			}, welcome);
		}
	};
});
