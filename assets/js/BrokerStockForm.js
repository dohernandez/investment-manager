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

    render() {
        let select2StockTemplate = new Select2StockTemplate();

        let $autocomplete = $('.js-broker-stock-autocomplete');

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
}

global.BrokerStockForm = BrokerStockForm;
