/**
 * @file
 * Select box.
 */

/*jshint esversion: 6 */

/* global jQuery, Drupal, drupalSettings, once, vanillaSelectBox */
(function selectBox(jQuery, Drupal, drupalSettings, once, vanillaSelectBox) {

  "use strict";

  // TODO clean and convert it to typescript.

  /* eslint-disable no-param-reassign */
  /*Drupal.behaviors.selectBoxDefaultParams = {
    selectBoxClass: 'vanilla-select-box',
  };
  /* eslint-enable no-param-reassign */
  const selectBoxDefaultParams = {
    selectBoxClass: 'vanilla-select-box',
  };

  /**
   * SelectBox object.
   */
  function SelectBox() {
    this.is_open = false;
    this.disabled = false;
  }

  SelectBox.prototype = {
    constructor: SelectBox,
    init: function init(selectBox) {
      const self = this;
      self.$selectBox = jQuery(selectBox);

      // If there is a selectBox.
      if (self.$selectBox) {
        // @todo : get infos from select data.
        new vanillaSelectBox(
          '#' + selectBox.id,
          {
            "placeHolder": Drupal.t('Select'),
            //"maxSelect":3,
            //translations: { "all": "All", "items": "Cars" },
            "maxHeight":200,
            search:true
          }
        );
        //self.bindHandlers();
      }
    },

    // Bind selectBox click. @todo : remove.
    /*
    bindHandlers: function bindHandlers() {
      const self = this;

      // Open selectBox is also the close selectBox by default.
      self.$selectBoxBtn.on('click', (e) => {
        e.preventDefault();

        // Menu is not open nor disabled.
        if (!self.is_open && !self.disabled) {
          self.selectBox.showModal();
        }
      });
    },*/
  };



  /**
   * SelectBoxManager object.
   */
  function SelectBoxManager() {
  }

  SelectBoxManager.prototype = {
    constructor: SelectBoxManager,
    tables: {},

    init: function init() {
      const self = this;

      // Add settings object if not exist.
      self.manageSettings();

      // If there are selectBoxes.
      const selector = '.' + drupalSettings.selectBoxManager.selectBoxClass;
      if (jQuery(selector).length > 0) {
        jQuery(selector).each(function inst() {
          self.instantiateSelectBox(this);
        });
      }
    },

    // Add settings object if not exist.
    manageSettings: function manageSettings() {
      // Add default classes, if not specified.
      if (!drupalSettings.selectBoxManager.selectBoxClass) {
        drupalSettings.selectBoxManager.selectBoxClass = selectBoxDefaultParams.selectBoxClass;
      }
    },

    // Instantiate selectBox ; ensure select box have an ID.
    instantiateSelectBox: function instantiateSelectBox(selectBox) {
      if (selectBox.id === '') {
        selectBox.setAttribute('id', `SelectBox${this.getRandomSelectBoxId()}`);
      }

      // Record and init new instance.
      const instance = new SelectBox();
      if (!drupalSettings.selectBoxManager.manager.selectBoxs[selectBox.id]) {
        drupalSettings.selectBoxManager.manager.selectBoxs[selectBox.id] = instance;
        instance.init(selectBox);
      }
    },

    /**
     * Get random selectBox id, for element that don't have one by default.
     *
     * @return {string}
     *   The id randomly generated.
     */
    getRandomSelectBoxId: function getRandomSelectBoxId() {
      const random = `00000${(Math.random() * 16777216).toString(16)}`;
      return random.substr(-6).toUpperCase();
    },
  };

  /* eslint-disable no-param-reassign */
  Drupal.behaviors.selectBox = {

    /* eslint-enable no-param-reassign */
    attach: function attach(context) {
      /* eslint-disable func-names */
      once('selectBox', 'html', context).forEach(() => {
        /* eslint no-param-reassign:["error",
        { "props": true, "ignorePropertyModificationsFor": ["drupalSettings"] }]
        */
        if (!drupalSettings.selectBoxManager) {
          drupalSettings.selectBoxManager = {};
        }
        drupalSettings.selectBoxManager.manager = new SelectBoxManager();
        drupalSettings.selectBoxManager.manager.selectBoxs = [];
        drupalSettings.selectBoxManager.manager.init();
      });
    },

  };

  // We pass the parameters of this anonymous function, the global variables
  // that this script depend on.
}(jQuery, Drupal, drupalSettings, once, vanillaSelectBox));
