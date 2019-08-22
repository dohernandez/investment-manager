'use strict';

import InvestmentManagerClient from "./InvestmentManagerClient";

import Swal from 'sweetalert2';
import $ from 'jquery';
import Template from "./Template";


/**
 * Form manage how a form should be build when a crud manager invokes a create or an update action.
 *
 * @param {Object} $container
 * @param {Object} swalOptions
 * @param {Object} swalOptions.form
 * @param {Object} swalOptions.confirm
 * @param {Object} swalOptions.toast
 * @param {Object} swalOptions.text
 */
class SwalForm {
    constructor(swalOptions, template = '#js-panel-form-template', selector = '.js-entity-from') {
        this.swalOptions = swalOptions;

        this.template = template;
        this.selector = selector;
    }

    create(url) {
        return this.display(url, 'create');
    }

    /**
     * Display a form to create or edit entity.
     *
     * @param {Object} data Use to pre populate the from.
     * @param {string} force Use to for an action.
     *
     * @return {*|Promise|Promise<T | never>}
     */
    display(url, action, data = null, force = '') {
        if (force == 'create' || force == 'update') {
            action = force;
        }

        // Build form html base on the template.
        const html = this.html();
        const swalOptions = this.formOptions(action);

        // Swal form modal
        const swalForm = Swal.mixin(swalOptions.form);// The options use to show the form inside the modal and how to parser the inputs.

        return swalForm.fire({
            html: html,
            confirmButtonText: swalOptions.formConfirmButtonText,
            titleText: swalOptions.formTitleText,
            onBeforeOpen: () => {
                const $modal = $(swalForm.getContainer()).find('.swal2-modal');

                swalOptions.onBeforeOpen(data, $modal);
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
                        let $swalValidationMessage = $('#swal2-validation-message');
                        $swalValidationMessage.empty();

                        if (errorsData.errors) {
                            this.mapErrors($form, errorsData.errors);

                            return false;
                        }

                        if (errorsData.message) {
                            $swalValidationMessage.append(
                                $('<span></span>').html(errorsData.message)
                            ).show()
                        }

                        return false;
                    });
            },
        }).then((result) => {
            // Show popup with success message
            if (result.value) {
                this.showStatusMessage(swalOptions.toastTitleText);
            }

            return result
        }).catch((arg) => {
            // canceling is cool!
            console.log(arg)
        });
    }

    /**
     * Form html code.
     *
     * @return {*}
     */
    html() {
        return Template.compile(this.template);
    }

    /**
     * Defines from options base on the action use by crud manage when an entity is create, update and remove.
     *
     * @param {string} action
     */
    formOptions(action = 'create') {
        let formOptions = {
            form: this.swalOptions.form,
            onBeforeOpen: this.onBeforeOpen.bind(this),
        };

        switch (action) {
            case 'create':
                formOptions['formConfirmButtonText'] = this.swalOptions.text.create.confirmButtonText;
                formOptions['formTitleText'] = this.swalOptions.text.create.titleText;
                formOptions['toastTitleText'] = this.swalOptions.text.create.toastTitleText;

                break;
            case 'update':
                formOptions['formConfirmButtonText'] = this.swalOptions.text.update.confirmButtonText;
                formOptions['formTitleText'] = this.swalOptions.text.update.titleText;
                formOptions['toastTitleText'] = this.swalOptions.text.update.toastTitleText;

                break;
            case 'delete':
                formOptions['toastTitleText'] = this.swalOptions.text.delete.toastTitleText;

                break;
        }

        return formOptions;
    }

    /**
     * Show action success message.
     *
     * @param titleText
     */
    showStatusMessage(titleText) {
        const toast = Swal.mixin(this.swalOptions.toast);

        toast.fire({
            type: 'success',
            titleText
        });
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
        console.log('onBeforeOpen');
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

    update(url, data) {
        return this.display(url, 'update', data);
    }

    delete(url, id, title) {
        // Create delete text confirmation.
        const text = this.swalOptions.confirm.text.replace(/\{0\}/g, '"' + title + '"');

        // Swal confirmation modal
        const swalConfirm = Swal.mixin(swalConfirmOptions);

        return swalConfirm.fire({
            text,
            preConfirm: () => {
                return InvestmentManagerClient.sendRPC(url, 'DELETE');
            }
        }).then((result) => {
            // Show popup with success message
            if (result.value) {
                this.showStatusMessage(this.formOptions('delete').toastTitleText);
            }

            return result
        }).catch((arg) => {
            // canceling is cool!

            console.log(arg);
        });
    }
}

export default SwalForm;
