'use strict';

import SwalForm from "./Components/SwalForm";
import StockNoteForm from "./StockNoteForm";
import Select2StockTemplate from './Components/Select2StockTemplate';
import $ from 'jquery';
import moment from 'moment';
import Routing from './Components/Routing';
import InvestmentManagerClient from './Components/InvestmentManagerClient';
import RowButton from "./Components/RowButton";
import 'canvasjs/dist/canvasjs';

import 'select2';
import 'eonasdan-bootstrap-datetimepicker';

import './../css/Select2.scss';
import './../css/WalletDashboard.scss';

const eventBus = require('js-event-bus')();

/**
 * Form manage how the operation form should be build when a crud manager invokes a create or an update action.
 */
class WalletDashboard {
    constructor(
        walletId,
        positionPanel,
        dividendPanel,
        operationPanel,
        comingDividendsPanel,
        toPayDividendsPanel
    ) {
        this.walletId = walletId;

        this.positionPanel = positionPanel;
        this.dividendPanel = dividendPanel;
        this.operationPanel = operationPanel;
        this.comingDividendsPanel = comingDividendsPanel;
        this.toPayDividendsPanel = toPayDividendsPanel;

        this.header = new WalletDashboardHeader();
        this.dividenProjected = new WalletDashboardDividendProjected();
        this.dividenPaidChart = new DividendPaidChart();
        this.dividendStockChart = new DividendStockChart();

        eventBus.on("entity_operation_created", this.onOperationCreated.bind(this));

        eventBus.on("position_searched", this.onPositionSearched.bind(this));
        eventBus.on("position_search_cleaned", this.onPositionSearchCleaned.bind(this));

        eventBus.on("entity_position_dividend_updated", this.onPositionDividendUpdated.bind(this));
    }

    render() {
        this.positionPanel.render();
        this.dividendPanel.render();
        this.operationPanel.render();
        this.comingDividendsPanel.render();
        this.toPayDividendsPanel.render();
    }

    load() {
        this._loadWallet();
        this._loadPositions();
        this._loadOperations();
    }

    _loadWallet() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_get', {'id': this.walletId}),
            'GET'
        ).then((result) => {
            let wallet = result.item;

            this.header.setData(wallet);
            this.dividenProjected.setData(wallet);
            this.dividenPaidChart.setData(wallet);
            this.dividendStockChart.setWalletData(wallet);
        });
    }

    _loadPositions() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_position_list', {'_id': this.walletId, 's': 'open'}),
            'GET'
        ).then((result) => {
            this.positionPanel.setData(result);
            this.dividendPanel.setData(result);
            this.comingDividendsPanel.setData(result);
            this.toPayDividendsPanel.setData(result);
            this.dividendStockChart.setPositionsData(result.items);
        });
    }

    _loadOperations() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_operation_list', {'_id': this.walletId}),
            'GET'
        ).then((result) => {
            this.operationPanel.setData(result);
        });
    }

    onOperationCreated() {
        this._loadWallet();
        this._loadPositions();
    }

    onPositionSearched(search) {
        this.dividendPanel.search(search);
        this.operationPanel.search(search);
    }

    onPositionSearchCleaned() {
        this.dividendPanel.cleanSearch();
        this.operationPanel.cleanSearch();
    }

    toggleExpanded() {
        this.positionPanel.toggleExpanded();
        this.dividendPanel.toggleExpanded();
        this.comingDividendsPanel.toggleExpanded();
        this.toPayDividendsPanel.toggleExpanded();
        this.operationPanel.toggleExpanded();

        this.dividenPaidChart.toggleExpanded();
    }

    onPositionDividendUpdated() {
        this._loadWallet();
    }
}

class WalletDashboardHeader {
    setData(wallet) {
        // Dashboard
        $('.js-wallet-invested').each(function (index, span) {
            $(span).html('<small>' + wallet.invested.currency.symbol + '</small> ' + wallet.invested.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-net-capital').each(function (index, span) {
            $(span).html('<small>' + wallet.netCapital.currency.symbol + '</small> ' + wallet.netCapital.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-dividend').each(function (index, span) {
            $(span).html('<small>' + wallet.dividend.currency.symbol + '</small> ' + wallet.dividend.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-benefits').each(function (index, span) {
            $(span).html('<small>' + wallet.benefits.currency.symbol + '</small> ' + wallet.benefits.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-pbenefits').each(function (index, span) {
            $(span).html(wallet.pBenefits.toFixed(2) + '%')
        });
        $('.js-wallet-capital').each(function (index, span) {
            $(span).html('<small>' + wallet.capital.currency.symbol + '</small> ' + wallet.capital.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-margin').each(function (index, span) {
            $(span).html('<small>' + wallet.margin.currency.symbol + '</small> ' + wallet.margin.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-funds').each(function (index, span) {
            $(span).html('<small>' + wallet.funds.currency.symbol + '</small> ' + wallet.funds.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-commissions').each(function (index, span) {
            $(span).html('<small>' + wallet.commissions.currency.symbol + '</small> ' + wallet.commissions.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-interest').each(function (index, span) {
            $(span).html('<small>' + wallet.interest.currency.symbol + '</small> ' + wallet.interest.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-connection').each(function (index, span) {
            $(span).html('<small>' + wallet.connection.currency.symbol + '</small> ' + wallet.connection.preciseValue.toFixed(2) + '</span>')
        });
    }
}

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
        }

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
                let operation = this.disableInputInOperations[selected];

                for (let i = 0; i < operation.length; i++) {
                    let $input = $form.find('#' + operation[i]);
                    $input.prop('disabled', true);
                }
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
        this.table.addRecord(entity);
    }
}

class PositionOperationRowButton extends RowButton {
    /**
     *
     * @param form {Object}
     * @param swalOptions {Object}
     * @param type {string}
     * @param url {string}
     * @param eventName {string}
     */
    constructor(form, swalOptions, type, url, eventName) {
        super('.js-position-' + type, function (e) {
            e.preventDefault();

            // find entity to edit
            const $row = $(e.currentTarget).closest('tr');
            const id = $row.data('id');

            let entity = this.table.getRecord(id);
            let amount = entity.amount;

            entity.type = type;
            if (type === 'buy') {
                entity.amount = '';
            }

            return form.display(swalOptions, url, 'create', entity)
                .then((result) => {
                    if (result.value) {
                        let entity = result.value.item;

                        eventBus.emit(eventName, null, entity);
                    } else if (
                        /* Read more about handling dismissals below */
                        result.dismiss === 'cancel' || result.dismiss === 'close'
                    ) {
                        entity.amount = amount;
                    }
                });
        });
    }
}

class WalletDashboardDividendProjected {
    setData(wallet) {
        $('.js-wallet-dividend-projected').each(function (index, span) {
            // $(span).html('<small>' + wallet.dividendProjected.currency.symbol + '</small> ' + wallet.dividendProjected.preciseValue.toFixed(2) + '</span>')
            $(span).html('<small>' + wallet.displayDividendProjectedYield + '</span>')
        });
        $('.js-wallet-dividend-increase').each(function (index, span) {
            $(span).html(wallet.dividendProjectedIncrease.toFixed(2))
        });
        $('.js-wallet-dividend-increase-bar').each(function (index, span) {
            // console.log($(span), $(span).val());
            $(span).css('width', wallet.dividendProjectedIncrease.toFixed(2))
        });

        let year = null;
        $('.js-wallet-dividend-year').each(function (index, span) {
            year = $(span).data('year');
        });

        let previousYear = null;
        $('.js-wallet-dividend-previous-year').each(function (index, span) {
            previousYear = $(span).data('year');
        });

        $('.js-wallet-dividend-month').each(function (index, span) {
            let month = $(span).data('month');

            let mIdx = index + 1;

            $('.js-wallet-dividend-year-month-' + mIdx).each(function (index, span) {
                let dividend = wallet.dividendProjectedMonths[year][month];
                $(span).html('<small>' + dividend.currency.symbol + '</small> ' + dividend.preciseValue.toFixed(2) + '</span>')
            });

            $('.js-wallet-dividend-previous-month-' + mIdx).each(function (index, span) {
                let dividend = wallet.dividendProjectedMonths[previousYear][month];
                $(span).html('<small>' + dividend.currency.symbol + '</small> ' + dividend.preciseValue.toFixed(2) + '</span>')
            });
        });
    }
}

class DividendPaidChart {
    constructor() {
        this.chart = new CanvasJS.Chart("dividend-paid-chart-container", {
            animationEnabled: true,
            title:{
                text: "Dividends earned years, months"
            },
            axisY: {
                title: "Euros",
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor:"pointer",
                itemclick: this.toggleDataSeries.bind(this)
            },
        });
    }

    setData(wallet) {
        const dividendValue = function(dividend) {
            if (!dividend) {
                return 0;
            }

            if (!dividend.paid) {
                return 0;
            }

            return dividend.paid.preciseValue;
        };

        let data = [];

        $.each(wallet.metadata.dividends, function (index, dividend) {
            data.push({
                type: "column",
                name: index,
                legendText: index,
                showInLegend: true,
                dataPoints:[
                    { label: "Jan", y: dividendValue(dividend.months[1]) },
                    { label: "Feb", y: dividendValue(dividend.months[2]) },
                    { label: "Mar", y: dividendValue(dividend.months[3]) },
                    { label: "Apr", y: dividendValue(dividend.months[4]) },
                    { label: "May", y: dividendValue(dividend.months[5]) },
                    { label: "Jun", y: dividendValue(dividend.months[6]) },
                    { label: "Jul", y: dividendValue(dividend.months[7]) },
                    { label: "Aug", y: dividendValue(dividend.months[8]) },
                    { label: "Sep", y: dividendValue(dividend.months[9]) },
                    { label: "Oct", y: dividendValue(dividend.months[10]) },
                    { label: "Nov", y: dividendValue(dividend.months[11]) },
                    { label: "Dec", y: dividendValue(dividend.months[12]) },
                ]
            });
        });

        this.chart.options.data = data;

        this.chart.render();
    }

    toggleDataSeries(e) {
        e.dataSeries.visible = !(typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible);

        this.chart.render();
    }

    toggleExpanded() {
        this.chart.render(true);
    }
}

class PositionDividendForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-panel-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;

        eventBus.on("entity_position_dividend_updated", this.onUpdated.bind(this));
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpenEditView(data, $wrapper) {
        let $form = $wrapper.find(this.selector);

        let $controlGroup = $form.find('.box-body').children('div').first();

        let $group = $(
            '<div class="form-group">' +
                '<div class="col-sm-3">' +
                    '<label class="control-label" for="stockName">Stock</label>'+
                '</div>'+
                '<div class="input-group">'+
                    data.stock.name + ' (' + data.stock.symbol +':' + data.stock.market.symbol +')' +
                '</div>'+
            '</div>'
        );

        $controlGroup.prepend($group);

        $controlGroup.find('.form-group').each(function (index, group) {
            let $label = $(group).children('div').first();
            $label.addClass('col-sm-4');
            $label.removeClass('col-sm-3');

            $label = $(group).children('div').last();
            $label.addClass('col-sm-8');
            $label.removeClass('col-sm-9');
        });

        if (data) {
            for (const property in data) {
                let $input = $form.find('#' + property);

                if ('dividendRetention' == property) {
                    let inputData = data[property];

                    if (inputData !== null) {
                        $input.val(inputData.preciseValue);
                    }

                    continue;
                }

                $input.val(data[property]);
            }
        }
    }

    onUpdated(entity, $row) {
        this.table.replaceRecord(entity, entity.id);

        $row.fadeOut('normal', () => {
            $row.replaceWith(this.table.createRow(entity));
        });
    }
}

class PositionDividendRowButton extends RowButton {
    /**
     *
     * @param form {Object}
     * @param swalOptions {Object}
     * @param selector {string}
     * @param url {function}
     */
    constructor(form, swalOptions, selector, url) {
        super(selector, function (e) {
            e.preventDefault();

            // find entity to edit
            const $row = $(e.currentTarget).closest('tr');
            const id = $row.data('id');

            let entity = this.table.getRecord(id);

            return form.display(swalOptions, url(id), 'PATCH', entity)
                .then((result) => {
                    console.log('TODO: update the dashboard.', result);
                });
        });
    }
}

class PositionStockNoteForm extends StockNoteForm {
    onBeforeOpenEditView(data, $wrapper) {
        let $form = $wrapper.find(this.selector);
        let $content = $form.parent();

        $content.prepend($('<span>' + data['title'] + '</span>'));

        super.onBeforeOpenEditView(data, $wrapper);
    }

    display(swalOptions, url, method, entity) {
        let stock = entity.stock;

        return super.display(swalOptions, url, method, stock)
    }

    onUpdated(entity, $row) {
        const id = $row.data('id');

        let position = this.table.getRecord(id);
        position.stock = entity;

        super.onUpdated(position, $row);
    }
}

class DividendStockChart {
    constructor() {
        this.chart = new CanvasJS.Chart("dividend-stock-chart-container", {
            exportEnabled: true,
            animationEnabled: true,
            title:{
                text: "Invested"
            },
            legend: {
                cursor:"pointer",
                itemclick: DividendStockChart.explodePie
            },
        });

        this.walletData = null;
        this.positionsData = null;
    }

    setWalletData(wallet) {
        this.walletData = wallet;

        if (this.walletData !== null && this.positionsData !== null) {
            this.setData(this.walletData, this.positionsData);
        }
    }

    setPositionsData(positions) {
        this.positionsData = positions;

        if (this.walletData !== null && this.positionsData !== null) {
            this.setData(this.walletData, this.positionsData);
        }
    }

    setData(wallet, positions) {
        let totalInvested = wallet.invested.value;
        if (wallet.funds.value < 0){
            totalInvested += wallet.funds.value * -1
        }

        let withoutDividendInvested = 0;
        let withDividendInvested = 0;

        $.each(positions, function (index, position) {
            if (position.dividend.value > 0){
                withDividendInvested += position.invested.value;
            } else {
                withoutDividendInvested += position.invested.value;
            }
        });

        console.log("totalInvested" + totalInvested);
        console.log("withDividendInvested" + withDividendInvested);
        console.log("withoutDividendInvested" + withoutDividendInvested);
        console.log("sum" + (withDividendInvested + withoutDividendInvested));

        this.chart.options.data = [{
            type: "pie",
            showInLegend: true,
            toolTipContent: "{name}: <strong>{y}%</strong>",
            indexLabel: "{name} - {y}%",
            dataPoints: [
                { y: (withDividendInvested * 100 / totalInvested).toFixed(2), name: "stocks with dividend", exploded: true },
                { y: (withoutDividendInvested * 100 / totalInvested).toFixed(2), name: "stocks without dividend" },
            ]
        }];

        this.chart.render();
    }

    static explodePie(e) {
        e.dataSeries.dataPoints[e.dataPointIndex].exploded = typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded;

        e.chart.render();

    }
}

global.WalletDashboard = WalletDashboard;
global.OperationForm = OperationForm;
global.PositionOperationRowButton = PositionOperationRowButton;
global.PositionDividendRowButton = PositionDividendRowButton;
global.PositionDividendForm = PositionDividendForm;
global.PositionStockNoteForm = PositionStockNoteForm;

window.eventBus = eventBus;
