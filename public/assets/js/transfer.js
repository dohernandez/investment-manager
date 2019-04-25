'use strict';

(function (window, $) {

    /**
     * Create a transfer instance
     * @constructor
     *
     * @param $wrapper
     * @constructor
     */
    window.Transfer = function ($wrapper) {
        this.$wrapper = $wrapper;

        this.$wrapper.on();
    };

    /**
     * Select2 template for account objects.
     *
     * templateResult defines how the selected item will be shown in the select area.
     * templateSelection defines how items will be shown
     *
     * @type {{templateResult: templateResult, templateSelection: templateSelection}}
     */
    var Select2AccountTemplate = {
        templateResult: function(item) {
            if (item.loading) {
                return item.text;
            }

            let markup = "<div class='select2-result-account clearfix'>" +
                "<strong>" + item.name + "</strong>" +
                "<br />" +
                "<small>" + item.iban + "</small>" +
                "</div>";

            return markup;
        },


        /**
         * Define how will be render the item selected.
         *
         * @param {Object} account An account object with text property.
         * @param {string} account.text - The employee's department.
         * @param {string} account.name - The name of the employee.
         * @param {string} account.iban - The name of the employee.
         *
         * @return {string}
         */
        templateSelection: function(account) {
            if (!account.name) {
                return account.text;
            }

            return account.name + " - " + account.iban;
        }
    };

    /**
     * Class represents ajax autocomplete using select2 from https://select2.org/
     *
     * It finds all selects that matches the given css classname and attach an ajax call to find matching
     * options base on current search.
     *
     * @param $wrapper
     * @param template Select2Template contains the functions use on render result and selection.
     * @constructor
     */
    var Select2 = function ($wrapper) {
        this.$wrapper = $wrapper;
    };

    $.extend(Select2.prototype, {

        /**
         * Set url
         * @param {string} url
         */
        withUrl: function (url) {
            this.url = url
        }

    });
})();
