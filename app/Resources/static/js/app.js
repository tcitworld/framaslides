import jQuery from 'jquery';
import moment from 'moment';
import 'moment/locale/fr';
import bootstrap from 'bootstrap'; // eslint-disable-line no-unused-vars
import Clipboard from 'clipboard';
import cachedFetched from './cachedFetched';

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
cachedFetched('/user-locale')
  .then(r => r.text())
  .then(locale => moment.locale(locale));

/**
 * jQuery Calls
 */
$(() => {
  /**
   * Initialize all tooltips
   */
  $(() => {
    $('[data-toggle="tooltip"]').tooltip();
  });

  /**
   * Initialize copy/paste
   */
  new Clipboard('.btn-copy'); // eslint-disable-line no-new

  /**
   * Share stuff
   */
  $('.share').on('click', (event) => {
    event.preventDefault();
    const elem = $(event.target).parents('.card');
    const shareModal = $('#shareModal');

    fetch(`share/${elem.attr('data-presentation')}`, {
      credentials: 'same-origin',
    }).then((response) => {
      if (response.ok) {
        $('#presentationshareurl').val(response.url);
        $('#btn-external').attr('href', response.url);
        shareModal.attr('data-presentation', elem.attr('data-presentation'));
        shareModal.modal();
      }
    });
  });

  $('#deleteshare').on('click', () => {
    const shareModal = $('#shareModal');
    fetch(`share/delete/${shareModal.attr('data-presentation')}`, {
      credentials: 'same-origin',
    }).then((response) => {
      if (response.ok) {
        shareModal.modal('hide');
      }
    });
  });

  /**
   * Delete presentation
   */
  $('.delete').on('click', (event) => {
    event.preventDefault();
    const elem = $(event.target).parents('.card');
    $.ajax({
      url: `delete-presentation/${elem.attr('data-presentation-title')}`,
      success: () => {
        elem.addClass('item-hidden').delay(400).remove();
        if ($('.card').length === 0) {
          $('.no-presentations-message').show();
        }
      },
    });
  });

  /**
   * Fork presentation
   */
  $('.fork').on('click', (event) => {
    event.preventDefault();
    const elem = $(this);
    const templateModal = $('#forkModal');
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
      $('.modal-body > .list-group').append(`<li class="list-group-item" data-version="' + version.id + '">${moment(version.updated_at).format('LLLL')}<span class="icon icon-download pull-right" data-toggle="tooltip" data-placement="bottom" title="Télécharger cette version"><a href="export-version/${version.id}"><i class="glyphicon glyphicon-download-alt"></i></a></span>${(idx === array.length - 1 ? '' : '<span class="icon icon-delete pull-right" data-toggle="tooltip" data-placement="bottom" title="Supprimer cette version"><i class="glyphicon glyphicon-trash"></i></span><span class="icon icon-restore pull-right" data-toggle="tooltip" data-placement="bottom" title="Restaurer cette version (crée une nouvelle version)"><i class="glyphicon glyphicon-repeat"></i></span>')}</li>`);
    });
  }

  /**
   * Show version list
   */
  $('.label.versions').on('click', (event) => {
    event.preventDefault();
    const elem = $(event.target);
    $('#versionModal .modal-title').text(`Versions pour la présentation « ${elem.closest('.card-title .title').text()} »`);
    $('#versionModal .modal-body > .list-group').empty();
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
   */
  const modalBody = $('.modal-body');
  const body = $('body');

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
  function publicIfTemplate() {
    const makepublic = $('#makepublic');
    if (makepublic.attr('disabled')) {
      makepublic.removeAttr('disabled');
      makepublic.parent().parent().removeClass('disabled');
    } else {
      makepublic.attr('disabled', 'true');
      makepublic.parent().parent().addClass('disabled');
    }
  }

  /**
   * Publish presentation
   */
  $('.publish').on('click', (event) => {
    event.preventDefault();
    const elem = $(event.target);
    const templateModal = $('#templateModal');
    templateModal.find('.modal-title').text(`Sauvegarder « ${elem.parents('.card').find('.title').text()} » en modèle`);
    templateModal.attr('data-presentation', elem.parents('.card').attr('data-presentation'));
    templateModal.find('#maketemplate').prop('checked', elem.parents('.card').attr('data-template') === '1');
    publicIfTemplate();
    templateModal.find('#makepublic').prop('checked', elem.parents('.card').attr('data-public') === '1');
    templateModal.modal();
  });

  $('#maketemplate').change(() => {
    publicIfTemplate();
  });

  /**
   * Save template stuff
   */
  body.on('click', '#template-save', () => {
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

  /**
   * Save fork stuff
   */
  body.on('click', '#fork-save', () => {
    $.ajax({
      url: `create-from-template/${$('#forkModal').attr('data-presentation')}`,
      data: {
        title: $('presentationtitle').val(),
      },
      success: () => {
        $('#forkModal').modal('hide');
      },
    });
  });
});
