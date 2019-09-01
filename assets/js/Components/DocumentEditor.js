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

                self.editor = editor;
                self.editor.setData(data);
            } )
            .catch( err => {
                console.error( err );
            } );
    }
}

export default DocumentEditor;
