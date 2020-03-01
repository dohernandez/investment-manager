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

const eventBus = require('js-event-bus')();

/**
 * Form manage how the operation form should be build when a crud manager invokes a create or an update action.
 */
class WalletDashboard {
    constructor(
        walletId,
        operationPanel
    ) {
        this.walletId = walletId;

        this.operationPanel = operationPanel;

        this.header = new WalletDashboardHeader();

        eventBus.on("entity_operation_created", this.onOperationCreated.bind(this));
    }

    render() {
        this.operationPanel.render();
    }

    load() {
        this._loadWalletStatistics();
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

    _loadWalletOperations() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_operation_list', {'walletId': this.walletId}),
            'GET'
        ).then((result) => {
            // let operations = result.items;
            console.log(result);
            this.operationPanel.setData(result);
        });
    }

    toggleExpanded() {
        this.operationPanel.toggleExpanded();
    }

    onOperationCreated() {
        this.load()
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

        if (statistic.dividend) {
            $('.js-wallet-dividend').each(function (index, span) {
                $(span).html('<small>' + statistic.dividend.currency.symbol + '</small> ' + statistic.dividend.preciseValue.toFixed(2) + '</span>')
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

global.WalletDashboard = WalletDashboard;
global.OperationForm = OperationForm;

window.eventBus = eventBus;
