'use strict';

(function (window, $, moment) {
    /**
     * Create a Index page instance to allow generate form for new data, using modal view.
     *
     * @param $wrapper
     * @param $createModal
     *
     * @constructor
     */
    window.IndexPage = function ($wrapper, $createModal) {
        this.$wrapper = $wrapper;
        this.$createModal = $createModal;

        // Delegate selector
        this.$wrapper.on(
            'click',
            '.entity-create',
            this.handleModalFormDisplay.bind(this)
        )

        // Delegate selector
        this.$createModal.on(
            'click',
            '#submit-btn',
            this.handleModalFormSubmit.bind(this)
        )
    };

    $.extend(window.IndexPage.prototype, {
        _selectors: {
            createForm: '.js-transfer-create-from'
        },

        /**
         * Handle on click event for create button
         * @param e
         */
        handleModalFormDisplay: function (e) {
            e.preventDefault();

            let $processing = this.$createModal.find('.js-modal-create-processing');
            console.log('$processing', $processing);

            this._clearForm();
        },

        /**
         * Clean up the form
         *
         * @private
         */
        _clearForm: function() {
            let $form = this.$createModal.find(this._selectors.createForm);
            console.log('$form', $form);

            $form[0].reset();
        },

        handleModalFormSubmit: function(e) {
            e.preventDefault();

            let $form = this.$createModal.find(this._selectors.createForm);
            let formData = {};

            $.each($form.serializeArray(), function(key, fieldData) {
                    formData[fieldData.name] = fieldData.value
            });

            console.log('$form', $form, formData);

            this._saveForm(formData);
        },

        _saveForm: function(data) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: Routing.generate('transfer_save'),
                    method: 'POST',
                    data: JSON.stringify(data),
                    success: function (data) {
                        console.log(data);
                    },
                    error: function (jqXHR) {
                        // TODO implement error form handling
                        console.log(jqXHR.responseText);
                    }
                });
            });
        },
    });
    
})(window, jQuery, moment);
