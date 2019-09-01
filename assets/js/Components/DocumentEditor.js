'use strict';

import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document';

import './../../css/DocumentEditor.scss';

class DocumentEditor {
    constructor(data, editorSelector = '.document-editor__editable', toolbarSelector = '.document-editor__toolbar'){
        this.editor = null;

        let self = this;
        DecoupledEditor.create(document.querySelector(editorSelector))
            .then( editor => {
                const toolbarContainer = document.querySelector(toolbarSelector);

                toolbarContainer.appendChild(editor.ui.view.toolbar.element);

                editor.setData(data);

                self.setEditor(editor);
            } )
            .catch( err => {
                console.error( err );
            } );
    }

    setEditor(editor) {
        this.editor = editor;
    }

    getData() {
        return this.editor.getData();
    }
}

export default DocumentEditor;
