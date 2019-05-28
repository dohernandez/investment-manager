'use strict';

/**
 * Select2 template for account objects.
 *
 * templateResult defines how the selected item will be shown in the select area.
 * templateSelection defines how items will be shown
 *
 * @type {{templateResult: templateResult, templateSelection: templateSelection}}
 */
class Select2BrokerTemplate {
    /**
     * Define how will be render the item result.
     *
     * @param {{loading: boolean, text: string, name: string, account: {accountNo: string}}} broker A broker object with text property.
     *
     * @return {*}
     */
    templateResult(broker) {
        if (broker.loading) {
            return broker.text;
        }

        let markup = "<div class='select2-result-broker clearfix'>" +
            "<strong>" + broker.name + "</strong>" +
            "<br />" +
            "<small>" + broker.account.accountNo + "</small>" +
            "</div>";

        return markup;
    }


    /**
     * Define how will be render the item selected.
     *
     * @param {{text: string, name: string, account: {accountNo: string}}} broker A broker object with text property.
     *
     * @return {string}
     */
    templateSelection(broker) {
        if (!broker.name) {
            return broker.text;
        }

        return broker.name + " - " + broker.account.accountNo;
    }
}

export default Select2BrokerTemplate;
