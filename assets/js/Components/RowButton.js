'use strict';

import Button from "./Button";

const eventBus = require('js-event-bus')();

class RowButton extends Button {
    /**
     *
     * @param from {Object}
     * @param url {function}
     * @param selector {string}
     * @param container {string}
     */
    constructor(selector, handler) {
        super(selector, null, null, handler);

        this.table = null;
    }

    /**
     * Render the button
     * @param $wrapper
     */
    render($wrapper) {
        this.register($wrapper);
    }

    /**
     * Set the table
     * @param table
     */
    setTable(table) {
        this.table = table;
    }
}


export default RowButton;
