define(['libs/backbone', 'strut/storage/model/ActionHandlers', 'strut/storage/view/StorageModal', 'strut/storage/model/StorageInterface', '../PreviewLauncher', 'lang'],
function(Backbone, ActionHandlers, StorageModal, StorageInterface , PreviewLauncher, lang) {
	return Backbone.View.extend({
		className: 'btn-group iconBtns',
		events: {
			'click .act': '_launch'
		},

		initialize: function() {
			this._editorModel = this.options.editorModel;
			this._previewLauncher = new PreviewLauncher(this._editorModel);
			this._generators = this._editorModel.registry
				.getBest('strut.presentation_generator.GeneratorCollection');

			delete this.options.editorModel;
			// TODO: we should keep session meta per bundle...
			this._index = Math.min(window.sessionMeta.generator_index || 0, this._generators.length - 1);
			this._generatorChanged();

			this._template = JST['strut.presentation_generator/Button'];

      /* var storageInterface = new StorageInterface(this._editorModel.registry);

      this.saveAsModal = new StorageModal({
        editorModel: this._editorModel,
        storageInterface: storageInterface
      });
      */
		},

		_launch: function() {
		  if (this._editorModel.getExistStatus()) {
        this._previewLauncher.launch(this._generators[this._index]);
      } else {
		    alert('Vous devez enregistrer la présentation avant de pouvoir la visionner');
        //this.saveAsModal.show(ActionHandlers.save, lang.save_as);
      }
		},

		_bind: function() {
			var self = this;
			this.$el.find('li').each(function(i) {
				var $btn = $(this);
				$btn.click(function(e) {
					// self._previewLauncher.launch(self._generators[i]);
					self.$el.find('.check').css('visibility', 'hidden');
					$btn.find('.check').css('visibility', '');
					self._index = i;
					window.sessionMeta.generator_index = i;
					self._generatorChanged();
					self.$el.find('.dropdown-toggle').dropdown('toggle');
					e.stopPropagation();
				});
			});
		},

		/**
		* Need to inform the world of a generator update.
		* Some modes are only present for certain generators.
		*/
		_generatorChanged: function() {
			this._editorModel.set('generator', this._generators[this._index]);
			if (this._$readout)
				this._$readout.text(this._generators[this._index].displayName);
		},

		render: function() {
			this.$el.html(this._template({ preview: lang.preview}));
			this._bind();
			this._$readout = this.$el.find('.chosen');
			$(this.$el.find('.check')[this._index]).css('visibility', '');
			return this;
		}
	});
});
