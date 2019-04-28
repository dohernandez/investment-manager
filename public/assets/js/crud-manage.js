'use strict';

(function (window, $, Routing, Swal) {
    /**
     * Create a CRUD Manage Table instance to add, remove row in the table defined in
     * @see templates/Components/Table/crud-manage-table.html.twig
     *
     * @constructor
     *
     * @param $wrapper
     * @param swalFormOptions
     * @param swalFormOptionsText
     * @param swalFormOptions
     * @param toastOptions
     * @constructor
     */
    window.CRUDManage = function ($wrapper, swalFormOptions, swalFormOptionsText, swalConfirmOptions, toastOptions) {
        // Start binding functions for $wrapper
        this.$wrapper = $wrapper;

        this.swalFormOptions = swalFormOptions;
        this.swalFormOptionsText = swalFormOptionsText;

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

        // Delegate selector
        this.$wrapper.on(
            'click',
            '.js-entity-edit',
            this.handleEdit.bind(this)
        );

        // Delegate selector
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

        setForm: function (form) {
            this.form = form;
        },

        /**
         * Handle on click event for create button.
         *
         * @param e
         */
        handleCreate: function (e) {
            e.preventDefault();

            let formOptionsText = this.swalFormOptionsText.create;
            
            this._createFrom(formOptionsText)
                .then((result) => {
                    if (result.value) {
                        this._addRow(result.value.item);
                    }
                });
        },

        _createFrom : function (formOptionsText, data) {
            let tplText = $('#js-manager-form-template').html();
            let tpl = _.template(tplText);
            let html = tpl();

            const swalForm = Swal.mixin(this.swalFormOptions);

            return swalForm.fire({
                html: html,
                confirmButtonText: formOptionsText.confirmButtonText,
                titleText: formOptionsText.titleText,
                onBeforeOpen: () => {
                    let modal = $(swalForm.getContainer()).find('.swal2-modal');

                    if (data) {
                        let $form = modal.find(this._selectors.createForm);
                        for (let property in data) {
                            $form.find('#' + property).val(data[property]);
                        }
                    }

                    $('[data-datepickerenable="on"]').datetimepicker();

                    let $autocomplete = $('.js-account-autocomplete');

                    $autocomplete.each((index, select) => {
                        let jsDataAccountUrl = $(select).data('autocomplete-url');

                        $(select).select2({
                            dropdownParent: modal,
                            ajax: {
                                url: jsDataAccountUrl,
                                dataType: 'json',
                                delay: 10,
                                allowClear: true,
                                data: (params) => {
                                    return {
                                        q: params.term, // search term
                                        page: params.page
                                    };
                                },
                                processResults: (data, params)=> {
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
                    let $form = $(swalForm.getContainer()).find(this._selectors.createForm);

                    return this._saveForm($form)
                        .catch((errorsData) => {
                            this._mapErrorsToForm($form, errorsData.errors);

                            return false;
                        });
                }
            }).then((result) => {
                if (result.value) {
                    this._showStatusMessage(formOptionsText);
                }

                return result
            }).catch((arg) => {
                // canceling is cool!
                console.log(arg)
            });
        },

        _showStatusMessage: function (formOptionsText) {
            // let titleText = formOptionsText.toastTitleText.replace(/\{0\}/g, 'created');
            let titleText = formOptionsText.toastTitleText;
            const toast = Swal.mixin(this.toastOptions);

            toast.fire({
                type: 'success',
                titleText: titleText
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
            let formData = {};

            $.each($form.serializeArray(), (key, fieldData) => {
                formData[fieldData.name] = fieldData.value
            });

            return this._sendRPC(Routing.generate('transfer_save'), 'POST', formData);
        },

        _sendRPC: function(url, method, formData) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: url,
                    method: method,
                    data: formData !== 'undefined' ? JSON.stringify(formData) : ''
                }).then((data, textStatus, jqXHR) => {
                    resolve(data);
                }).catch((jqXHR) => {
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
         * @param {{
         *          id: int,
         *          date: string,
         *          beneficiaryParty: {name: string, accountNo: string},
         *          debtorParty: {name: string, accountNo: string},
         *          amount: float
         *        }} entity
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

            $form.find(':input').each((index, input) => {
                let fieldName = $(input).attr('name');
                let $groupWrapper = $(input).closest('.form-group');
                let $wrapper = $(input).closest('div');

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
        handleEdit: function (e) {
            e.preventDefault();

            // Setting form data
            let $form = $(e.currentTarget).closest('tr');
            let id = $form.data('id');

            let getUrl = Routing.generate('transfer_get', {id: id});

            this._sendRPC(getUrl, 'GET').then((data) => {
                let formOptionsText = this.swalFormOptionsText.update;

                this._createFrom(formOptionsText, data.item);
            });
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

            let text = this.swalConfirmOptions.text.replace(/\{0\}/g, '"' + itemTitle + '"');
            const swalConfirm = Swal.mixin(this.swalConfirmOptions);

            swalConfirm.fire({
                text: text,
                preConfirm: () => {
                    return this._deleteForm($row, Routing.generate('transfer_delete', {id: id}));
                }
            }).then((result) => {
                if (result.value) {
                    this._showStatusMessage(this.swalFormOptionsText.delete)
                }
            }).catch((arg) => {
                // canceling is cool!
            });
        },

        /**
         * Delete the entity thro an ajax request. Add the row is removed from the table
         *
         * @param e
         */
        _deleteForm: function ($row, deleteUrl) {
            return this._sendRPC(deleteUrl, 'DELETE')
                .then(() => {
                    $row.fadeOut('normal', () => {
                        $row.remove();

                        this._recalculateRowIndex();

                        let $table = this.$wrapper.find(this._selectors.table);
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

            $.each($rowIndexThs, function (index, th) {
                $(th).html(index + 1);
            })
        },

        /**
         * Loads all entities into the table.
         */
        loadEntities: function () {
            $.ajax({
                url: Routing.generate('transfer_list'),
                success: (data) => {
                    $.each(data.items, (index, entity) => {
                        this._addRow(entity);
                    });
                }
            });
        }
    });
})(window, jQuery, Routing, Swal);
