'use strict';

import Button from "./Button";

import $ from 'jquery';

const eventBus = require('js-event-bus')();

class CreateButton extends Button {
    /**
     *
     * @param text {string}
     * @param form {Object}
     * @param url {function}
     * @param selector {string}
     * @param container {string}
     */
    constructor(text, form, url, selector, container, eventName = 'entity_created') {
        super(selector, null, container, function (e) {
            e.preventDefault();

            form.create(url())
                .then((result) => {
                    if (result.value) {
                        let entity = result.value.item;

                        eventBus.emit(eventName, null, entity);
                    }
                });
        });

        this.text = text;
    }

    /**
     * Render the button
     * @param $wrapper
     */
    render($wrapper) {
        let $button = $('<button class="' + this.selector.slice(1) +' btn btn-success pull-right"></button>')
                .append('<li class="fa-plus-circle fa" aria-hidden="true"></li>')
                .html(this.text);

        $wrapper.find(this.container)
            .append($button);

        this.register($wrapper);
    }
}

export default CreateButton;
