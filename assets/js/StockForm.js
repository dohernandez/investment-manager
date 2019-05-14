'use strict';

import Form from './Components/Form';
import Select2StockMarketTemplate from './Components/Select2StockMarketTemplate';
import $ from 'jquery';

import 'select2';

import './../css/TransferFrom.scss';

/**
 * Form manage how the stock form should be build when a crud manager invokes a create or an update action.
 */
class StockForm extends Form {
    constructor(swalFormOptionsText, template = '#js-manager-form-template', selector = '.js-entity-create-from') {
        super(swalFormOptionsText, template, selector);

        this.Select2StockMarketTemplate = new Select2StockMarketTemplate();
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

                if (property === 'market') {
                    let inputData = data[property];
                    $input.append(new Option(inputData.symbol + " - " + inputData.name, inputData.id));

                    $input.val(inputData.id);

                    continue;
                }

                $input.val(data[property]);
            }
        }

        let $autocomplete = $('.js-stock-market-autocomplete');

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
                placeholder: 'Search for a market',
                escapeMarkup: (markup) => markup,
                minimumInputLength: 1,
                templateResult: this.Select2StockMarketTemplate.templateResult,
                templateSelection: this.Select2StockMarketTemplate.templateSelection
            });
        });
    }
}

global.StockForm = StockForm;

