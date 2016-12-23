import jQuery from 'jquery';
import moment from 'moment';
import 'moment/locale/fr';
import bootstrap from 'bootstrap'; // eslint-disable-line no-unused-vars
import Clipboard from 'clipboard';
import cachedFetched from './cachedFetched';

const template = require('./views/versions.twig'); // eslint-disable-line

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

  $('.tab a').click((e) => {
      e.preventDefault();
      $(this).tab('show');
  });

  /**
   * Initialize copy/paste
   */
  new Clipboard('.btn-copy'); // eslint-disable-line no-new

  const body = $('body');

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

  function getVersionsPage(presentation, page = 1) {
    return $.ajax(`versions/${presentation}/${page}`);
  }

  /**
   * Show version list
   */
  $('.label.versions').on('click', (event) => {
    event.preventDefault();
    const elem = $(event.target);
    $('#versionModal').attr('data-presentation', elem.parents('.card').attr('data-presentation'));
    $('#versionModal .modal-title').text(`Versions pour la présentation « ${elem.parents('.card').find('.card-title .title').text()} »`);
    $('#versionModal .modal-body').empty();
    getVersionsPage(elem.closest('.card').attr('data-presentation')).done((data) => {
      const versions = data.versions;
      versions.forEach((version) => {
        version.updated_at = moment(version.updated_at).format('LLLL'); // eslint-disable-line
      });

      const html = template(
        {
          versions,
          data,
        });

      $('.modal-body').append(html);
      $('body').tooltip({
        selector: '[data-toggle="tooltip"]',
      });
      $('#versionModal').modal();
    });
  });

  function navigationPage(presentation, versionNb, next) {
    const modifier = next ? 1 : -1;
    const page = parseInt(versionNb, 10) + parseInt(modifier, 10);
    getVersionsPage(presentation, page).done((data) => {
      const versions = data.versions;
      versions.forEach((version) => {
        version.updated_at = moment(version.updated_at).format('LLLL'); // eslint-disable-line
      });

      const html = template(
        {
          versions,
          data,
        });

      $('.modal-body').empty();
      $('.modal-body').append(html);
      $('div.versions').attr('data-page', page);
      $('#versionModal').modal();
    });
  }

  body.on('click', 'a.previous', () => {
    navigationPage($('#versionModal').attr('data-presentation'), $('div.versions').attr('data-page'), false);
  });

  body.on('click', 'a.next', () => {
    navigationPage($('#versionModal').attr('data-presentation'), $('div.versions').attr('data-page'), true);
  });

  /**
   * Version stuff
   */
  const modalBody = $('#versionModal .modal-body');

  /**
   * Delete version
   */
  modalBody.on('click', '.icon-delete', (event) => {
    event.preventDefault();
    const elem = $(event.target).closest('li');
    $.ajax({
      url: `delete-version/${elem.attr('data-version')}`,
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
      url: `restore-version/${$(event.target).closest('li').attr('data-version')}`,
      success: () => {
        $('#versionModal .modal-header .alert-info strong').text('Version restaurée');
        $('#versionModal .modal-header .alert-info .desc').text('Une nouvelle version correspondante a été créée.');
        $('#versionModal .modal-header .alert-info').toggleClass('hidden');
      },
    });
  });

  body.on('click', '#purgeVersions', () => {
    const confirm = window.confirm('Voulez-vous vraiment supprimer toutes les anciennes versions ?');
    if (confirm) {
      $.ajax({
        url: `purge-versions/${$('#versionModal').attr('data-presentation')}`,
        success: () => {
          $('#versionModal').modal('hide');
        },
      });
    }
  });

  /**
   * Presentation title edition
   */
  body.on('click', '.edit-title', (e) => {
    $(e.target).parent().find('.title').attr('contenteditable', 'true');
    $(e.target).removeClass('glyphicon-edit');
    $(e.target).addClass('glyphicon glyphicon-ok');
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
   * Fork presentation
   */
  $('.fork').on('click', (event) => {
    event.preventDefault();
    const elem = $(event.target).parents('.card');
    const templateModal = $('#forkModal');
    templateModal.attr('data-presentation', elem.attr('data-presentation'));
    templateModal.modal();
  });

  /**
   * Save fork stuff
   */
  body.on('click', '#fork-save', () => {
    $.ajax({
      method: 'POST',
      url: `create-from-template/${$('#forkModal').attr('data-presentation')}`,
      data: {
        title: $('#presentationtitle').val(),
      },
      success: () => {
        $('#forkModal').modal('hide');
      },
    });
  });
});
