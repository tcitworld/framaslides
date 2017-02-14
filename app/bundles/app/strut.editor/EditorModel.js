define(['libs/backbone',
	'strut/header/model/HeaderModel',
	'strut/deck/Deck',
	'strut/slide_components/ComponentFactory',
	'common/Adapter',
	'tantaman/web/interactions/Clipboard',
	'./GlobalEvents',
	'tantaman/web/undo_support/CmdListFactory'],
	function(Backbone, Header, Deck, ComponentFactory, Adapter, Clipboard, GlobalEvents, CmdListFactory) {
		'use strict';

		function adaptStorageInterfaceForSavers(storageInterface) {
			return new Adapter(storageInterface, {
				store: 'savePresentation'
			});
		}

		return Backbone.Model.extend({
			initialize: function() {
				// is there a better way to do this?
				window.uiTestAcc = this;

				this._fontState = window.sessionMeta.fontState || {};
				this._deck = new Deck();
				this._deck.on('change:customBackgrounds', function(deck, bgs) {
					this.trigger('change:customBackgrounds', this, bgs)
				}, this);
				this.addSlide();

				this.set('header', new Header(this.registry, this));

				this.set('modeId', 'slide-editor');

				this.exportable = new Adapter(this, {
					export: 'exportPresentation',
					identifier: 'fileName'
				});

				this.exportable.adapted = this;

				this.filename = this._deck.get('fileName');
				this.storageInterface = null;

				var savers = this.registry.getBest('tantaman.web.saver.AutoSavers');
				if (savers) {
					this.storageInterface = this.registry.getBest('strut.StorageInterface');
					this.storageInterface = adaptStorageInterfaceForSavers(this.storageInterface);
					this._exitSaver = savers.exitSaver(this.exportable, this.storageInterface, this);
					this._timedSaver = savers.timedSaver(this.exportable, 60000, this.storageInterface, this);
				}

				this.clipboard = new Clipboard();
				this._createMode();

				this._cmdList = CmdListFactory.managedInstance('editor');
				GlobalEvents.on('undo', this._cmdList.undo, this._cmdList);
				GlobalEvents.on('redo', this._cmdList.redo, this._cmdList);

				Backbone.on('etch:state', this._fontStateChanged, this);
			},

			changeActiveMode: function(modeId) {
				if (modeId != this.get('modeId')) {
					this.set('modeId', modeId);
					this._createMode();
				}
			},

			customStylesheet: function(css) {
				if (css == null) {
					return this._deck.get('customStylesheet');
				} else {
					this._deck.set('customStylesheet', css);
				}
			},

			dispose: function() {
				throw "EditorModel can not be disposed yet"
				this._exitSaver.dispose();
				this._timedSaver.dispose();
				Backbone.off(null, null, this);
			},

			newPresentation: function() {
				var num = window.sessionMeta.num || 0;

				num += 1;
				window.sessionMeta.num = num;

				this.setExistStatus(false);

				console.log('inside newPresentation editormodel');
        window.location.hash = '';

				this.importPresentation({
	        		fileName: "new-prez",
	        		slides: []
	      		});
				this._deck.create();
			},

			/**
			 * see Deck.addCustomBgClassFor
			 */
			addCustomBgClassFor: function(color) {
				var result = this._deck.addCustomBgClassFor(color);
				if (!result.existed) {
					this.trigger('change:customBackgrounds', this, this._deck.get('customBackgrounds'));
				}
				return result;
			},

			customBackgrounds: function() {
				return this._deck.get('customBackgrounds');
			},

      getExistStatus: function () {
        return this._deck.get('exists');
      },

			setExistStatus: function (exists) {
				this._deck.set('exists', exists);
      },

			getBackendId: function () {
				return this._deck.get('backendId');
      },

      setBackendId: function (backendId) {
				this._deck.set('backendId');
      },

			importPresentation: function(rawObj) {
				// deck disposes iteself on import?
				console.log('New file name: ' + rawObj.fileName);
				this._deck.import(rawObj);
			},

			exportPresentation: function(filename) {
				if (filename)
					this._deck.set('fileName', filename);
				var obj = this._deck.toJSON(false, true);
				return obj;
			},

			fileName: function() {
				console.log('fileName called');
				var fname = this._deck.get('fileName');
				if (fname == null) {
					// TODO...
					fname = 'new-prez';
					this._deck.set('fileName', fname);
				}

				return fname;
			},

			setFileName: function (newFileName) {
				this._deck.set('fileName', newFileName);
      },

			deck: function() {
				return this._deck;
			},

			cannedTransition: function(c) {
				if (c != null)
					this._deck.set('cannedTransition', c);
				else
					return this._deck.get('cannedTransition');
			},

			slides: function() {
				return this._deck.get('slides');
			},

			addSlide: function(index) {
				this._deck.create(index);
			},

			activeSlide: function() {
				return this._deck.get('activeSlide');
			},

			activeSlideIndex: function() {
				return this._deck.get('slides').indexOf(this._deck.get('activeSlide'));
			},

			addComponent: function(type) {
				var slide = this._deck.get('activeSlide');
				if (slide) {
					var comp = ComponentFactory.instance.createModel(type, {
						fontStyles: this._fontState
					});
					slide.add(comp);
				}
			},

			_fontStateChanged: function(state) {
				_.extend(this._fontState, state);
				window.sessionMeta.fontState = this._fontState;
			},

			_createMode: function() {
				var modeId = this.get('modeId');
				var modeService = this.registry.getBest({
					interfaces: 'strut.EditMode',
					meta: { id: modeId }
				});

				if (modeService) {
					var prevMode = this.get('activeMode');
					if (prevMode)
						prevMode.close();
					this.set('activeMode', modeService.getMode(this, this.registry));
				}
			},

			constructor: function EditorModel(registry) {
				this.registry = registry;
				Backbone.Model.prototype.constructor.call(this);
			}
		});
	});
