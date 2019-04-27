'use strict';

(function (window, $, Routing) {
    /**
     * Create a CRUD Manage Table instance to add, remove row in the table defined in
     * @see templates/Components/Table/crud-manage-table.html.twig
     *
     * @constructor
     *
     * @param $wrapper
     * @param $createModal
     * @param $deleteModal
     * @constructor
     */
    window.CRUDManage = function ($wrapper, $createModal, $deleteModal) {
        // Start binding functions for $wrapper
        this.$wrapper = $wrapper;

        /**
         * Delegate selector
         * Define a second argument, which is the selector for the element that you truly want to react to.
         */

        // Attaching show modals on button clicks
        this.$wrapper.on(
            'click',
            '.js-entity-create',
            this.handleDisplayModalCreate.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-entity-edit',
            this.handleDisplayModalEdit.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-entity-delete',
            this.handlerDisplayModalConfirmDelete.bind(this)
        );
        // End binding functions to $wrapper


        // Start binding functions for $modal ($createModal)
        this.$createModal = $createModal;

        // Delegate selector
        this.$createModal.on(
            'click',
            '.js-submit-btn',
            this.handleModalCreateSubmit.bind(this)
        );

        this.$createModal.on(
            'hidden.bs.modal',
            this.handleModalCreateHidden.bind(this)
        );

        // Start binding functions for $modal ($deleteModal)
        this.$deleteModal = $deleteModal;

        // Delegate selector
        this.$deleteModal.on(
            'click',
            '.js-confirm-delete',
            this.handleModalDeleteConfirm.bind(this)
        );

        this.$deleteModal.on(
            'hidden.bs.modal',
            this.handleModalDeleteConfirmHidden.bind(this)
        );

        this.loadEntities();
    };

    $.extend(window.CRUDManage.prototype, {
        _selectors: {
            createForm: '.js-entity-create-from',
            table: '.js-manager-table'
        },

        /**
         * Handle on click event for create button.
         *
         * @param e
         */
        handleDisplayModalCreate: function (e) {
            e.preventDefault();

            let $form = this.$createModal.find(this._selectors.createForm);

            this._clearForm($form);
        },

        /**
         * Clean up the form
         *
         * @param {Object} $form
         * @private
         */
        _clearForm: function($form) {
            this._removeFormErrors($form);

            $form[0].reset();
        },

        _removeFormErrors: function($form) {
            $form.find('.js-field-error').remove();
            $form.find('.form-group').removeClass('has-error');
        },

        /**
         * Handle on click event for submit data to be created.
         *
         * @param e
         */
        handleModalCreateSubmit: function (e) {
            e.preventDefault();

            let $form = this.$createModal.find(this._selectors.createForm);

            let _self = this;

            this._saveForm(this.$createModal, $form)
                .then(function (data) {
                    console.log('successfully save');

                    _self._addRow(data.item);

                    // Hide modal to trigger event `hidden.bs.modal` to _toggleProcessingPanel
                    _self.$createModal.modal('hide')
                }).catch(function (errorData) {
                    _self._toggleProcessingPanel(_self.$createModal, false);

                    /**
                     *  @var {Object} errorData
                     *  @var {Object} errorData.errors
                     */
                    _self._mapErrorsToForm($form, errorData.errors);
                })
            ;
        },

        /**
         * Save the data thro an ajax request. Add the new row to the table
         *
         * @param $modal
         * @param $form
         * @return {Promise<any>}
         * @private
         */
        _saveForm: function($modal, $form) {
            this._toggleProcessingPanel($modal, true);

            let formData = this._getDataFromForm($form);

            return this._sendRPC(Routing.generate('transfer_save'), 'POST', formData);
        },

        /**
         * Extract data from a form and serialize it.
         *
         * @param data
         *
         * @private
         *
         * @return data
         */
        _getDataFromForm: function($form) {
            let formData = {};

            $.each($form.serializeArray(), function(key, fieldData) {
                formData[fieldData.name] = fieldData.value
            });

            return formData;
        },

        _sendRPC: function(url, method, formData) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: url,
                    method: method,
                    data: formData !== 'undefined' ? JSON.stringify(formData) : ''
                }).then(function(data, textStatus, jqXHR){
                    resolve(data);
                }).catch(function(jqXHR){
                    if (jqXHR.status =! 400) {
                        reject(jqXHR);

                        return;
                    }

                    let errorData = JSON.parse(jqXHR.responseText);

                    reject(errorData);
                });
            });
        },

        /**
         * Create a row table with the entity value.
         *
         * @param {Object} entity
         * @param {int} entity.id
         * @param {string} entity.date
         * @param {string} entity.beneficiaryParty.name
         * @param {string} entity.beneficiaryParty.iban
         * @param {string} entity.debtorParty.name
         * @param {string} entity.debtorParty.iban
         * @param {float} entity.amount
         * @param {int} entity.index
         *
         * @private
         */
        _addRow: function (entity) {
            let $table = this.$wrapper.find(this._selectors.table);

            // adding index to the entity
            let length = parseInt($table.data('length'));
            entity.index = length + 1;

            let tplText = $('#js-manager-row-template').html();
            let tpl = _.template(tplText);

            let html = tpl(entity);
            $table.find('tbody').append($.parseHTML(html));

            $table.data('length', entity.index)
        },

        /**
         * Use to show processing panel when the request is being sent to the server.
         *
         * @param {Object} $modal A modal instance
         * @param {boolean} activate
         * @private
         */
        _toggleProcessingPanel: function($modal, activate) {
            let $modalDialog = $modal.find('.modal-dialog');
            let modalId = $modal.attr('id');
            let $processingBackground = $('.js-' + modalId + '-processing');

            if (activate) {
                // Activating processing panel
                $modalDialog.hide();
                $modal.css('text-align', 'center');

                $processingBackground.show();

                return
            }

            // Activating processing panel
            $modal.css('text-align', 'left');
            $modalDialog.show();

            $processingBackground.hide();
        },

        _mapErrorsToForm: function($form, errorData) {
            this._removeFormErrors($form);

            $form.find(':input').each(function() {
                let fieldName = $(this).attr('name');
                let $wrapper = $(this).closest('.form-group');

                if (!errorData[fieldName]) {
                    // no error!
                    return;
                }

                let $error = $('<span class="js-field-error help-block"></span>');
                $error.html(errorData[fieldName]);

                $wrapper.append($error);
                $wrapper.addClass('has-error');
            });
        },

        /**
         * Use to hide gracefully the create modal
         * @param e
         */
        handleModalCreateHidden: function(e) {
            e.preventDefault();

            this._toggleProcessingPanel(this.$createModal, false);
        },

        /**
         * Handle on click event for edit buttons.
         *
         * @param e
         */
        handleDisplayModalEdit: function (e) {
            e.preventDefault();

            console.log('Modal edit should be displayed!!!');
        },

        /**
         * Handle on click event for row delete buttons.
         *
         * @param e The event
         */
        handlerDisplayModalConfirmDelete: function (e) {
            e.preventDefault();

            // Setting form data
            let $form = $(e.currentTarget);
            // This variable is set in this function, but will be access in this.handleModalDeleteConfirm
            this.$selected = $form;

            let itemId = $form.data('id');
            let itemTitle = $form.data('title');

            this.$deleteModal.find('#itemId').val(itemId);
            this.$deleteModal.find('.modal-body p span').text(itemTitle);
        },

        /**
         * Handle on click event for confirm delete button.
         *
         * @param e
         */
        handleModalDeleteConfirm: function (e) {
            e.preventDefault();

            console.log(this.$deleteModal);

            let deleteUrl = this.$selected.data('url');
            let $row = this.$selected.closest('tr');

            this._toggleProcessingPanel(this.$deleteModal, true);

            let _self = this;

            this._sendRPC(deleteUrl, 'DELETE')
                .then(function () {
                    $row.fadeOut('normal', function () {
                        $(this).remove();

                        _self._recalculateRowIndex();

                        let $table = _self.$wrapper.find(_self._selectors.table);
                        let length = parseInt($table.data('length'));
                        $table.data('length', length - 1);

                        _self.$deleteModal.modal('hide');
                    });
                })
                .catch(function (jqXHR) {
                    // TODO implement error form handling
                    console.log(jqXHR.responseText);
                })
            ;
        },

        /**
         * Recalculate the index fo the row table base on the new list.
         *
         * When adding or removing a new row, indexes get messy, so this functions helps to reorder them again.
         *
         * @private
         */
        _recalculateRowIndex: function () {
            let $rowIndexThs = this.$wrapper.find(this._selectors.table).find('.js-manager-row-index');

            $.each($rowIndexThs, function (key, th) {
                $(th).html(key + 1);
            })
        },

        /**
         * Use to hide gracefully the delete confirmation modal
         * @param e
         */
        handleModalDeleteConfirmHidden: function(e) {
            e.preventDefault();

            this._toggleProcessingPanel(this.$deleteModal, false);
        },

        /**
         * Loads all entities into the table.
         */
        loadEntities: function () {
            let _self = this;

            $.ajax({
                url: Routing.generate('transfer_list'),
                success: function (data) {
                    $.each(data.items, function (key, entity) {
                        _self._addRow(entity);
                    });
                }
            });
        }
    });
})(window, jQuery, Routing);
