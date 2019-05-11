'use strict';

const _ = require('underscore');
const $ = require('jquery');

/**
 * Form manage how a form should be build when a crud manager invokes a create or an update action.
 */
class Form {
    constructor(swalFormOptionsText, template = '#js-manager-form-template', selector = '.js-entity-create-from') {
        this.swalFormOptionsText = swalFormOptionsText;

        this.template = template;
        this.selector = selector;
    }

    /**
     * Form html code.
     *
     * @return {*}
     */
    html() {
        const tplText = $(this.template).html();
        const tpl = _.template(tplText);
        const html = tpl();

        return html;
    }

    /**
     * Defines from options base on the action use by crud manage when an entity is create, update and remove.
     *
     * @param {string} action
     */
    formOptions(action = 'create') {
        let formOptions = {};

        switch (action) {
            case 'create':
                formOptions = {
                    text: this.swalFormOptionsText.create,
                    onBeforeOpen: this.onBeforeOpen.bind(this)
                };

                break;
            case 'update':
                formOptions = {
                    text: this.swalFormOptionsText.update,
                    onBeforeOpen: this.onBeforeOpen.bind(this)
                };
                break;
            case 'delete':
                formOptions = {
                    text: this.swalFormOptionsText.delete
                };

                break;
        }

        return formOptions;
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * This method should overwritten by the child class in case the form requires to be preload.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpen(data, $wrapper) {

    }

    mapErrors($form, errorData) {
        // Remove form errors
        $form.find('.js-field-error').remove();
        $form.find('.form-group').removeClass('has-error');

        // Add errors
        $form.find(':input').each((index, input) => {
            const fieldName = $(input).attr('name');
            const $groupWrapper = $(input).closest('.form-group');
            const $wrapper = $(input).closest('div');

            if (!errorData[fieldName]) {
                // no error!
                return;
            }

            const $error = $('<span class="js-field-error help-block" style="text-align: left;"></span>');
            $error.html(errorData[fieldName]);

            $wrapper.append($error);
            $groupWrapper.addClass('has-error');
        });
    }
}

export default Form;
