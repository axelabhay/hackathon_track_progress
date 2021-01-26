/**
* @todo this file.
**/

(function ($, Drupal) {

  /****** ON INTERACT PAGE */
  /** @todo */
  if (typeof drupalSettings.trackProgress !== 'undefined' && typeof drupalSettings.trackProgress.interact !== 'undefined' ) {
    $.each(drupalSettings.trackProgress.interact, function(key, data) {
      //data = drupalSettings.trackProgress.interact[aid];
      console.log(data);
      refreshProgress(data);
    });
  }

  $('.track-progress-interact').on('change', function(event, aid) {
    data = drupalSettings.trackProgress.interact[aid];
    console.log(drupalSettings);
    refreshProgress(data);
  });

  // @todo should work if new progress_theme added via form-alter. Probably add OOPs JS.
  function refreshProgress(data) {
    console.log(drupalSettings);

    console.log(data);

    if (typeof data === 'undefined') {
      console.log('No iteract data.');
      return false;
    }

    if (data.theme === 'track_progress_interact') {
          console.log('track_progress_interact');
          track_progress_interact(data);
    }
    else if (data.theme === 'track_progress_interact__circular') {
          console.log('track_progress_interactC');
          track_progress_interact__circular(data);
    }
  }

  function track_progress_interact(data) {
    //@todo round percent properly all places
    $('.track-progress.' + data.id + ' .progess-info-percent').html(Math.round(data.progress['percent']) + '%');
    // @todo diplay for 2 of 3 must be configuratble.
    $('.track-progress.' + data.id + ' .progess-info-details').html(data.progress['completed'] + ' of ' + data.progress['total'] + ' done');


    $.each(data.progress['category_wise'], function($key, $val) {
      $('.track-progress.' + data.id + ' .category-progress-' + $key).css('width', $val['width'] + '%');
      $('.track-progress.' + data.id + ' .category-progress-' + $key).css('background', data.category_list[$key]['color']);
      // @todo round()
      $('.track-progress.' + data.id + ' .category-progress-' + $key).attr('data-tooltip' , data.category_list[$key]['title'] + ' ' + $val['percent'] + '% (' + $val['completed'] + ' of ' + $val['total'] + ' done)');
    });
  }
  function track_progress_interact__circular(data) {
    //@todo round percent properly all places
    $('.track-progress.' + data.id + ' .progess-info-percent').html(Math.round(data.progress['percent']) + '%');
    // @todo diplay for 2 of 3 must be configuratble.
    $('.track-progress.' + data.id + ' .progess-info-details').html(data.progress['completed'] + ' of ' + data.progress['total'] + ' done');

    $.each(data.progress['category_wise'], function($key, $val) {
      $('.track-progress.' + data.id + ' .category-progress-' + $key).css('width', $val['width'] + '%');
      $('.track-progress.' + data.id + ' .category-progress-' + $key).css('background', data.category_list[$key]['color']);
      // @todo round()
      $('.track-progress.' + data.id + ' .category-progress-' + $key).attr('data-tooltip' , data.category_list[$key]['title'] + ' ' + $val['percent'] + '% (' + $val['completed'] + ' of ' + $val['total'] + ' done)');
    });
  }
  /****** ON INTERACT PAGE END */


})(jQuery, Drupal);
