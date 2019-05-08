'use strict';

(function (window, $) {

    /**
     * Form manage how the account form should be build when a crud manager invokes a create or an update action.
     */
    class AccountForm extends window.Form {
        constructor(swalFormOptionsText, template = '#js-manager-form-template', selector = '.js-entity-create-from') {
            super(swalFormOptionsText, template, selector);
        }

        /**
         * Defines how inputs inside the form must be parser.
         *
         * @param {Object} data
         * @param $wrapper
         */
        onBeforeOpen(data, $wrapper) {
            if (data) {
                let $form = $wrapper.find(this.selector);
                for (const property in data) {
                    let $input = $form.find('#' + property);

                    $input.val(data[property]);
                }
            }
        }
    }

    window.AccountForm = AccountForm;

})(window, jQuery);
