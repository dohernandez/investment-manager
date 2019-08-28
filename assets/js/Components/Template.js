'use strict';

import _ from 'underscore';
import $ from 'jquery';

class Template {
    static compile(selector, compile = null) {
        const tplText = $(selector).html();
        const tpl = _.template(tplText);

        let html = '';

        if (compile !== null) {
            html = tpl(compile);
        } else {
            html = tpl();
        }

        return $.parseHTML(html);
    }
}

export default Template;
