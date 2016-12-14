define(['./Saver'],
function(Saver) {
	'use strict';
	function TimedSaver(exportables, duration, storageInterface, model) {
		Saver.call(this, exportables, storageInterface, model);

		this._intervalH = setInterval(this.__save.bind(this), duration);
	}

	var proto = TimedSaver.prototype = Object.create(Saver.prototype);

	proto.dispose = function() {
		clearInterval(this._intervalH);
	};

	return TimedSaver;
});
