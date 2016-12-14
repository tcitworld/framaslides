define(['./ExitSaver', './TimedSaver', './Saver'],
function(ExitSaver, TimedSaver) {
	/*
	service will be an auto-saver factory
	so you can get new instance of the auto saver.

	The auto saver takes an object that is exportable.
	*/

	var service = {
		timedSaver: function(exportable, duration, storageInterface, model) {
			return new TimedSaver(exportable, duration, storageInterface, model);
		},

		exitSaver: function(exportable, storageInterface, model) {
			return new ExitSaver(exportable, storageInterface, model);
		},

		manualSaver: function(exportable, storageInterface) {
			return new Saver(exportable, storageInterface);
		}
	};

	return {
		initialize: function(registry) {
			registry.register({
				interfaces: 'tantaman.web.saver.AutoSavers'
			}, service);
		}
	};
});
