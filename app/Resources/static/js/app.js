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

  $('#template_template').change(() => {
    const makepublic = $('#template_public');
    const maketemplate = $('#template_template');
    if (maketemplate.prop('checked')) {
      makepublic.removeAttr('disabled');
      makepublic.parent().parent().removeClass('disabled');
    } else {
      makepublic.attr('disabled', 'true');
      makepublic.prop('checked', false);
      makepublic.parent().parent().addClass('disabled');
    }
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
