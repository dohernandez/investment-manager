'use strict';

import SwalForm from "./Components/SwalForm";
import Select2StockTemplate from "./Components/Select2StockTemplate";
import 'eonasdan-bootstrap-datetimepicker';

const eventBus = require('js-event-bus')();

class OperationForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-table-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;

        this.select2StockTemplate = new Select2StockTemplate();

        this.disableInputInOperations = {
            connectivity: [
                'stock', 'amount', 'price', 'priceChange', 'priceChangeCommission', 'commission'
            ],
            interest: [
                'stock', 'amount', 'price', 'priceChange', 'priceChangeCommission', 'commission'
            ],
            dividend: [
                'amount', 'commission'
            ],
            'split/reverse': [
                'commission', 'price', 'priceChange', 'priceChangeCommission', 'commission', 'value'
            ],
        };

        this.priceChangeCommission = {
            'buy': 0.16,
            'sell': 0.16,
        };

        eventBus.on("entity_operation_created", this.onCreated.bind(this));
    }

    /**
     * @inheritDoc
     */
    onBeforeOpenEditView(data, $wrapper) {
        $('[data-datepickerenable="on"]').datetimepicker();

        let $form = $wrapper.find(this.selector);

        let $type = $form.find('#type');
        let $inputs = $form.find(':input');

        $type.on('change', function(e) {
            e.preventDefault();

            $inputs.each((index, input) => {
                $(input).prop('disabled', false);
            });

            let selected = $type.val();

            if (selected in this.disableInputInOperations) {
                let $input = $form.find('#priceChangeCommission');
                $input.val('');

                let operation = this.disableInputInOperations[selected];

                for (let i = 0; i < operation.length; i++) {
                    let $input = $form.find('#' + operation[i]);
                    $input.prop('disabled', true);
                }
            }

            if (selected in this.priceChangeCommission) {
                let $input = $form.find('#priceChangeCommission');
                $input.val(this.priceChangeCommission[selected]);
            }
        }.bind(this));

        if (data) {
            for (const property in data) {
                let $input = $form.find('#' + property);

                // setting date pickerenable property
                if (property === 'type') {
                    $input.val(data[property]).change();

                    continue;
                }

                // setting date pickerenable property
                if (property === 'dateAt') {
                    $input.val(
                        moment(new Date(data[property])).format('DD/MM/YYYY')
                    ).change();

                    continue;
                }

                // setting select2 autocomplete property
                if (property === 'stock') {
                    let inputData = data[property];
                    $input.append(new Option(inputData.title, inputData.id));

                    $input.val(inputData.id);

                    continue;
                }

                $input.val(data[property]);
            }
        }

        let $autocomplete = $('.js-stock-autocomplete');

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
                    processResults: (data, params) => {
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
                templateResult: this.select2StockTemplate.templateResult,
                templateSelection: this.select2StockTemplate.templateSelection
            });
        });
    }

    onCreated(entity) {
        if (this.table) {
            this.table.addRecord(entity);
        }
    }
}

export default OperationForm;
