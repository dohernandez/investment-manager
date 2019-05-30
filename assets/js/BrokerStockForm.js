'use strict';

import Form from "./Components/Form";
import Select2StockTemplate from './Components/Select2StockTemplate';
import $ from 'jquery';

import 'select2';

import './../css/BrokerStockForm.scss';

/**
 * Form manage how the form should be build when a crud manager invokes a create or an update action.
 */
class BrokerStockForm extends Form {
    constructor(swalFormOptionsText, template = '#js-manager-form-template', selector = '.js-entity-create-from') {
        super(swalFormOptionsText, template, selector);
    }

    static _selector() {
        return {
            stockAutocomplete: '.js-broker-stock-autocomplete',
        }
    }

    /**
     * Defines from options base on the action use by crud manage when an entity is create, update and remove.
     *
     * @param {string} action
     */
    formOptions(action = 'create') {
        let formOptions = {};

        switch (action) {
            case 'add':
                formOptions = {
                    text: this.swalFormOptionsText.add,
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

    render() {
        // Because during render, this function is called with this bind
        const crudManager = this;

        let select2StockTemplate = new Select2StockTemplate();

        let $autocomplete = $(BrokerStockForm._selector().stockAutocomplete);

        $autocomplete.each((index, select) => {
            const url = $(select).data('autocomplete-url');

            $(select).select2({
                ajax: {
                    url,
                    dataType: 'json',
                    delay: 10,
                    allowClear: true,
                    data: (params) => {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: (data, params)=> {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                placeholder: 'Search for an stock',
                escapeMarkup: (markup) => markup,
                minimumInputLength: 1,
                templateResult: select2StockTemplate.templateResult,
                templateSelection: select2StockTemplate.templateSelection
            });
        });
    }

    addStock(e) {
        e.preventDefault();

        // Setting form data.
        const $form = $(e.currentTarget).closest('form');

        const formData = {};

        $.each($form.serializeArray(), (key, fieldData) => {
            formData[fieldData.name] = fieldData.value
        });

        let url = this.routing(this.entityType, 'add');

        // The options use to show the form inside the modal and how to parser the inputs.
        const formOptions = this.form.formOptions('add');

        this._sendRPC(url, 'POST', formData)
        // Catches response error
            .catch((errorsData) => {
                // $('#swal2-validation-message').empty();

                if (errorsData.errors) {
                    console.log(errorsData.errors);
                    // this.form.mapErrors($form, errorsData.errors);

                    return false;
                }

                if (errorsData.message) {
                    // $('#swal2-validation-message').append(
                    //     $('<span></span>').html(errorsData.message)
                    // ).show()
                    console.log(errorsData.message);
                }

                return false;
            })
            .then((result) => {
                console.log(formOptions);
                // Show popup with success message
                if (result.item) {
                    this._showStatusMessage(formOptions.text.toastTitleText);

                    let $autocomplete = $(BrokerStockForm._selector().stockAutocomplete);
                    $autocomplete.each((index, select) => {
                        $(select).val(null).trigger('change');
                    });
                }

                return result
            }).then((result) => {
                if (result.item) {
                    let entity = result.item;

                    this._addEntity(entity);
                }
            });
    }
}

global.BrokerStockForm = BrokerStockForm;
