'use strict';

import Form from './Components/Form';
import $ from 'jquery';
import Swal from 'sweetalert2';
import Routing from './Components/Routing';
import _ from 'underscore';

import 'twbs-pagination';

import '../css/CrudManager.scss';


/**
 * CRUD Manage Table for adding, updating and removing rows (entities) in the table defined in
 * @see templates/Components/Table/crud-manage-table.html.twig.
 *
 */
class CRUDManage {
    /**
     *
     * @param {{
     *      entityType: string,
     *      wrapper: {Object},
     *      swalFormOptions: {Object},
     *      swalConfirmOptions: {Object},
     *      toastOptions: {Object}
     * }} options
     */
    constructor(options) {
        let _options = _.defaults(options || {}, {
            showPerPage: 0,
        });

        this.entityType = _options.entityType;

        // Start binding functions for $wrapper
        this.$wrapper = _options.wrapper;

        this.swalFormOptions = _options.swalFormOptions;

        this.swalConfirmOptions = _options.swalConfirmOptions;
        this.toastOptions = _options.toastOptions;

        // The total records object of array.
        this.records = [];

        // The records per page will show into table.
        this.showPerPage = _options.showPerPage;
        // The total number of records fetch from database.
        this.totalRecords = 0;
        // The current page number.
        this.page = 1;
        // The total pages based on records.
        this.totalPages = 0;

        this.routing = this._getRouting
    }

    static get _selectors() {
        return {
            showPerPage: '.js-manage-show-per-page',
            table: '.js-manager-table',
            rowTemplate : '#js-manager-row-template',
            pagination: '.js-manage-pagination',
            paginationInfo: '.js-manage-pagination-info'
        };
    }

    setRouteGenerating(routing) {
        this.routing = routing.bind(this);
    }

    /**
     * Loads all entities via ajax and create its respective row into the table.
     */
    loadRows() {
        $.ajax({
            url: this.routing(this.entityType, 'list'),
            success: (data) => {
                this.records = data.items;
                this.totalRecords = data.items.length;

                if (this.showPerPage > 0 && this.totalRecords > this.showPerPage) {
                    // Delegate selector
                    this.$wrapper.on(
                        'change',
                        CRUDManage._selectors.showPerPage,
                        (e) => {
                            e.preventDefault();

                            const perPage = $(e.currentTarget).val();
                            this.showPerPage = parseInt(perPage);
                            this.page = 1;

                            this._refreshPagination();
                        }
                    );

                    let $pagination = this.$wrapper.find($(CRUDManage._selectors.pagination));
                    $pagination.each((index, ul) => {
                        let $ul = $(ul)
                        this._showPagination($ul);
                    });

                    return;
                }

                this._cleanRows();

                $.each(this.records, (index, entity) => {
                    this._addRow(entity, index);
                });
            }
        });
    }

    _getRouting(entityType, endpoint, id = null) {
        let route = '';
        let param = {};

        if (id) {
            param['id'] = id;
        }

        switch (endpoint) {
            case 'list':
                route = Routing.generate(entityType + '_list', param);

                break;
            case 'new':
                route = Routing.generate(entityType + '_new', param);

                break;
            case 'get':
                route = Routing.generate(entityType + '_get', param);

                break;
            case 'edit':
                route = Routing.generate(entityType + '_edit', param);

                break;
            case 'delete':
                route = Routing.generate(entityType + '_delete', param);

                break;
            default:
                throw 'Endpoint ' + endpoint + ' not supported';
        }

        return route
    }

    _refreshPagination() {
        let $pagination = this.$wrapper.find($(CRUDManage._selectors.pagination));
        let $parent = $pagination.parent();

        $pagination.remove();

        $pagination = new $('<ul id="pagination" class="pagination-sm pagination js-manage-pagination pull-right"></ul>')
        $parent.append($pagination);

        this._showPagination($pagination);
    }

    _showPagination($pagination) {
        this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

        let $paginationInfo = this.$wrapper.find($(CRUDManage._selectors.paginationInfo));
        $paginationInfo.parent().css('margin-top', '24px');

        self = this;

        let pageInfoText = $paginationInfo.data('text');

        $pagination.twbsPagination({
            totalPages: self.totalPages,
            visiblePages: 6,
            onPageClick: function (event, page) {
                let displayRecordsIndex = Math.max(page - 1, 0) * self.showPerPage;
                let endRec = (displayRecordsIndex) + self.showPerPage;

                self.displayRecords = self.records.slice(displayRecordsIndex, endRec);

                self._cleanRows();

                $.each(self.displayRecords, (index, entity) => {
                    self._addRow(entity, displayRecordsIndex + index);
                });

                let pageInfo = pageInfoText
                    .replace(/:from/g, displayRecordsIndex + 1)
                    .replace(/:to/g, displayRecordsIndex + self.displayRecords.length)
                    .replace(/:of/g, self.totalRecords)
                ;

                $paginationInfo.html(pageInfo);
            }
        });

        if (this.totalRecords <= this.showPerPage) {
            console.log('hiding $pagination');

            $pagination.hide();

            return;
        }

        // margin-top: 24px;
    }

    /**
     * Clean the rows from the table.
     *
     * @private
     */
    _cleanRows() {
        const $table = this.$wrapper.find(CRUDManage._selectors.table);

        $table.find('tbody').html('');
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
     * @param {int} index
     *
     * @private
     */
    _addRow(entity, index) {
        const $table = this.$wrapper.find(CRUDManage._selectors.table);

        entity.index = index + 1;

        $table.find('tbody').append(this._createRow(entity));
    }

    /**
     * Create a row base on row template.
     *
     * @param {Object} entity
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
     * @param {{From}} form
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
                    let item = result.value.item;

                    this._addRow(item);
                    this.form.onCreated(item)
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
                    url = this.routing(this.entityType, 'new');
                    method = 'POST';
                } else {
                    url = this.routing(this.entityType, 'edit', data.id);
                    method = 'PUT';
                }

                return this._sendRPC(url, method, formData)
                    // Catches response error
                    .catch((errorsData) => {
                        $('#swal2-validation-message').empty();

                        if (errorsData.errors) {
                            this.form.mapErrors($form, errorsData.errors);

                            return false;
                        }

                        if (errorsData.message) {
                            $('#swal2-validation-message').append(
                                $('<span></span>').html(errorsData.message)
                            ).show()
                        }

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

        let getUrl = this.routing(this.entityType, 'get', id);

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
                return this._sendRPC(this.routing(this.entityType, 'delete', id), 'DELETE')
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

global.CRUDManage = CRUDManage;
