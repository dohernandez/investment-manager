'use strict';

import SwalForm from "./Components/SwalForm";
import $ from 'jquery';

import 'select2';
import 'eonasdan-bootstrap-datetimepicker';

import './../css/StockDividendFrom.scss';

const eventBus = require('js-event-bus')();

/**
 * Form manage how the stock form should be build when a crud manager invokes a create or an update action.
 */
class StockDividendForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-table-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

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
        $('[data-datepickerenable="on"]').datetimepicker();

        // start set data to the form
        if (data) {
            // edition mode
            let $form = $wrapper.find(this.selector);
            for (const property in data) {
                let $input = $form.find('#' + property);

                if (property === 'exDate' || property === 'paymentDate' || property === 'recordDate') {
                    if (data[property]) {
                        $input.val(
                            moment(new Date(data[property])).format('DD/MM/YYYY')
                        ).change();

                        continue;
                    }
                }

                $input.val(data[property]);
            }
        }
        // end set data to the form

        // // market dropdown
        // let $market = $('.js-stock-market-autocomplete');
        // const url = $market.data('autocomplete-url');
        //
        // $market.select2({
        //     dropdownParent: $wrapper,
        //     ajax: {
        //         url,
        //         dataType: 'json',
        //         delay: 10,
        //         allowClear: true,
        //         data: (params) => {
        //             return {
        //                 q: params.term, // search term
        //                 page: params.page
        //             };
        //         },
        //         processResults: (data, params)=> {
        //             // parse the results into the format expected by Select2
        //             // since we are using custom formatting functions we do not need to
        //             // alter the remote JSON data, except to indicate that infinite
        //             // scrolling can be used
        //             params.page = params.page || 1;
        //
        //             return {
        //                 results: data.items,
        //                 pagination: {
        //                     more: (params.page * 30) < data.total_count
        //                 }
        //             };
        //         },
        //         cache: true
        //     },
        //     placeholder: 'Search for a market',
        //     escapeMarkup: (markup) => markup,
        //     minimumInputLength: 1,
        //     templateResult: this.Select2StockMarketTemplate.templateResult,
        //     templateSelection: this.Select2StockMarketTemplate.templateSelection
        // });
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

global.StockDividendForm = StockDividendForm;

