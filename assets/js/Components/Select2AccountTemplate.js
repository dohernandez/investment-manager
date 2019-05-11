'use strict';

/**
 * Select2 template for account objects.
 *
 * templateResult defines how the selected item will be shown in the select area.
 * templateSelection defines how items will be shown
 *
 * @type {{templateResult: templateResult, templateSelection: templateSelection}}
 */
class Select2AccountTemplate {
    /**
     * Define how will be render the item result.
     *
     * @param {{loading: boolean, text: string, name: string, accountNo: string}} account An account object with text property.
     *
     * @return {*}
     */
    templateResult(account) {
        if (account.loading) {
            return account.text;
        }

        let markup = "<div class='select2-result-account clearfix'>" +
            "<strong>" + account.name + "</strong>" +
            "<br />" +
            "<small>" + account.accountNo + "</small>" +
            "</div>";

        return markup;
    }


    /**
     * Define how will be render the item selected.
     *
     * @param {{text: string, name: string, accountNo: string}} account An account object with text property.
     *
     * @return {string}
     */
    templateSelection(account) {
        if (!account.name) {
            return account.text;
        }

        return account.name + " - " + account.accountNo;
    }
}

export default Select2AccountTemplate;
