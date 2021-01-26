(function ($, Drupal) {
  Drupal.behaviors.trackProgressComponentForm = {
    attach: function attach(context) {
      var $context = $(context);
      $context.find('#edit-tab-archive-on').drupalSetSummary(function (context) {
        var vals = [];

        /** @todo date do not show 24hrs  */
        var date = $(context).find('#edit-archive-on-date').val();
        var time = $(context).find('#edit-archive-on-time').val();
        if (date && time) {
          vals.push(Drupal.checkPlain('On ' + date + ' ' + time));
        }
        else {
          vals.push(Drupal.t('Not set'));
        }

        return vals.join(', ');
      });
      $context.find('#edit-tab-promoted').drupalSetSummary(function (context) {
        var vals = [];
        var msg = $(context).find('#edit-promoted:checked').val()
          ? Drupal.t('Yes')
          : Drupal.t('No');

        vals.push(msg);
        return vals.join(', ');
      });
      $context.find('#edit-tab-weighted-progress').drupalSetSummary(function (context) {
        var vals = [];
        var msg = $(context).find('#edit-weighted-progress:checked').val()
          ? Drupal.t('Opted')
          : Drupal.t('Not opted');

        vals.push(msg);
        return vals.join(', ');
      });
      $context.find('#edit-tab-partial-progress').drupalSetSummary(function (context) {
        var vals = [];
        var msg = $(context).find('#edit-partial-progress:checked').val()
          ? Drupal.t('Allowed')
          : Drupal.t('Not allowed');

        vals.push(msg);
        return vals.join(', ');
      });
      $context.find('#edit-tab-active').drupalSetSummary(function (context) {
        var vals = [];
        var msg = $(context).find('#edit-active:checked').val()
          ? Drupal.t('Active')
          : Drupal.t('Archived');

        vals.push(msg);
        return vals.join(', ');
      });
      $context.find('#edit-tab-assignee').drupalSetSummary(function (context) {
        var vals = [];
        vals.push(Drupal.checkPlain($(context).find('#edit-assignee').val()) || Drupal.t('Assignee is required'));
        return vals.join(', ');
      });
    }
  };
})(jQuery, Drupal);