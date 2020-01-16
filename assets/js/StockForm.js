'use strict';

import SwalForm from "./Components/SwalForm";
import Select2StockMarketTemplate from './Components/Select2StockMarketTemplate';
import Select2StockInfoTemplate from './Components/Select2StockInfoTemplate';
import Slider from 'bootstrap-slider';
import Routing from './Components/Routing';
import $ from 'jquery';

import 'select2';

import './../css/StockForm.scss';
import './../css/StockView.scss';

const eventBus = require('js-event-bus')();

/**
 * Form manage how the stock form should be build when a crud manager invokes a create or an update action.
 */
class StockForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-table-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;

        this.Select2StockMarketTemplate = new Select2StockMarketTemplate();

        this.Select2StockInfoTypeTemplate = new Select2StockInfoTemplate('type');
        this.Select2StockInfoSectorTemplate = new Select2StockInfoTemplate('sector');
        this.Select2StockInfoIndustryTemplate = new Select2StockInfoTemplate('industry');

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
        // Form use to set data and to read symbol to load values from external sources.
        let $form = $wrapper.find(this.selector);

        $form.find('#symbol').prop("disabled", false);

        // start set data to the form
        if (data) {
            $form.find('#symbol').prop("disabled", true);

            // edition mode
            this._setData($form, data);
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
                    templateResult = this.Select2StockInfoSectorTemplate.templateResult.bind(this.Select2StockInfoSectorTemplate);
                    templateSelection = this.Select2StockInfoSectorTemplate.templateSelection;
                    break;
                case 'industry':
                    templateResult = this.Select2StockInfoIndustryTemplate.templateResult.bind(this.Select2StockInfoIndustryTemplate);
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

        // Load finance.yahoo.com button
        $wrapper.find('button#yahoo_scrape').on(
            'click',
            function (e) {
                e.preventDefault();

                // disable button
                let $button = $(e.currentTarget);
                $button.attr("disabled", true);

                // set button to loading
                let $buttonHtml = $button.html();
                console.log($buttonHtml);
                $button.html('<i class="fas fa-circle-notch fa-spin"></i> Loading...');

                let $symbol = $form.find('input[name="symbol"]');
                let formData = {
                    symbol: $symbol.val()
                };

                new Promise((resolve, reject) => {
                    $.ajax({
                        url: Routing.generate('stock_yahoo_scraper'),
                        method: 'GET',
                        data: formData
                    }).then((data, textStatus, jqXHR) => {
                        resolve(data);
                    }).catch((jqXHR) => {
                        if (jqXHR.status =! 400) {
                            reject(jqXHR);

                            return;
                        }

                        const errorData = JSON.parse(jqXHR.responseText);

                        reject(errorData);
                    });
                }).then((result) => {
                    console.log(result);

                    let stock = result.item;
                    this._setData($form, stock);

                    $button.attr("disabled", false);
                    $button.html($buttonHtml);
                }).catch((errorsData) => {
                    console.log(errorsData);

                    $button.attr("disabled", false);
                    $button.html($buttonHtml);
                });
            }.bind(this)
        );
    }

    /**
     * Set data into form inputs
     *
     * @param $form
     * @param data
     * @private
     */
    _setData($form, data) {
        for (const property in data) {
            let $input = $form.find('#' + property);

            if (property === 'market') {
                let inputData = data[property];
                $input.append(new Option(inputData.symbol + " - " + inputData.name, inputData.id));

                $input.val(inputData.id);

                continue;
            }

            if (property === 'type' || property === 'sector' || property === 'industry') {
                let inputData = data[property];
                if (inputData !== null) {
                    let id = inputData.id ? inputData.id : inputData.title;

                    $input.append(new Option(inputData.title, id));

                    $input.val(id);
                }

                continue;
            }

            if (['value', 'preClose', 'open', 'dayLow', 'dayHigh', 'week52Low', 'week52High']
                .indexOf(property) !== -1) {
                let inputData = data[property];

                if (inputData !== null) {
                    $input.val(inputData.preciseValue);
                }

                continue;
            }

            if (property === 'change') {
                let inputData = data[property];

                $input = $form.find('#lastChangePrice');

                if (inputData !== null) {
                    $input.val(inputData.preciseValue);
                }

                continue;
            }

            $input.val(data[property]);
        }
    }

    /**
     * Callback function when data is preview
     *
     * This method should overwritten by the child class in case the form requires to do an action after data is preview.
     *
     * @param {Object} data
     */
    onBeforeOpenPreview(data) {
        let sliderDay = new Slider('#low-high-day-price', {
            precision: 3
        });
        let sliderWeek = new Slider('#low-high-52-week-price', {
            precision: 3
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

global.StockForm = StockForm;

