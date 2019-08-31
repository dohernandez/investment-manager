'use strict';

import RowButton from "./RowButton";

import $ from 'jquery';

const eventBus = require('js-event-bus')();

class StockNoteRowButton extends RowButton {
    /**
     *
     * @param form {Object}
     * @param swalOptions {Object}
     * @param url {function}
     * @param selector {string}
     */
    constructor(form, swalOptions, url, selector) {
        super(selector, function (e) {
            e.preventDefault();

            // find entity to edit
            const $row = $(e.currentTarget).closest('tr');
            const id = $row.data('id');

            let entity = this.table.getRecord(id);

            form.display(swalOptions, url(id), 'PATCH', entity)
                .then((result) => {
                    if (result.value) {
                        let entity = result.value.item;
                        entity.index = index;

                        eventBus.emit('stock_note_updated', null, entity, $row);
                    }
                });
        });
    }
}


export default StockNoteRowButton;
