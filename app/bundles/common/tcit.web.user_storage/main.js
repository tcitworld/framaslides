define(["./UserStorageProvider"],
    function(UserStorageProvider) {
        var service = new UserStorageProvider();

        return {
            initialize: function(registry) {
                registry.register({
                    interfaces: 'tantaman.web.StorageProvider'
                }, service);
            }
        };
    });