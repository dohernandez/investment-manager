'use strict';

import RowButton from "./RowButton";

import $ from 'jquery';

class ViewRowButton extends RowButton {
    /**
     *
     * @param form {Object}
     * @param selector {string}
     */
    constructor(form, selector) {
        super(selector, function (e) {
            e.preventDefault();

            // Setting form data.
            const $row = $(e.currentTarget).closest('tr');

            const title = $row.data('title');
            const id = $row.data('id');

            let entity = this.table.getRecord(id);

            return form.preview(title, entity);
        });
    }
}


export default ViewRowButton;
