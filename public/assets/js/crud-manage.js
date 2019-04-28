'use strict';

(function (window, $, Routing, Swal) {
    /**
     * Create a CRUD Manage Table instance to add, remove row in the table defined in
     * @see templates/Components/Table/crud-manage-table.html.twig
     *
     * @constructor
     *
     * @param $wrapper
     * @param $createModal
     * @param swalFormOptions
     * @param toastOptions
     * @constructor
     */
    window.CRUDManage = function ($wrapper, swalFormOptions, swalConfirmOptions, toastOptions) {
        // Start binding functions for $wrapper
        this.$wrapper = $wrapper;

        this.swalFormOptions = swalFormOptions;
        this.swalConfirmOptions = swalConfirmOptions;
        this.toastOptions = toastOptions;

        /**
         * Delegate selector
         * Define a second argument, which is the selector for the element that you truly want to react to.
         */

        // Attaching show modals on button clicks
        this.$wrapper.on(
            'click',
            '.js-entity-create',
            this.handleCreate.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-entity-edit',
            this.handleDisplayModalEdit.bind(this)
        );

        this.$wrapper.on(
            'click',
            '.js-entity-delete',
            this.handlerDelete.bind(this)
        );
        // End binding functions to $wrapper

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
        handleCreate: function (e) {
            e.preventDefault();

            let tplText = $('#js-manager-form-template').html();
            let tpl = _.template(tplText);
            let html = tpl();

            let _self = this;

            const swalForm = Swal.mixin(this.swalFormOptions);

            swalForm.fire({
                html: html,
                onBeforeOpen: () => {
                    $('[data-datepickerenable="on"]').datetimepicker();

                    let $autocomplete = $('.js-account-autocomplete');
                    let modal = $(swalForm.getContainer()).find('.swal2-modal');

                    $autocomplete.each(function () {
                        let jsDataAccountUrl = $(this).data('autocomplete-url');

                        $(this).select2({
                            dropdownParent: modal,
                            ajax: {
                                url: jsDataAccountUrl,
                                dataType: 'json',
                                delay: 10,
                                allowClear: true,
                                data: function (params) {
                                    return {
                                        group: 'options_name_account_no',
                                        q: params.term, // search term
                                        page: params.page
                                    };
                                },
                                processResults: function (data, params) {
                                    // parse the results into the format expected by Select2
                                    // since we are using custom formatting functions we do not need to
                                    // alter the remote JSON data, except to indicate that infinite
                                    // scrolling can be used
                                    params.page = params.page || 1;

                                    return {
                                        results: data.items,
                                        pagination: {
                                            more: (params.page * 30) < data.total_count
                                        }
                                    };
                                },
                                cache: true
                            },
                            placeholder: 'Search for an account',
                            escapeMarkup: (markup) => { // let our custom formatter work
                                return markup;
                            },
                            minimumInputLength: 1,
                            templateResult: (repo) => {
                                if (repo.loading) {
                                    return repo.text;
                                }

                                let markup = "<div class='select2-result-account clearfix'>" +
                                    "<strong>" + repo.name + "</strong>" +
                                    "<br />" +
                                    "<small>" + repo.accountNo + "</small>" +
                                    "</div>";

                                return markup;
                            },
                            templateSelection: (repo) => {
                                if (!repo.name) {
                                    return repo.text;
                                }

                                return repo.name + " - " + repo.accountNo;
                            }
                        });
                    });
                },
                preConfirm: () => {
                    let $form = $(swalForm.getContainer()).find(_self._selectors.createForm);

                    return _self._saveForm($form)
                        .catch((errorsData) => {
                            _self._mapErrorsToForm($form, errorsData.errors);

                            return false;
                        });
                }
            }).then((result) => {
                if (result.value) {
                    _self._addRow(result.value.item);

                    let titleText = this.toastOptions.titleText.replace(/\{0\}/g, 'created');
                    const toast = Swal.mixin(_self.toastOptions);

                    toast.fire({
                        type: 'success',
                        titleText: titleText
                    });
                }
            }).catch(function(arg) {
                // canceling is cool!
            });
        },

        /**
         * Save the data thro an ajax request. Add the new row to the table
         *
         * @param $form
         * @return {Promise<any>}
         * @private
         */
        _saveForm: function($form) {
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

        _mapErrorsToForm: function($form, errorData) {
            this._removeFormErrors($form);

            $form.find(':input').each(function() {
                let fieldName = $(this).attr('name');
                let $groupWrapper = $(this).closest('.form-group');
                let $wrapper = $(this).closest('div');

                if (!errorData[fieldName]) {
                    // no error!
                    return;
                }

                let $error = $('<span class="js-field-error help-block" style="text-align: left;"></span>');
                $error.html(errorData[fieldName]);

                $wrapper.append($error);
                $groupWrapper.addClass('has-error');
            });
        },

        _removeFormErrors: function($form) {
            $form.find('.js-field-error').remove();
            $form.find('.form-group').removeClass('has-error');
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
        handlerDelete: function (e) {
            e.preventDefault();

            // Setting form data
            let $form = $(e.currentTarget);
            let $row = $form.closest('tr');

            let itemTitle = $form.data('title');
            let id = $form.data('id');

            let _self = this;

            let text = this.swalConfirmOptions.text.replace(/\{0\}/g, '"' + itemTitle + '"');
            const swalConfirm = Swal.mixin(this.swalConfirmOptions);

            swalConfirm.fire({
                text: text,
                preConfirm: () => {
                    return _self._deleteForm($row, Routing.generate('transfer_delete', {id: id}));
                }
            }).then((result) => {
                if (result.value) {
                    let titleText = this.toastOptions.titleText.replace(/\{0\}/g, 'deleted');
                    const toast = Swal.mixin(_self.toastOptions);

                    toast.fire({
                        type: 'success',
                        titleText: titleText
                    });
                }
            }).catch(function(arg) {
                // canceling is cool!
            });
        },

        /**
         * Delete the entity thro an ajax request. Add the row is removed from the table
         *
         * @param e
         */
        _deleteForm: function ($row, deleteUrl) {
            let _self = this;

            return this._sendRPC(deleteUrl, 'DELETE')
                .then(function () {
                    $row.fadeOut('normal', function () {
                        $(this).remove();

                        _self._recalculateRowIndex();

                        let $table = _self.$wrapper.find(_self._selectors.table);
                        let length = parseInt($table.data('length'));
                        $table.data('length', length - 1);
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
})(window, jQuery, Routing, Swal);
