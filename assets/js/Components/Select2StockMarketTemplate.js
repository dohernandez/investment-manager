'use strict';

/**
 * Select2 template for stock market objects.
 *
 * templateResult defines how the selected item will be shown in the select area.
 * templateSelection defines how items will be shown
 *
 * @type {{templateResult: templateResult, templateSelection: templateSelection}}
 */
class Select2StockMarketTemplate {
    /**
     * Define how will be render the item result.
     *
     * @param {{loading: boolean, text: string, title: string}} stockMarket An stock market object with text property.
     *
     * @return {*}
     */
    templateResult(stockMarket) {
        if (stockMarket.loading) {
            return stockMarket.text;
        }

        let markup = "<div class='select2-result-stock-market clearfix'>" +
            stockMarket.title +
            "</div>";

        return markup;
    }


    /**
     * Define how will be render the item selected.
     *
     * @param {{text: string, title: string}} stockMarket An stock market object with text property.
     *
     * @return {string}
     */
    templateSelection(stockMarket) {
        if (!stockMarket.title) {
            return stockMarket.text;
        }

        return stockMarket.title;
    }
}

export default Select2StockMarketTemplate;
