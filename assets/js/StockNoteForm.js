'use strict';

import SwalForm from "./Components/SwalForm";
import StockNoteRowButton from './Components/StockNoteRowButton';
import DocumentEditor from "./Components/DocumentEditor";

class StockNoteForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-table-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;
        this.editor = null;

        eventBus.on("stock_note_updated", this.onUpdated.bind(this));
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpenEditView(data, $wrapper) {
        let notes = data['notes'] ? data['notes'] : '';
        this.editor = new DocumentEditor(notes);
    }

    preConfirm($wrapper, url, method) {
        let $form = $wrapper.find(this.selector);

        let $inputNote = $form.find('#notes');
        $inputNote.val(this.editor.getData());

        return super.preConfirm($wrapper, url, method);
    }

    onUpdated(entity, $row) {
        this.table.replaceRecord(entity, entity.id);

        $row.fadeOut('normal', () => {
            $row.replaceWith(this.table.createRow(entity));
        });

        this.table.refresh();
    }
}

global.StockNoteForm = StockNoteForm;
global.StockNoteRowButton = StockNoteRowButton;

export default window.StockNoteForm;
