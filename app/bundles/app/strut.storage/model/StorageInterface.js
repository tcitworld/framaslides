define(['tantaman/web/storage/StorageProvidersWrapper', 'strut/presentation_generator/PreviewLauncher'],
function(StorageProviders, PreviewLauncher) {
	'use strict';

	// TODO: update to use ServiceCollection
	// remove presentation specific garbage
	function StorageInterface(registry) {
		this._providers = new StorageProviders(registry);
	}

	StorageInterface.prototype = {
		providerNames: function() {
			return this._providers.providerNames();
		},

		providerReady: function($el) {
			return this.currentProvider().ready($el);
		},

		activateProvider: function($el, cb) {
			this.currentProvider().activate($el, cb);
		},

		selectProvider: function(providerId) {
			this._providers.selectProvider(providerId);
		},

		currentProvider: function() {
			return this._providers.currentProvider();
		},

		currentProviderId: function() {
			return this._providers._currentProviderId;
		},

		on: function() {
			return this._providers.on.apply(this._providers, arguments);
		},

		store: function(identifier, data, cb, saveAction) {
			this.currentProvider().setContents(identifier, data, cb, saveAction);
			return this;
		},

		load: function(identifier, cb) {
			this.currentProvider().getContents(identifier, cb);
			return this;
		},

		remove: function(identifier, cb) {
			this.currentProvider().rm(identifier, cb);
			return this;
		},

		list: function(path, cb) {
			this.currentProvider().ls(path, /.*/, cb);
			return this;
		},

		listPresentations: function(path, cb) {
			this.currentProvider().ls(path, cb)
			return this;
		},

		savePresentation: function(identifier, data, cb, saveAction, model) {

			this.store(identifier, data, cb, saveAction);

			model.setExistStatus(true);
      /** Also save preview */
      var previewLauncher = new PreviewLauncher(model);
      var generators = model.registry
        .getBest('strut.presentation_generator.GeneratorCollection');
      previewLauncher.launch(generators[0], true);
		},

		saveNewPresentation: function (identifier, data, cb, saveAction, model) {
      this.currentProvider().create(identifier, data, function (data) {
        model.setFileName(data.id);
        model.setBackendId(data.id);

        var previewStr = model.registry.getBest('strut.presentation_generator.GeneratorCollection')[0].generate(model.deck());
        var previewConfig = JSON.stringify({
          surface: model.deck().get('surface')
        });

        $.ajax({
          type: 'POST',
          url: '/save-preview/' + data.id,
          data: {
            previewData: previewStr,
            previewConfig: previewConfig
          }
        });
        if (cb) {
          cb(data, null);
        }
      });
    }
	};

	return StorageInterface;
});
