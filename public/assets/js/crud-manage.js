'use strict';

(function (window, $, Routing, Swal) {
    /**
     * CRUD Manage Table to add, remove row in the table defined in
     * @see templates/Components/Table/crud-manage-table.html.twig
     *
     */
    class CRUDManage {
        /**
         *
         * @param $wrapper
         * @param swalFormOptions
         * @param swalConfirmOptions
         * @param toastOptions
         */
        constructor($wrapper, swalFormOptions, swalConfirmOptions, toastOptions) {
            // Start binding functions for $wrapper
            this.$wrapper = $wrapper;

            this.swalFormOptions = swalFormOptions;

            this.swalConfirmOptions = swalConfirmOptions;
            this.toastOptions = toastOptions;

            /**
             * Delegate selector
             * Define a second argument, which is the selector for the element that you truly want to react to.
             */

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
        }

        withCreateButton(form) {
            this.form = form;

            // Attaching show modals on button clicks
            this.$wrapper.on(
                'click',
                '.js-entity-create',
                this.handleCreate.bind(this)
            );
        }

        static get _selectors() {
            return {
                table: '.js-manager-table'
            };
        }

        /**
         * Handle on click event for create button.
         *
         * @param e
         */
        handleCreate(e) {
            e.preventDefault();

            this._createFrom()
                .then((result) => {
                    if (result.value) {
                        this._addRow(result.value.item);
                    }
                });
        }

        _createFrom(data = null) {
            const html = this.form.html();
            const formOptions = this.form.formOptions(data === null ? 'create' : 'update');

            const swalForm = Swal.mixin(this.swalFormOptions);

            return swalForm.fire({
                html: html,
                confirmButtonText: formOptions.text.confirmButtonText,
                titleText: formOptions.text.titleText,
                onBeforeOpen: () => {
                    const $modal = $(swalForm.getContainer()).find('.swal2-modal');

                    formOptions.onBeforeOpen(data, $modal);
                },
                preConfirm: () => {
                    const $form = $(swalForm.getContainer()).find(this.form.selector);

                    return this._saveForm($form)
                        .catch((errorsData) => {
                            this._mapErrorsToForm($form, errorsData.errors);

                            return false;
                        });
                }
            }).then((result) => {
                if (result.value) {
                    this._showStatusMessage(formOptions.text.toastTitleText);
                }

                return result
            }).catch((arg) => {
                // canceling is cool!
                console.log(arg)
            });
        }

        _showStatusMessage(titleText) {
            // let titleText = formOptionsText.toastTitleText.replace(/\{0\}/g, 'created');
            const toast = Swal.mixin(this.toastOptions);

            toast.fire({
                type: 'success',
                titleText
            });
        }

        /**
         * Save the data thro an ajax request. Add the new row to the table
         *
         * @param $form
         * @return {Promise<any>}
         * @private
         */
        _saveForm($form) {
            const formData = {};

            $.each($form.serializeArray(), (key, fieldData) => {
                formData[fieldData.name] = fieldData.value
            });

            return this._sendRPC(Routing.generate('transfer_save'), 'POST', formData);
        }

        _sendRPC(url, method, formData = null) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url,
                    method,
                    data: formData ? JSON.stringify(formData) : ''
                }).then((data, textStatus, jqXHR) => {
                    resolve(data);
                }).catch((jqXHR) => {
                    if (jqXHR.status =! 400) {
                        reject(jqXHR);

                        return;
                    }

                    const errorData = JSON.parse(jqXHR.responseText);

                    reject(errorData);
                });
            });
        }

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
        _addRow(entity) {
            const $table = this.$wrapper.find(CRUDManage._selectors.table);

            // adding index to the entity
            const length = parseInt($table.data('length'));
            entity.index = length + 1;

            const tplText = $('#js-manager-row-template').html();
            const tpl = _.template(tplText);

            const html = tpl(entity);
            $table.find('tbody').append($.parseHTML(html));

            $table.data('length', entity.index)
        }

        _mapErrorsToForm($form, errorData) {
            CRUDManage._removeFormErrors($form);

            $form.find(':input').each((index, input) => {
                const fieldName = $(input).attr('name');
                const $groupWrapper = $(input).closest('.form-group');
                const $wrapper = $(input).closest('div');

                if (!errorData[fieldName]) {
                    // no error!
                    return;
                }

                const $error = $('<span class="js-field-error help-block" style="text-align: left;"></span>');
                $error.html(errorData[fieldName]);

                $wrapper.append($error);
                $groupWrapper.addClass('has-error');
            });
        }

        static _removeFormErrors($form) {
            $form.find('.js-field-error').remove();
            $form.find('.form-group').removeClass('has-error');
        }

        /**
         * Handle on click event for edit buttons.
         *
         * @param e
         */
        handleEdit(e) {
            e.preventDefault();

            // Setting form data
            const $form = $(e.currentTarget).closest('tr');
            const id = $form.data('id');

            let getUrl = Routing.generate('transfer_get', {id: id});

            this._sendRPC(getUrl, 'GET').then((data) => {
                this._createFrom(data.item);
            });
        }

        /**
         * Handle on click event for row delete buttons.
         *
         * @param e The event
         */
        handlerDelete(e) {
            e.preventDefault();

            // Setting form data
            const $form = $(e.currentTarget);
            const $row = $form.closest('tr');

            const itemTitle = $form.data('title');
            const id = $form.data('id');

            const text = this.swalConfirmOptions.text.replace(/\{0\}/g, '"' + itemTitle + '"');
            const swalConfirm = Swal.mixin(this.swalConfirmOptions);

            swalConfirm.fire({
                text,
                preConfirm: () => this._deleteForm($row, Routing.generate('transfer_delete', {id: id}))
            }).then((result) => {
                if (result.value) {
                    this._showStatusMessage(this.form.formOptions('delete').text.toastTitleText)
                }
            }).catch((arg) => {
                // canceling is cool!
            });
        }

        /**
         * Delete the entity thro an ajax request. Add the row is removed from the table
         *
         * @param {object} $row
         * @param {string} url
         */
        _deleteForm($row, url) {
            return this._sendRPC(url, 'DELETE')
                .then(() => {
                    $row.fadeOut('normal', () => {
                        $row.remove();

                        this._recalculateRowIndex();

                        const $table = this.$wrapper.find(CRUDManage._selectors.table);
                        const length = parseInt($table.data('length'));

                        $table.data('length', length - 1);
                    });
                })
                .catch(function (jqXHR) {
                    // TODO implement error form handling
                    console.log(jqXHR.responseText);
                })
            ;
        }

        /**
         * Recalculate the index fo the row table base on the new list.
         *
         * When adding or removing a new row, indexes get messy, so this functions helps to reorder them again.
         *
         * @private
         */
        _recalculateRowIndex() {
            const $rowIndexThs = this.$wrapper.find(CRUDManage._selectors.table).find('.js-manager-row-index');

            $.each($rowIndexThs, function (index, th) {
                $(th).html(index + 1);
            })
        }

        /**
         * Loads all entities into the table.
         */
        loadEntities() {
            $.ajax({
                url: Routing.generate('transfer_list'),
                success: (data) => {
                    $.each(data.items, (index, entity) => {
                        this._addRow(entity);
                    });
                }
            });
        }
    }

    class Form {
        constructor(swalFormOptionsText, template = '#js-manager-form-template', selector = '.js-entity-create-from') {
            this.swalFormOptionsText = swalFormOptionsText;
            this.template = template;
            this.selector = selector;
        }

        html() {
           const tplText = $(this.template).html();
           const tpl = _.template(tplText);
           const html = tpl();

           return html;
        }

        formOptions(option = 'create') {
            let formOptions = {};

            switch (option) {
                case 'create':
                    formOptions = {
                        text: this.swalFormOptionsText.create,
                        onBeforeOpen: this.onBeforeOpen.bind(this)
                    };

                    break;
                case 'update':
                    break;
                case 'delete':
                    formOptions = {
                        text: this.swalFormOptionsText.delete
                    };

                    break;
            }

            return formOptions;
        }

        onBeforeOpen(data, $wrapper) {
            if (data) {
                let $form = $wrapper.find(this.selector);
                for (const property in data) {
                    $form.find('#' + property).val(data[property]);
                }
            }

            $('[data-datepickerenable="on"]').datetimepicker();

            let $autocomplete = $('.js-account-autocomplete');

            $autocomplete.each((index, select) => {
                const url = $(select).data('autocomplete-url');

                $(select).select2({
                    dropdownParent: $wrapper,
                    ajax: {
                        url,
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
                    escapeMarkup: (markup) => markup,
                    minimumInputLength: 1,
                    templateResult: (repo) => {
                        if (repo.loading) {
                            return repo.text;
                        }

                        return "<div class='select2-result-account clearfix'>" +
                            "<strong>" + repo.name + "</strong>" +
                            "<br />" +
                            "<small>" + repo.accountNo + "</small>" +
                            "</div>";
                    },
                    templateSelection: (repo) => {
                        if (!repo.name) {
                            return repo.text;
                        }

                        return repo.name + " - " + repo.accountNo;
                    }
                });
            });
        }
    }

    window.CRUDManage = CRUDManage;
    window.Form = Form;

})(window, jQuery, Routing, Swal);
