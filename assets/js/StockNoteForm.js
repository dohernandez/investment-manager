'use strict';

import SwalForm from "./Components/SwalForm";
import StockNoteRowButton from './Components/StockNoteRowButton';

class StockNoteForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-table-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;

        eventBus.on("stock_note_updated", this.onUpdated.bind(this));
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpenEditView(data, $wrapper) {
        if (data) {
            let $form = $wrapper.find(this.selector);
            for (const property in data) {
                let $input = $form.find('#' + property);

                $input.val(data[property]);
            }
        }
    }

    onUpdated(entity, $row) {
        this.table.replaceRecord(entity, entity.id);

        $row.fadeOut('normal', () => {
            $row.replaceWith(this.table.createRow(entity));
        });
    }
}

global.StockNoteForm = StockNoteForm;
global.StockNoteRowButton = StockNoteRowButton;

export default window.StockNoteForm;
