'use strict';

/**
 * Select2 template for stock info objects.
 *
 * templateResult defines how the selected item will be shown in the select area.
 * templateSelection defines how items will be shown
 *
 * @type {{templateResult: templateResult, templateSelection: templateSelection}}
 */
class Select2StockInfoTemplate {
    constructor(type) {
        this.type = type;
    }

    /**
     * Define how will be render the item result.
     *
     * @param {{loading: boolean, text: string, title: string}} stockInfo An stock info object with text property.
     *
     * @return {*}
     */
    templateResult(stockInfo) {
        if (stockInfo.loading) {
            return stockInfo.text;
        }

        if (stockInfo.title) {
            return "<div class='select2-result-stock-info-" + this.type + " clearfix'>" +
                stockInfo.title +
                "</div>";
        }

        return "<div class='select2-result-stock-info-" + this.type + " clearfix'>" +
            stockInfo.text +
            "</div>";
    }


    /**
     * Define how will be render the item selected.
     *
     * @param {{text: string, title: string}} stockInfo An stock info object with text property.
     *
     * @return {string}
     */
    templateSelection(stockInfo) {
        if (!stockInfo.title) {
            return stockInfo.text;
        }

        return stockInfo.title;
    }
}

export default Select2StockInfoTemplate;
