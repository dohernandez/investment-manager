'use strict';

import DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document';

import './../../css/DocumentEditor.scss';

class DocumentEditor {
    constructor(data, editorSelector = '.document-editor__editable', toolbarSelector = '.document-editor__toolbar'){
        this.editor = null;

        let self = this;
        DecoupledEditor.create(document.querySelector(editorSelector), {
            ckfinder: {
                // The URL that the images are uploaded to.
                uploadUrl: '/v1/upload/image',
            },
            image: {
                // You need to configure the image toolbar, too, so it uses the new style buttons.
                toolbar: [ 'imageStyle:alignLeft', 'imageStyle:full', 'imageStyle:alignRight' ],

                styles: [
                    // This option is equal to a situation where no style is applied.
                    'full',

                    // This represents an image aligned to the left.
                    'alignLeft',

                    // This represents an image aligned to the right.
                    'alignRight'
                ]
            }
        })
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
