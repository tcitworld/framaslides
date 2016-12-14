define(function() {
	var prefix = "strut-";
	function UserStorageProvider() {
		this.name = "User Storage";
		this.id = "userstorage";
	}
	var alerted = false;

	UserStorageProvider.prototype = {
		ready: function() {
			return true;
		},

		bg: function() {

		},

		ls: function(path, cb) {
			$.ajax({
				url: '/presentations-json',

			}).success(function(data) {
				var presentations = data.map(function(elem) {
					return elem.title;
				});
				cb(presentations);
			});
			return this;
		},

		rm: function(path, cb) {
			$.ajax({
				method: 'GET',
				url: '/delete-presentation/' + path
			}).success(function (data) {
				cb(JSON.parse(data));
			});
			return this;
		},

		getContents: function(path, cb) {
			$.ajax({
				method: 'GET',
				url: '/presentation/' + path,
			}).success(function (data) {
				cb(JSON.parse(data));
			});
			return this;
		},

		setContents: function(path, data, cb, saveAction) {
			$.ajax({
				method: 'POST',
				url: '/new-presentation',
				data: {
					presentation: path,
					data: JSON.stringify(data),
					newEntry: (saveAction !== undefined && saveAction !== false) ? 1 : 0
				}
			}).success(function (data, status, xhr) {
				if (cb) {
					cb(true);
				}
			});
			return this;
		}
	};

	return UserStorageProvider;
});
