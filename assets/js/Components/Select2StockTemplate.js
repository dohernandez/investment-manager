'use strict';

/**
 * Select2 template for stock objects.
 *
 * templateResult defines how the selected item will be shown in the select area.
 * templateSelection defines how items will be shown
 *
 * @type {{templateResult: templateResult, templateSelection: templateSelection}}
 */
class Select2StockTemplate {
    constructor(type) {
        this.type = type;
    }

    /**
     * Define how will be render the item result.
     *
     * @param {{loading: boolean, text: string, title: string}} stock An stock object with text property.
     *
     * @return {*}
     */
    templateResult(stock) {
        if (stock.loading) {
            return stock.text;
        }

        if (stock.title) {
            return "<div class='select2-result-stock clearfix'>" +
                stock.title +
                "</div>";
        }

        return "<div class='select2-result-stock clearfix'>" +
            stock.text +
            "</div>";
    }


    /**
     * Define how will be render the item selected.
     *
     * @param {{text: string, title: string}} stock An stock object with text property.
     *
     * @return {string}
     */
    templateSelection(stock) {
        if (!stock.title) {
            return stock.text;
        }

        return stock.title;
    }
}

export default Select2StockTemplate;
