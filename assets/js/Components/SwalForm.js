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
 * @param {Object} swalOptions.editView
 * @param {Object} swalOptions.deleteView
 * @param {Object} swalOptions.detailView
 * @param {Object} swalOptions.confirm
 * @param {Object} swalOptions.text
 */
class SwalForm {
    constructor(swalOptions, template = '#js-panel-form-template', selector = '.js-entity-from') {
        this.swalOptions = swalOptions;

        this.template = template;
        this.selector = selector;
    }

    create(url) {
        let swalOptions = {
            options: this.swalOptions.editView,
            onBeforeOpen: this.onBeforeOpenEditView.bind(this),
            confirmButtonText: this.swalOptions.text.create.confirmButtonText,
            titleText: this.swalOptions.text.create.titleText,
            confirmTitleText: this.swalOptions.text.create.confirmTitleText,
        };

        return this.display(swalOptions, url, 'create');
    }

    /**
     * Display a form to create or edit entity.
     *
     * @param {Object} swalOptions
     * @param {Object} swalOptions.options
     * @param {function} swalOptions.onBeforeOpen
     * @param {string} swalOptions.confirmButtonText
     * @param {string} swalOptions.titleText
     * @param {string} swalOptions.confirmTitleText
     * @param {string} url
     * @param {string} action
     * @param {Object} data Use to pre populate the from.
     *
     * @return {*|Promise|Promise<T | never>}
     */
    display(swalOptions, url, action, data = null) {
        // Build form html base on the template.
        const html = this.html();

        // Swal form modal
        const swalForm = Swal.mixin(swalOptions.options);// The options use to show the form inside the modal and how to parser the inputs.

        return swalForm.fire({
            html: html,
            confirmButtonText: swalOptions.confirmButtonText,
            titleText: swalOptions.titleText,
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
                this.showStatusMessage(swalOptions.confirmTitleText);
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
     * Show action success message.
     *
     * @param titleText
     */
    showStatusMessage(titleText) {
        const toast = Swal.mixin(this.swalOptions.confirm);

        toast.fire({
            type: 'success',
            titleText
        });
    }

    /**
     * Defines instructions to execute before the form totaly display.
     *
     * This method should overwritten by the child class in case the form requires to be preload.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpenEditView(data, $wrapper) {
        console.log('onBeforeOpenEditView');
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
        let swalOptions = {
            options: this.swalOptions.editView,
            onBeforeOpen: this.onBeforeOpenEditView.bind(this),
            confirmButtonText: this.swalOptions.text.update.confirmButtonText,
            titleText: this.swalOptions.text.update.titleText,
            confirmTitleText: this.swalOptions.text.update.confirmTitleText,
        };

        return this.display(swalOptions, url, 'update', data);
    }

    delete(url, id, title) {
        // Create delete text confirmation.
        const text = this.swalOptions.deleteView.text.replace(/\{0\}/g, '"' + title + '"');

        // Swal confirmation modal
        const swalConfirm = Swal.mixin(this.swalOptions.deleteView);

        return swalConfirm.fire({
            text,
            preConfirm: () => {
                return InvestmentManagerClient.sendRPC(url, 'DELETE');
            }
        }).then((result) => {
            // Show popup with success message
            if (result.value) {
                this.showStatusMessage(this.swalOptions.text.delete.confirmTitleText);
            }

            return result
        }).catch((arg) => {
            // canceling is cool!

            console.log(arg);
        });
    }
}

export default SwalForm;
