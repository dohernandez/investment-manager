'use strict';

import SwalForm from './Components/SwalForm';
import Select2BrokerTemplate from "./Components/Select2BrokerTemplate";
import Select2AccountTemplate from "./Components/Select2AccountTemplate";
import $ from 'jquery';

import 'select2';

import './../css/WalletFrom.scss';

const eventBus = require('js-event-bus')();
/**
 * Form manage how the wallet form should be build when a crud manager invokes a create or an update action.
 */
class WalletForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-table-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;

        this.select2BrokerTemplate = new Select2BrokerTemplate();
        this.select2AccountTemplate = new Select2AccountTemplate();

        eventBus.on("entity_created", this.onCreated.bind(this));
        eventBus.on("entity_updated", this.onUpdated.bind(this));
        eventBus.on("entity_deleted", this.onDeleted.bind(this));
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpenEditView(data, $wrapper) {
        if (data) {
            let $form = $wrapper.find(this.selector);
            for (const property in data) {
                let $input = $form.find('#' + property);

                if (property === 'broker') {
                    let inputData = data[property];
                    $input.append(new Option(inputData.title, inputData.id));

                    $input.val(inputData.id);

                    continue;
                }

                $input.val(data[property]);
            }
        }

        let $brokerAutocomplete = $('.js-broker-autocomplete');

        $brokerAutocomplete.each((index, select) => {
            const url = $(select).data('autocomplete-url');

            $(select).select2({
                dropdownParent: $wrapper,
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
                placeholder: 'Search for a broker',
                escapeMarkup: (markup) => markup,
                minimumInputLength: 1,
                templateResult: this.select2BrokerTemplate.templateResult,
                templateSelection: this.select2BrokerTemplate.templateSelection
            });
        });

        let $accountAutocomplete = $('.js-account-autocomplete');

        $accountAutocomplete.each((index, select) => {
            const url = $(select).data('autocomplete-url');

            $(select).select2({
                dropdownParent: $wrapper,
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
                placeholder: 'Search for an account',
                escapeMarkup: (markup) => markup,
                minimumInputLength: 1,
                templateResult: this.select2AccountTemplate.templateResult,
                templateSelection: this.select2AccountTemplate.templateSelection
            });
        });
    }

    onCreated(entity) {
        this.table.addRecord(entity);
    }

    onUpdated(entity, $row) {
        this.table.replaceRecord(entity, entity.id);

        $row.fadeOut('normal', () => {
            $row.replaceWith(this.table.createRow(entity));
        });
    }

    onDeleted(id) {
        this.table.removeRecord(id);
    }
}

global.WalletForm = WalletForm;

