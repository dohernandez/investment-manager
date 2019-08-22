'use strict';

import SwalForm from './Components/SwalForm';
/**
 * Form manage how the account form should be build when a crud manager invokes a create or an update action.
 */
class AccountForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-panel-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpen(data, $wrapper) {
        if (data) {
            let $form = $wrapper.find(this.selector);
            for (const property in data) {
                let $input = $form.find('#' + property);

                $input.val(data[property]);
            }
        }
    }

    onCreated(entity) {
        this.table.addRecord(entity);
    }
}

global.AccountForm = AccountForm;

