/**
* @todo this file.
**/

(function ($, Drupal) {
  /****** ON CREATE/LIST PAGES */
  function hintCategoryColor(selector, color) {
    $('.category-color').css('display', 'none');

    // Check if hex code.
    // if (typeof color !== 'undefined'  && color.length === 7 && !isNaN(Number('0x' + color))) {
    if (/^#[0-9A-F]{6}$/i.test(color)) {
      $(selector).append('<span class=\'category-color\'>&#11044;</span>');
      $('.category-color').css("color", color);
    }
  }

  // Hint color on category list page.
  $('span.hint-category-color').each(function(index, item) {
    var res = this.classList.value.match("color-(.{7})");
    if (res !== null && res.length) {
      $(this).css("background", res[1]);
    }
  });

  // Category label set background.
  $('.category-label').each(function(index, item) {
    var classes = $(this).attr('class');
    var res = classes.match("color-(.{7})");
    if (res !== null && res.length) {
      $(this).css("background", res[1]);
    }

    // $(this).css("background", item.innerText);
  });

  // Hint user of category-color while adding/updating a task.
  if (drupalSettings.trackProgress && drupalSettings.trackProgress.categoryColor) {
    var categoryColor = drupalSettings.trackProgress.categoryColor;
    var colorCode =  $('select').val();
    // @todo consider empty case.
    // @todo multiple calls before launch.
    hintCategoryColor(".form-item-category label", categoryColor[colorCode]);
  }
  $('select').on('change', function() {
    hintCategoryColor(".form-item-category label", categoryColor[this.value]);
  });

  /****** ON CREATE/LIST PAGES END */

})(jQuery, Drupal);
