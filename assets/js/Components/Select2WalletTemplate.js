'use strict';

/**
 * Select2 template for wallet objects.
 *
 * templateResult defines how the selected item will be shown in the select area.
 * templateSelection defines how items will be shown
 *
 * @type {{templateResult: templateResult, templateSelection: templateSelection}}
 */
class Select2WalletTemplate {
    constructor(type) {
        this.type = type;
    }

    /**
     * Define how will be render the item result.
     *
     * @param {{loading: boolean, text: string, title: string}} wallet A wallet object with text property.
     *
     * @return {*}
     */
    templateResult(wallet) {
        if (wallet.loading) {
            return wallet.text;
        }

        let text = wallet.text;

        if (wallet.title) {
            text = wallet.title;
        }

        return "<div class='select2-result-wallet clearfix'>" +
            text +
            "</div>";
    }


    /**
     * Define how will be render the item selected.
     *
     * @param {{text: string, title: string}} wallet A wallet object with text property.
     *
     * @return {string}
     */
    templateSelection(wallet) {
        if (!wallet.title) {
            return wallet.text;
        }

        return wallet.title;
    }
}

export default Select2WalletTemplate;
