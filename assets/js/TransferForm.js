'use strict';

import Form from './Components/Form';
import Select2AccountTemplate from './Components/Select2AccountTemplate';
import moment from 'moment';
import $ from 'jquery';

import 'select2';
import 'eonasdan-bootstrap-datetimepicker';

import './../css/TransferFrom.scss';

/**
 * Form manage how the transfer form should be build when a crud manager invokes a create or an update action.
 */
class TransferForm extends Form {
    constructor(swalFormOptionsText, template = '#js-manager-form-template', selector = '.js-entity-create-from') {
        super(swalFormOptionsText, template, selector);

        this.select2AccountTemplate = new Select2AccountTemplate()
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
}

global.TransferForm = TransferForm;
