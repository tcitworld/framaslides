define(['../locales/de',
		'../locales/en',
		'../locales/es',
		'../locales/fr',
		'../locales/nl',
		'../locales/ru',
		'../locales/ar',
		'handlebars'],
function(de, en, es, fr, nl, ru, ar, handlebars) {
	var langs = {
		en: en,
		de: de,
		es: es,
		fr: fr,
		nl: nl,
		ru: ru,
		ar: ar
	};

	var userlang;
	$.ajax({
	  method: 'GET',
	  url: '/users/locale',
    async: false,
    success: function (data) {
      userlang = data;
    }
  });

	var lang = userlang || window.navigator.language || window.navigator.userLanguage;
	var result = langs[lang.split('-')[0]] || langs.en;
	handlebars.registerHelper("lang", function(key) {
		return result[key];
	});

	return result;
});
