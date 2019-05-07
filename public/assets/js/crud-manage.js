'use strict';

(function (window, $, Routing, Swal, moment) {
    /**
     * CRUD Manage Table for adding, updating and removing rows (entities) in the table defined in
     * @see templates/Components/Table/crud-manage-table.html.twig.
     *
     */
    class CRUDManage {
        /**
         *
         * @param {string} entityType
         * @param {Object} $wrapper
         * @param {Object} swalFormOptions
         * @param {Object} swalConfirmOptions
         * @param {Object} toastOptions
         */
        constructor(entityType, $wrapper, swalFormOptions, swalConfirmOptions, toastOptions) {
            this.entityType = entityType;

            // Start binding functions for $wrapper
            this.$wrapper = $wrapper;

            this.swalFormOptions = swalFormOptions;

            this.swalConfirmOptions = swalConfirmOptions;
            this.toastOptions = toastOptions;

            this.loadRows();
        }

        static get _selectors() {
            return {
                table: '.js-manager-table',
                rowTemplate : '#js-manager-row-template'
            };
        }

        /**
         * Loads all entities via ajax and create its respective row into the table.
         */
        loadRows() {
            $.ajax({
                url: Routing.generate(this.entityType + '_list'),
                success: (data) => {
                    $.each(data.items, (index, entity) => {
                        this._addRow(entity);
                    });
                }
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

            $table.find('tbody').append(this._createRow(entity));

            $table.data('length', entity.index)
        }

        /**
         * Create a row base on row template.
         *
         * @param {{
         *          id: int,
         *          date: string,
         *          beneficiaryParty: {name: string, accountNo: string},
         *          debtorParty: {name: string, accountNo: string},
         *          amount: float
         *        }} entity
         *
         * @return {Object}
         *
         * @private
         */
        _createRow(entity) {
            const tplText = $(CRUDManage._selectors.rowTemplate).html();
            const tpl = _.template(tplText);

            const html = tpl(entity);

            return $.parseHTML(html);
        }

        /**
         * Set the form.
         *
         * @param form
         */
        withForm(form) {
            this.form = form;
        }

        /**
         * Enables a create button for the table and adds it handler function.
         */
        withCreateButton() {
            // Delegate selector
            this.$wrapper.on(
                'click',
                '.js-entity-create',
                this.handleCreate.bind(this)
            );
        }

        /**
         * Handle on click event for create button.
         * Create a new row when create success.
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

        /**
         * Create a form to create or edit entity.
         *
         * @param {Object} data Use to pre populate the from.
         *
         * @return {*|Promise|Promise<T | never>}
         *
         * @private
         */
        _createFrom(data = null) {
            // Set the action to performance.
            let action = data === null ? 'create' : 'update';

            // Build form html base on the template.
            const html = this.form.html();

            // The options use to show the form inside the modal and how to parser the inputs.
            const formOptions = this.form.formOptions(action);

            // Swal form modal
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
                    // Getting form data.
                    const $form = $(swalForm.getContainer()).find(this.form.selector);
                    const formData = {};

                    $.each($form.serializeArray(), (key, fieldData) => {
                        formData[fieldData.name] = fieldData.value
                    });

                    // Sending the data to the server.
                    let url, method = '';

                    if (action === 'create') {
                        url = Routing.generate(this.entityType + '_new');
                        method = 'POST';
                    } else {
                        url = Routing.generate(this.entityType + '_edit', {id: data.id});
                        method = 'PUT';
                    }

                    return this._sendRPC(url, method, formData)
                        // Catches response error
                        .catch((errorsData) => {
                            this.form.mapErrors($form, errorsData.errors);

                            return false;
                        });
                }
            }).then((result) => {
                // Show popup with success message
                if (result.value) {
                    this._showStatusMessage(formOptions.text.toastTitleText);
                }

                return result
            }).catch((arg) => {
                // canceling is cool!
                console.log(arg)
            });
        }

        /**
         * Send a remote procedure call to the server.
         *
         * @param url
         * @param method
         * @param formData
         * @return {Promise<any>}
         * @private
         */
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
         * Show action success message.
         *
         * @param titleText
         * @private
         */
        _showStatusMessage(titleText) {
            const toast = Swal.mixin(this.toastOptions);

            toast.fire({
                type: 'success',
                titleText
            });
        }

        /**
         * Enables a edit button for the rows table and adds it handler function.
         */
        withEditButton() {
            // Delegate selector
            this.$wrapper.on(
                'click',
                '.js-entity-edit',
                this.handleEdit.bind(this)
            );
        }

        /**
         * Handle on click event for edit buttons.
         * Send a request to the server to get the entity data and pre populate the form with it.
         * Update the row when update success.
         *
         * @param e
         */
        handleEdit(e) {
            e.preventDefault();

            const $row = $(e.currentTarget).closest('tr');
            const id = $row.data('id');

            let getUrl = Routing.generate(this.entityType + '_get', {id});

            this._sendRPC(getUrl, 'GET')
                .then((data) => {
                    this._createFrom(data.item)
                        // update the row by creating a new row base on the row template and
                        // replace the old row
                        .then((result) => {
                            if (result.value) {
                                let entity = result.value.item;
                                entity.index = $row.data('i');

                                $row.fadeOut('normal', () => {
                                    $row.replaceWith(this._createRow(entity));
                                });
                            }
                        });
                });
        }

        /**
         * Enables a delete button for the rows table and adds it handler function.
         */
        withDeleteButton() {
            // Delegate selector
            this.$wrapper.on(
                'click',
                '.js-entity-delete',
                this.handlerDelete.bind(this)
            );
        }

        /**
         * Handle on click event for row delete buttons.
         * Remove the row when delete success.
         *
         * @param e The event
         */
        handlerDelete(e) {
            e.preventDefault();

            // Setting form data.
            const $form = $(e.currentTarget);
            const $row = $form.closest('tr');

            const itemTitle = $form.data('title');
            const id = $form.data('id');

            // Create delete text confirmation.
            const text = this.swalConfirmOptions.text.replace(/\{0\}/g, '"' + itemTitle + '"');

            // Swal confirmation modal
            const swalConfirm = Swal.mixin(this.swalConfirmOptions);

            swalConfirm.fire({
                text,
                preConfirm: () => {
                    return this._sendRPC(Routing.generate(this.entityType + '_delete', {id: id}), 'DELETE')
                        // Remove the row from the table.
                        .then(() => {
                            $row.fadeOut('normal', () => {
                                $row.remove();

                                // re calculate row index to reflect the removal;
                                const $rowIndexThs = this.$wrapper.find(CRUDManage._selectors.table).find('.js-manager-row-index');

                                $.each($rowIndexThs, function (index, th) {
                                    $(th).html(index + 1);
                                });
                            });
                        });
                }
            }).then((result) => {
                // Show popup with success message
                if (result.value) {
                    this._showStatusMessage(this.form.formOptions('delete').text.toastTitleText)
                }
            }).catch((arg) => {
                // canceling is cool!
            });
        }
    }

    /**
     * Form manage how a form should be build when a crud manager invokes a create or an update action.
     */
    class Form {
        constructor(swalFormOptionsText, template = '#js-manager-form-template', selector = '.js-entity-create-from') {
            this.swalFormOptionsText = swalFormOptionsText;

            this.template = template;
            this.selector = selector;
        }

        /**
         * Form html code.
         *
         * @return {*}
         */
        html() {
           const tplText = $(this.template).html();
           const tpl = _.template(tplText);
           const html = tpl();

           return html;
        }

        /**
         * Defines from options base on the action use by crud manage when an entity is create, update and remove.
         *
         * @param {string} action
         */
        formOptions(action = 'create') {
            let formOptions = {};

            switch (action) {
                case 'create':
                    formOptions = {
                        text: this.swalFormOptionsText.create,
                        onBeforeOpen: this.onBeforeOpen.bind(this)
                    };

                    break;
                case 'update':
                    formOptions = {
                        text: this.swalFormOptionsText.update,
                        onBeforeOpen: this.onBeforeOpen.bind(this)
                    };
                    break;
                case 'delete':
                    formOptions = {
                        text: this.swalFormOptionsText.delete
                    };

                    break;
            }

            return formOptions;
        }

        /**
         * Defines how inputs inside the form must be parser.
         *
         * @param {Object} data
         * @param $wrapper
         */
        onBeforeOpen(data, $wrapper) {
            $('[data-datepickerenable="on"]').datetimepicker();

            if (data) {
                let $form = $wrapper.find(this.selector);
                for (const property in data) {
                    let $input = $form.find('#' + property);

                    if (property == 'date') {
                        $input.val(
                            moment(new Date(data[property])).format('DD/MM/YYYY')
                        ).change();

                        continue;
                    }

                    if (property == 'beneficiaryParty' || property == 'debtorParty' ) {
                        let inputData = data[property]
                        $input.append(new Option(inputData.name + " - " + inputData.accountNo, inputData.id));

                        $input.val(inputData.id);

                        continue;
                    }

                    $input.val(data[property]);
                }
            }

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


        mapErrors($form, errorData) {
            // Remove form errors
            $form.find('.js-field-error').remove();
            $form.find('.form-group').removeClass('has-error');

            // Add errors
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
    }

    window.CRUDManage = CRUDManage;
    window.Form = Form;

})(window, jQuery, Routing, Swal, moment);
