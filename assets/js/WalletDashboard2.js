'use strict';

import $ from 'jquery';
import Routing from './Components/Routing';
import InvestmentManagerClient from './Components/InvestmentManagerClient';
import 'canvasjs/dist/canvasjs';
import 'select2';
import 'eonasdan-bootstrap-datetimepicker';
import './../css/Select2.scss';
import './../css/WalletDashboard.scss';
import SwalForm from "./Components/SwalForm";
import Select2StockTemplate from "./Components/Select2StockTemplate";
import RowButton from "./Components/RowButton";
import OperationForm from "./OperationForm";

const eventBus = require('js-event-bus')();

/**
 * Form manage how the operation form should be build when a crud manager invokes a create or an update action.
 */
class WalletDashboard {
    constructor(
        walletId,
        positionPanel,
        positionDividendPanel,
        positionComingDividendPanel,
        positionToPayDividendPanel,
        operationPanel
    ) {
        this.walletId = walletId;

        this.positionPanel = positionPanel;
        this.positionDividendPanel = new WrapperPositionDividendPanel(
            positionDividendPanel,
            positionComingDividendPanel,
            positionToPayDividendPanel
        );
        this.operationPanel = operationPanel;

        this.header = new WalletDashboardHeader();

        eventBus.on("entity_operation_created", this.onOperationCreated.bind(this));
        eventBus.on("entity_position_dividend_updated", this.onPositionDividendUpdated.bind(this));

        eventBus.on("position_searched", this.onPositionSearched.bind(this));
        eventBus.on("position_search_cleaned", this.onPositionSearchCleaned.bind(this));
    }

    render() {
        this.positionPanel.render();
        this.positionDividendPanel.render();
        this.operationPanel.render();
    }

    load() {
        this._loadWalletStatistics();
        this._loadWalletPositions();
        this._loadWalletPositionsDividends();
        this._loadWalletOperations();
    }

    _loadWalletStatistics() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_statistics', {'id': this.walletId}),
            'GET'
        ).then((result) => {
            let statistic = result.item;

            this.header.setData(statistic);
        });
    }

    _loadWalletPositions() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_position_list', {'walletId': this.walletId, 's': 'open'}),
            'GET'
        ).then((result) => {
            // let positions = result.items;
            this.positionPanel.setData(result);
        });
    }

    _loadWalletPositionsDividends() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_position_dividend_list', {'walletId': this.walletId, 's': 'open'}),
            'GET'
        ).then((result) => {
            // let positions = result.items;
            this.positionDividendPanel.setData(result);
        });
    }

    _loadWalletOperations() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_operation_list', {'walletId': this.walletId}),
            'GET'
        ).then((result) => {
            // console.log(result);
            this.operationPanel.setData(result);
        });
    }

    toggleExpanded() {
        this.positionPanel.toggleExpanded();
        this.positionDividendPanel.toggleExpanded();
        this.operationPanel.toggleExpanded();
    }

    onOperationCreated() {
        this._loadWalletStatistics();
        this._loadWalletPositions();
        this._loadWalletPositionsDividends();
    }

    onPositionDividendUpdated() {
        this._loadWalletPositionsDividends();
    }

    onPositionSearched(search) {
        this.positionDividendPanel.search(search);
        this.operationPanel.search(search);
    }

    onPositionSearchCleaned() {
        this.positionDividendPanel.cleanSearch();
        this.operationPanel.cleanSearch();
    }
}

class WalletDashboardHeader {
    setData(statistic) {
        $('.js-wallet-pbenefits-box').each(function (index, box) {
            $(box).removeClass('bg-aqua');

            if (statistic.benefits.value > 0) {
                $(box).addClass('bg-green');
            } else if (statistic.benefits.value < 0) {
                $(box).addClass('bg-red');
            }
        });

        $('.js-wallet-invested').each(function (index, span) {
            $(span).html('<small>' + statistic.invested.currency.symbol + '</small> ' + statistic.invested.preciseValue.toFixed(2) + '</span>')
        });
        $('.js-wallet-net-capital').each(function (index, span) {
            $(span).html('<small>' + statistic.netCapital.currency.symbol + '</small> ' + statistic.netCapital.preciseValue.toFixed(2) + '</span>')
        });

        if (statistic.dividends) {
            $('.js-wallet-dividend').each(function (index, span) {
                $(span).html('<small>' + statistic.dividends.currency.symbol + '</small> ' + statistic.dividends.preciseValue.toFixed(2) + '</span>')
            });
        }

        if (statistic.benefits) {
            $('.js-wallet-benefits').each(function (index, span) {
                $(span).html('<small>' + statistic.benefits.currency.symbol + '</small> ' + statistic.benefits.preciseValue.toFixed(2) + '</span>')
            });
        }

        if (statistic.pBenefits) {
            $('.js-wallet-pbenefits').each(function (index, span) {
                $(span).html(statistic.pBenefits.toFixed(2) + '%')
            });
        }

        $('.js-wallet-capital').each(function (index, span) {
            $(span).html('<small>' + statistic.capital.currency.symbol + '</small> ' + statistic.capital.preciseValue.toFixed(2) + '</span>')
        });

        if (statistic.margin) {
            $('.js-wallet-margin').each(function (index, span) {
                $(span).html('<small>' + statistic.margin.currency.symbol + '</small> ' + statistic.margin.preciseValue.toFixed(2) + '</span>')
            });
        }

        $('.js-wallet-funds').each(function (index, span) {
            $(span).html('<small>' + statistic.funds.currency.symbol + '</small> ' + statistic.funds.preciseValue.toFixed(2) + '</span>')
        });

        if (statistic.commissions) {
            $('.js-wallet-commissions').each(function (index, span) {
                $(span).html('<small>' + statistic.commissions.currency.symbol + '</small> ' + statistic.commissions.preciseValue.toFixed(2) + '</span>')
            });
        }

        if (statistic.interest) {
            $('.js-wallet-interest').each(function (index, span) {
                $(span).html('<small>' + statistic.interest.currency.symbol + '</small> ' + statistic.interest.preciseValue.toFixed(2) + '</span>')
            });
        }

        if (statistic.connection) {
            $('.js-wallet-connection').each(function (index, span) {
                $(span).html('<small>' + statistic.connection.currency.symbol + '</small> ' + statistic.connection.preciseValue.toFixed(2) + '</span>')
            });
        }
    }
}

class WrapperPositionDividendPanel {
    constructor(
        dividendPanel,
        positionComingDividendPanel,
        toPayDividendPanel
    ) {
        this.dividendPanel = dividendPanel;
        this.positionComingDividendPanel = positionComingDividendPanel;
        this.toPayDividendPanel = toPayDividendPanel;
    }

    render() {
        this.dividendPanel.render();
        this.positionComingDividendPanel.render();
        this.toPayDividendPanel.render();
    }

    setData(result) {
        this.dividendPanel.setData(result);

        const today = new Date();
        let comingDividend = {'items': result.items.filter(function (position) {
            return position.exDate !== null &&  new Date(position.exDate) >= today;
        })};
        this.positionComingDividendPanel.setData(comingDividend);

        let toPayDividend = {'items': result.items.filter(function (position) {
            return position.toPayDate !== null &&  new Date(position.toPayDate) >= today;
        })};
        this.toPayDividendPanel.setData(toPayDividend);
    }

    toggleExpanded() {
        this.dividendPanel.toggleExpanded();
        this.positionComingDividendPanel.toggleExpanded();
        this.toPayDividendPanel.toggleExpanded();
    }

    search(search) {
        this.dividendPanel.search(search);
        this.positionComingDividendPanel.search(search);
        this.toPayDividendPanel.search(search);
    }

    cleanSearch() {
        this.dividendPanel.cleanSearch();
        this.positionComingDividendPanel.cleanSearch();
        this.toPayDividendPanel.cleanSearch();
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
            if (type === 'buy' || type == 'dividend') {
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
            console.log(data);
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

    onUpdated(entity) {
        this.table.replaceRecord(entity, entity.id);
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
                    if (result.value) {
                        let entity = result.value.item;

                        eventBus.emit('entity_position_dividend_updated', null, entity);
                    }
                });
        });
    }
}

global.WalletDashboard = WalletDashboard;
global.PositionOperationRowButton = PositionOperationRowButton;
global.PositionDividendForm = PositionDividendForm;
global.PositionDividendRowButton = PositionDividendRowButton;
global.OperationForm = OperationForm;

window.eventBus = eventBus;
