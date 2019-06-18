'use strict';

import Form from './Components/Form';
import Select2StockTemplate from './Components/Select2StockTemplate';
import $ from 'jquery';
import moment from 'moment';
import Routing from './Components/Routing';
import InvestmentManagerClient from './Components/InvestmentManagerClient';

import 'select2';
import 'eonasdan-bootstrap-datetimepicker';

import './../css/Select2.scss';
import './../css/WalletDashboard.scss';

/**
 * Form manage how the operation form should be build when a crud manager invokes a create or an update action.
 */
class OperationForm extends Form {
    constructor(
        positionCrudManager,
        walletInfo,
        swalFormOptionsText,
        template = '#js-manager-form-template',
        selector = '.js-entity-create-from'
    ) {
        super(swalFormOptionsText, template, selector);

        this.walletInfo = walletInfo;
        this.positionCrudManager = positionCrudManager;

        this.select2StockTemplate = new Select2StockTemplate();

        this.operations = {
            connectivity: [
                'stock', 'amount', 'price', 'priceChange', 'priceChangeCommission', 'commission'
            ],
            interest: [
                'stock', 'amount', 'price', 'priceChange', 'priceChangeCommission', 'commission'
            ],
            dividend: [
                'amount', 'commission'
            ],
        }
    }

    /**
     * @inheritDoc
     */
    onBeforeOpen(data, $wrapper) {
        $('[data-datepickerenable="on"]').datetimepicker();

        let $form = $wrapper.find(this.selector);

        if (data) {
            for (const property in data) {
                let $input = $form.find('#' + property);

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

        let $type = $form.find('#type');
        let $inputs = $form.find(':input');

        $type.on('change', function(e) {
            e.preventDefault();

            $inputs.each((index, input) => {
                $(input).prop('disabled', false);
            });

            let selected = $type.val();

            if (selected in this.operations) {
                let operation = this.operations[selected];

                for (let i = 0; i < operation.length; i++) {
                    let $input = $form.find('#' + operation[i]);
                    $input.prop('disabled', true);
                }
            }
        }.bind(this));
    }

    /**
     * @inheritDoc
     */
    onCreated(data) {
        // refresh position table.
        this.positionCrudManager.loadRows();

        this.walletInfo.load();
    }
}

global.OperationForm = OperationForm;

class WalletInfo {
    constructor(walletId) {
        this.walletId = walletId;
    }

    /**
     * Load wallet from server and update header dashboard info.
     */
    load() {
        InvestmentManagerClient.sendRPC(
            Routing.generate('wallet_get', {'id': this.walletId}),
            'GET'
        ).then((result) => {
            console.log(result.item);

            let wallet = result.item;

            $('.js-wallet-invested').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.invested.toFixed(2) + '</span>')
            });
            $('.js-wallet-net-capital').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.netCapital.toFixed(2) + '</span>')
            });
            $('.js-wallet-dividend').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.dividend.toFixed(2) + '</span>')
            });
            $('.js-wallet-benefits').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.benefits.toFixed(2) + '</span>')
            });
            $('.js-wallet-capital').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.capital.toFixed(2) + '</span>')
            });
            $('.js-wallet-margin').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.capital.toFixed(2) + '</span>')
            });
            $('.js-wallet-funds').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.funds.toFixed(2) + '</span>')
            });
            $('.js-wallet-commissions').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.commissions.toFixed(2) + '</span>')
            });
            $('.js-wallet-interest').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.interest.toFixed(2) + '</span>')
            });
            $('.js-wallet-connection').each(function (index, span) {
                $(span).html('<small>&euro;</small> ' + wallet.connection.toFixed(2) + '</span>')
            });
        }).catch((errorsData) => {
            console.log(errorsData);
        });
    }
}

global.WalletInfo = WalletInfo;
