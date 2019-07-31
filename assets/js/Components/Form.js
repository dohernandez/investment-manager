'use strict';

import InvestmentManagerClient from "./InvestmentManagerClient";

import _ from 'underscore';
import $ from 'jquery';

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
     * Create a form to create or edit entity.
     *
     * @param {Object} data Use to pre populate the from.
     * @param {string} force Use to for an action.
     *
     * @return {*|Promise|Promise<T | never>}
     */
    create(swalForm, url, action, data = null, force = '') {
        if (force == 'create' || force == 'update') {
            action = force;
        }

        // Build form html base on the template.
        const html = this.html();

        // The options use to show the form inside the modal and how to parser the inputs.
        const formOptions = this.formOptions(action);

        return swalForm.fire({
            html: html,
            confirmButtonText: formOptions.text.confirmButtonText,
            titleText: formOptions.text.titleText,
            onBeforeOpen: () => {
                const $modal = $(swalForm.getContainer()).find('.swal2-modal');

                formOptions.onBeforeOpen(data, $modal);
            },
            preConfirm: () => {
                // Getting form data.
                const $form = $(swalForm.getContainer()).find(this.selector);
                const formData = {};

                $.each($form.serializeArray(), (key, fieldData) => {
                    formData[fieldData.name] = fieldData.value
                });

                // Sending the data to the server.
                let method = action === 'create' ? 'POST' : 'PUT';

                return InvestmentManagerClient.sendRPC(url, method, formData)
                // Catches response error
                    .catch((errorsData) => {
                        $('#swal2-validation-message').empty();

                        if (errorsData.errors) {
                            this.mapErrors($form, errorsData.errors);

                            return false;
                        }

                        if (errorsData.message) {
                            $('#swal2-validation-message').append(
                                $('<span></span>').html(errorsData.message)
                            ).show()
                        }

                        return false;
                    });
            },
        });
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

    toastTitleText(action = 'create') {
        return this.formOptions(action).text.toastTitleText;
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

    /**
     * Callback function when data is created
     *
     * This method should overwritten by the child class in case the form requires to do an action after data is stored.
     *
     * @param {Object} data
     */
    onCreated(data) {

    }

    /**
     * Callback function when data is preview
     *
     * This method should overwritten by the child class in case the form requires to do an action after data is preview.
     *
     * @param {Object} data
     */
    onPreview(data) {

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
