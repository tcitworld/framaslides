define(['tantaman/web/widgets/MenuItem',
    'framework/ServiceCollection',
    'lang'],
  function(MenuItem, ServiceCollection, lang) {
    'use strict';

    // Very boiler-platey.  Need to get
    // some dynamic dependency injection to declarative
    // wire all this type of stuff up

    function userPresentationsLauncher(editorModel) {
      // Launch the file browser
      // forward the file chosen event off to the various registered services...
      document.location = document.location.origin;
    }

    var menuProvider = {
      createMenuItems: function(editorModel) {
        return new MenuItem({ title: lang.return, handler: userPresentationsLauncher, model: editorModel});
      }
    };

    return {
      initialize: function(registry) {
        registry.register({
          interfaces: 'strut.LogoMenuItemProvider'
        }, menuProvider);
      }
    };
  });
