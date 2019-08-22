'use strict';

import SwalForm from "./Components/SwalForm";
import Select2AccountTemplate from './Components/Select2AccountTemplate';
import moment from 'moment';
import $ from 'jquery';

import 'select2';
import 'eonasdan-bootstrap-datetimepicker';

import './../css/TransferFrom.scss';

const eventBus = require('js-event-bus')();

/**
 * Form manage how the transfer form should be build when a crud manager invokes a create or an update action.
 */
class TransferForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-panel-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.select2AccountTemplate = new Select2AccountTemplate();
        this.table = table;

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
    onBeforeOpen(data, $wrapper) {
        $('[data-datepickerenable="on"]').datetimepicker();

        if (data) {
            let $form = $wrapper.find(this.selector);
            for (const property in data) {
                let $input = $form.find('#' + property);

                if (property === 'date') {
                    $input.val(
                        moment(new Date(data[property])).format('DD/MM/YYYY')
                    ).change();

                    continue;
                }

                if (property === 'beneficiaryParty' || property === 'debtorParty' ) {
                    let inputData = data[property];
                    $input.append(new Option(inputData.name + " - " + inputData.accountNo, inputData.id));

                    $input.val(inputData.id);

                    continue;
                }

                if (property === 'amount') {
                    let inputData = data[property];

                    if (inputData !== null) {
                        $input.val(inputData.preciseValue);
                    }

                    continue;
                }

                $input.val(data[property]);
            }
        }

        let $autocomplete = $('.js-account-autocomplete');

        $autocomplete.each((index, select) => {
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

global.TransferForm = TransferForm;
