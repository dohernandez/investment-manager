'use strict';

import $ from 'jquery';
import Routing from './Components/Routing';
import InvestmentManagerClient from './Components/InvestmentManagerClient';
import 'canvasjs/dist/canvasjs';

import 'select2';
import 'eonasdan-bootstrap-datetimepicker';

import './../css/Select2.scss';
import './../css/WalletDashboard.scss';

const eventBus = require('js-event-bus')();

/**
 * Form manage how the operation form should be build when a crud manager invokes a create or an update action.
 */
class WalletDashboard2 {
    constructor(
        walletId
    ) {
        this.walletId = walletId;

        this.header = new WalletDashboardHeader();
    }

    render() {
    }

    load() {
        this._loadWalletStatistics();
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

global.WalletDashboard = WalletDashboard2;

window.eventBus = eventBus;
