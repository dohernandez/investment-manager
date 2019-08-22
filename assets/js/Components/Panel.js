'use strict';

import Table from "./Table";

class Panel {
    /**
     *
     * @param {{
     *      wrapper: {Object},
     *      table: {Table},
     * }} options
     */
    constructor(options) {
        let _options = _.defaults(options || {}, {

        });

        // Start binding functions for $wrapper
        this.$wrapper = _options.wrapper;

        this.table = _options.table;
        this.buttons = [];
    }

    addButton(button) {
        this.buttons.push(button);
    }

    render() {
        let $wrapper = this.$wrapper;

        $.each(this.buttons, function (index, button) {
            button.render($wrapper);
        });

        this.table.render();
    }
}
