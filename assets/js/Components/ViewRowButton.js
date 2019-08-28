'use strict';

import RowButton from "./RowButton";
import Swal from 'sweetalert2';
import Template from "./Template";

import $ from 'jquery';

class ViewRowButton extends RowButton {
    /**
     *
     * @param form {Object}
     * @param selector {string}
     */
    constructor(form, selector, swalOptions, template = '#js-view-template') {
        super(selector, function (e) {
            e.preventDefault();

            // Setting form data.
            const $row = $(e.currentTarget).closest('tr');

            const title = $row.data('title');
            const id = $row.data('id');

            let entity = this.table.getRecord(id);
            let html = Template.compile(template, entity);

            // Swal form modal
            const swalView = Swal.mixin(swalOptions);

            return swalView.fire({
                html,
                title,
                onBeforeOpen: () => {
                    form.onBeforeOpenPreview(entity);
                },
            });
        });
    }
}


export default ViewRowButton;
