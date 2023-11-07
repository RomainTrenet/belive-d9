/**
 * @file
 * Select box.
 */

/*jshint esversion: 6 */

/* global jQuery */
(function bbRegistration(jQuery) {

  "use strict";

  // Function called by refindUsers Ajax callback. Hidden non wanted users is
  // Preferred as if there are no users to hide we show the "select all" option.
  // Hiding "select all" is better because it does not take into account the
  // non-wanted users.
  jQuery.fn.bbrRefindArtists = function(select, usersToHide) {
    // TODO should be gotten from real select > closest fake select ?.
    const $select = jQuery(select).find('> .vsb-menu > ul');

    const $select_options = $select.find('li').not('[data-value = all]');
    const $select_option_all = $select.find('li[data-value = all]');

    // If there are artists to hide.
    if(Object.keys(usersToHide).length) {
      // Hide the "All" option.
      $select_option_all.hide();

      // Show all and hide what needs to be hidden.
      $select_options.show();
      Object.keys(usersToHide).map(function(artist_id) {
        $select.find('li[data-value="' + artist_id + '"]').hide();
      });

    }
    // If there are no artist to hide.
    else {
      // Show the "All option".
      $select_option_all.show();

      // Show all.
      $select_options.show();
    }
  };

  // We pass the parameters of this anonymous function, the global variables
  // that this script depend on.
}(jQuery));
