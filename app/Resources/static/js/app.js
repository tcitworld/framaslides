import jQuery from 'jquery';
import moment from 'moment';
import bootstrap from 'bootstrap'; // eslint-disable-line no-unused-vars

/**
 * CSS Imports
 */
require('../css/app.scss');

/**
 * jQuery compatibility for nav
 */
const $ = jQuery;
window.jQuery = jQuery;

/**
 * Save user locale
 */
let localePromise;
if (sessionStorage.getItem('lang')) {
  localePromise = new Promise(sessionStorage.getItem('lang'));
} else {
  localePromise = fetch('/user-locale/')
    .then(response => response.text());
}
Promise.resolve(localePromise).then((locale) => {
  moment.locale(locale);
});

/**
 * jQuery Calls
 */
$(() => {

  /**
   * Delete presentation
   */
  $('.delete').on('click', (event) => {
    event.preventDefault();
    const elem = $(this);
    $.ajax({
      url: `delete-presentation/${elem.attr('data-presentation')}`,
      success: () => {
        const card = elem.closest('.card').parent();
        card.addClass('item-hidden').delay(400).remove();
        if ($('.card').length === 0) {
          $('.no-presentations-message').show();
        }
      },
    });
  });

  /**
   * Publish presentation
   */
  $('.publish').on('click', (event) => {
    event.preventDefault();
    const elem = $(this);
    const templateModal = $('#templateModal');
    templateModal.attr('data-presentation', elem.attr('data-presentation'));
    templateModal.modal();
  });

  /**
   * Generate versions body from version list
   * @param versions
   */
  function generateVersionList(versions) {
    versions.reverse();
    versions.forEach((version, idx, array) => {
      $('.modal-body > .list-group').append(`<li class="list-group-item" data-version="' + version.id + '">${moment(version.updated_at).format('LLLL')}<span class="icon icon-download pull-right" data-toggle="tooltip" data-placement="bottom" title="Télécharger cette version"><a href="export-version/${version.id}"><i class="material-icons">file_download</i></a></span>${(idx === array.length - 1 ? '' : '<span class="icon icon-delete pull-right" data-toggle="tooltip" data-placement="bottom" title="Supprimer cette version"><i class="material-icons">delete</i></span><span class="icon icon-restore pull-right" data-toggle="tooltip" data-placement="bottom" title="Restaurer cette version (crée une nouvelle version)"><i class="material-icons">restore</i></span>')}</li>`);
    });
  }

  /**
   * Show version list
   */
  $('.label.versions').on('click', (event) => {
    event.preventDefault();
    const elem = $(event.target);
    $('.modal-title').text(`Versions pour la présentation « ${elem.closest('.card-title .title').text()} »`);
    $('.modal-body > .list-group').empty();
    $.ajax({
      url: `versions/${elem.closest('.card').attr('data-presentation')}`,
      success: (versions) => {
        generateVersionList(versions);
        $('body').tooltip({
          selector: '[data-toggle="tooltip"]',
        });
        $('#versionModal').modal();
      },
    });
  });

  /**
   * Version stuff
   * @type {HTMLElement}
   */
  const modalBody = $('.modal-body');

  /**
   * Delete version
   */
  modalBody.on('click', '.icon-delete', (event) => {
    event.preventDefault();
    const elem = $(this).closest('li');
    $.ajax({
      url: `delete-version/${$(this).closest('li').attr('data-version')}`,
      success: () => {
        elem.addClass('item-hidden').delay(400).remove();
      },
    });
  });

  /**
   * Restore version
   */
  modalBody.on('click', '.icon-restore', (event) => {
    event.preventDefault();
    $.ajax({
      url: `restore-version/${$(this).closest('li').attr('data-version')}`,
      success: (versions) => {
        $('.modal-body > .list-group').empty();
        generateVersionList(versions);
        $('body').tooltip({
          selector: '[data-toggle="tooltip"]',
        });
        $('.modal-header .alert-info strong').text('Version restaurée');
        $('.modal-header .alert-info .desc').text('Une nouvelle version correspondante a été créée.');
        $('.modal-header .alert-info').show();
      },
    });
  });

  /**
   * Presentation title edition
   */
  $('.card').on('click', '.card-title .title', () => {
    $(this).attr('contenteditable', 'true');
  });

  /**
   * Template stuff
   */

  /**
   * Disable making a template public is presentation is not template
   */
  $('#maketemplate').change(() => {
    const makepublic = $('#makepublic');
    if (makepublic.attr('disabled')) {
      makepublic.removeAttr('disabled');
      makepublic.parent().parent().removeClass('disabled');
    } else {
      makepublic.attr('disabled', 'true');
      makepublic.parent().parent().addClass('disabled');
    }
  });

  /**
   * Save template stuff
   */
  $('body').on('click', '#template-save', () => {
    $.ajax({
      url: `make-template/${$('#templateModal').attr('data-presentation')}`,
      data: {
        template: $('#maketemplate').prop('checked'),
        public: $('#makepublic').prop('checked'),
      },
      success: () => {
        $('#templateModal').modal('hide');
      },
    });
  });
});
