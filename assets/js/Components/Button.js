'use strict';

import Template from "./Template";

class Button {
    constructor(selector, template, container, handler) {
        this.selector = selector;
        this.template = template;
        this.container = container;
        this.handler = handler;
    }

    // Render
    render($wrapper) {
        let $button = Template.compile(this.template);

        $wrapper.find(this.container)
            .append($button);

        // Delegate selector
        $wrapper.on(
            'click',
            this.selector,
            this.handler.bind(this)
        );
    }
}

export default Button;
