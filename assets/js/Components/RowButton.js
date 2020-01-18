'use strict';

import Button from "./Button";
import Swal from 'sweetalert2';
import Template from "./Template";

const eventBus = require('js-event-bus')();

class RowButton extends Button {
    /**
     *
     * @param from {Object}
     * @param url {function}
     * @param selector {string}
     * @param container {string}
     */
    constructor(selector, handler, width = null) {
        super(selector, null, null, handler, width);

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

    /**
     * Wrapper to compile templates in order to export the functionality
     *
     * @param template
     * @param entity
     */
    compile(template, entity) {
        return Template.compile(template, entity);
    }

    /**
     * Fire Swal window
     *
     * @param html
     * @param title
     * @param onBeforeOpen {function}
     * @param onOpen {function}
     *
     * @return {*|Promise|Promise<T | never>}
     */
    fireSwal(swalOptions, title, html, onBeforeOpen, onOpen) {
        // Swal form modal
        const swalView = Swal.mixin(swalOptions);

        return swalView.fire({
            html,
            title,
            onBeforeOpen: () => {
                if (typeof onBeforeOpen === "function") {
                    onBeforeOpen();
                }
            },
            onOpen: () => {
                if (typeof onOpen === "function") {
                    onOpen();
                }
            }
        });
    }
}


export default RowButton;
