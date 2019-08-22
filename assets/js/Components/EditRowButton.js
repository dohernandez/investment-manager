'use strict';

import RowButton from "./RowButton";

import $ from 'jquery';

const eventBus = require('js-event-bus')();

class EditRowButton extends RowButton {
    /**
     *
     * @param form {Object}
     * @param url {function}
     * @param selector {string}
     * @param container {string}
     */
    constructor(form, url, selector) {
        super(selector, function (e) {
            e.preventDefault();

            // find entity to edit
            const $row = $(e.currentTarget).closest('tr');
            const id = $row.data('id');

            let entity = this.table.getRecord(id);
            let index = entity.index;

            form.update(url(id), entity)
            // update the row by creating a new row base on the row template and
            // replace the old row
                .then((result) => {
                    if (result.value) {
                        let entity = result.value.item;
                        entity.index = index;

                        eventBus.emit('entity_updated', null, entity, $row);
                    }
                });
        });
    }
}


export default EditRowButton;
