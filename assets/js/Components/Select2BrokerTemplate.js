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
     * @param {{loading: boolean, text: string, title: string}} broker A broker object with text property.
     *
     * @return {*}
     */
    templateResult(broker) {
        if (broker.loading) {
            return broker.text;
        }

        let markup = "<div class='select2-result-broker clearfix'>" +
            broker.title +
            "</div>";

        return markup;
    }


    /**
     * Define how will be render the item selected.
     *
     * @param {{text: string, title: string}} broker A broker object with text property.
     *
     * @return {string}
     */
    templateSelection(broker) {
        if (!broker.name) {
            return broker.text;
        }

        return broker.title;
    }
}

export default Select2BrokerTemplate;
