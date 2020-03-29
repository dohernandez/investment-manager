'use strict';

import RowButton from "./RowButton";

import $ from 'jquery';

const eventBus = require('js-event-bus')();

class DeleteRowButton extends RowButton {
    /**
     *
     * @param form {Object}
     * @param url {function}
     * @param selector {string}
     */
    constructor(form, url, selector) {
        super(selector, function (e) {
            e.preventDefault();

            // Setting form data.
            const $row = $(e.currentTarget).closest('tr');

            const title = $row.data('title');
            const id = $row.data('id');

            form.delete(url(id), id, title)
                .then((result) => {
                    if (result.value) {
                        eventBus.emit('entity_deleted', null, id, $row);
                    }
                });

        });
    }
}


export default DeleteRowButton;
