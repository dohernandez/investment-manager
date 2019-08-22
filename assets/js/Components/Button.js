'use strict';

import Template from "./Template";

class Button {
    constructor(selector, template, container, handler, width = null) {
        this.selector = selector;
        this.template = template;
        this.container = container;
        this.handler = handler;

        this._width = width;
    }

    /**
     * Render the button
     * @param $wrapper
     */
    render($wrapper) {
        let $button = Template.compile(this.template);

        $wrapper.find(this.container)
            .append($button);

        this.register($wrapper);
    }

    /**
     * Register the click event
     *
     * @param $wrapper
     */
    register($wrapper) {
        // Delegate selector
        $wrapper.on(
            'click',
            this.selector,
            this.handler.bind(this)
        );
    }

    get width() {
        return this._width
    }
}

export default Button;
