'use strict';

import Form from './Components/Form';
import Select2StockMarketTemplate from './Components/Select2StockMarketTemplate';
import Select2StockInfoTemplate from './Components/Select2StockInfoTemplate';
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

        this.Select2StockInfoTypeTemplate = new Select2StockInfoTemplate('type');
        this.Select2StockInfoSectorTemplate = new Select2StockInfoTemplate('sector');
        this.Select2StockInfoIndustryTemplate = new Select2StockInfoTemplate('industry');
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpen(data, $wrapper) {
        // start set data to the form
        if (data) {
            // edition mode
            let $form = $wrapper.find(this.selector);
            for (const property in data) {
                let $input = $form.find('#' + property);

                if (property === 'market') {
                    let inputData = data[property];
                    $input.append(new Option(inputData.symbol + " - " + inputData.name, inputData.id));

                    $input.val(inputData.id);

                    continue;
                }

                if (property === 'type') {
                    let inputData = data[property];
                    if (inputData !== null) {
                        $input.append(new Option(inputData.title, inputData.id));

                        $input.val(inputData.id);
                    }

                    continue;
                }

                $input.val(data[property]);
            }
        }
        // end set data to the form

        // market dropdown
        let $market = $('.js-stock-market-autocomplete');
        const url = $market.data('autocomplete-url');

        $market.select2({
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

        // stock info dropdown
        let $stockInfos = $('.js-stock-info-autocomplete');

        $stockInfos.each((index, select) => {
            let $stockInfo = $(select);
            let type = $stockInfo.data('type');

            let templateResult = null;
            let templateSelection = null;

            switch (type) {
                case 'type':
                    templateResult = this.Select2StockInfoTypeTemplate.templateResult.bind(this.Select2StockInfoTypeTemplate);
                    templateSelection = this.Select2StockInfoTypeTemplate.templateSelection;
                    break;
                case 'sector':
                    templateResult = this.Select2StockInfoSectorTemplate.templateResult;
                    templateSelection = this.Select2StockInfoSectorTemplate.templateSelection;
                    break;
                case 'industry':
                    templateResult = this.Select2StockInfoIndustryTemplate.templateResult;
                    templateSelection = this.Select2StockInfoIndustryTemplate.templateSelection;
                    break;
            }

            const url = $stockInfo.data('autocomplete-url');

            $stockInfo.select2({
                dropdownParent: $wrapper,
                ajax: {
                    url,
                    dataType: 'json',
                    delay: 10,
                    allowClear: true,
                    data: (params) => {
                        return {
                            t: type, // type term
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
                placeholder: 'Search for a ' + type,
                escapeMarkup: (markup) => markup,
                minimumInputLength: 1,
                templateResult: templateResult,
                templateSelection: templateSelection,
                tags: true,
            });
        });
    }
}

global.StockForm = StockForm;

