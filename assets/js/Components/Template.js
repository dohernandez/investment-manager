'use strict';

import _ from 'underscore';
import $ from 'jquery';

class Template {
    static compile(selector, compile) {
        const tplText = $(selector).html();
        const tpl = _.template(tplText);

        let html = '';

        if (compile !== null || typeof compile !== 'undefined') {
            html = tpl(compile);
        }

        return $.parseHTML(html);
    }
}

export default Template;
