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
     *      toastOptions: {Object},
     *      expanded: boolean,
     * }} options
     */
    constructor(options) {
        let _options = _.defaults(options || {}, {
            pagination: false,
            showPerPage: 10,
            viewButton: false,
            createButton: false,
            editButton: false,
            deleteButton: false,
            totalButtons: 0,
            buttonColWidth: 0,
            swalViewOptions: null,
            viewTemplate: '',
        });

        this.entityType = _options.entityType;

        // Start binding functions for $wrapper
        this.$wrapper = _options.wrapper;

        this.swalFormOptions = _options.swalFormOptions;

        this.swalConfirmOptions = _options.swalConfirmOptions;
        this.toastOptions = _options.toastOptions;

        // buttons
        this.createButton = _options.createButton;
        this.editButton = _options.editButton;
        this.deleteButton = _options.deleteButton;

        this.viewButton = _options.viewButton;
        this.swalViewOptions = _options.swalViewOptions;
        this.viewTemplate = _options.viewTemplate;

        this.totalButtons = _options.totalButtons;
        this.buttonColWidth = _options.buttonColWidth;

        // The total records object of array.
        this.records = [];
        // The total number of records fetch from database.
        this.totalRecords = 0;

        // Variables related to pagination
        this.pagination = _options.pagination;
        // The records per page will show into table.
        this.showPerPage = _options.showPerPage;
        // The current page number.
        this.page = 1;
        // The total pages based on records.
        this.totalPages = 0;

        // set whether the table is shows all it cols
        this.expanded = 0;

        // search
        this.searchFunc = null;

        this.routing = this._getRouting;
    }

    static get _selectors() {
        return {
            // containers
            table: '.js-manager-table',
            header : '.js-manage-header',
            footer : '.js-manage-footer',
            createButtonContainer: '.js-manage-header-create-button-container',
            searchContainer: '.js-manage-header-search-container',
            perPageContainer: '.js-manage-header-per-page-container',

            // template
            rowTemplate: '#js-manager-row-template',
            createButtonTemplate: '#js-manager-create-button-template',
            searchTemplate: '#js-manager-search-template',
            showPerPageTemplate: '#js-manager-show-per-page-template',

            // pagination
            showPerPage: '.js-manage-show-per-page',
            pagination: '.js-manage-pagination',
            paginationInfo: '.js-manage-pagination-info',

            // serach input
            search: '.js-manage-search',
            searchClear: '.js-manage-search-clear',

            // show/hide column
            extraCell: '.js-manager-table-extra-cell',
            extraCellShown: '.js-manager-table-extra-cell-show',

            manageButtons: '.js-manage-buttons'
        };
    }

    setRouteGenerating(routing) {
        this.routing = routing.bind(this);
    }

    render() {
        // render create button
        if (this.createButton) {
            let $createButton = this._compileTemplate(CRUDManage._selectors.createButtonTemplate);
            this.$wrapper.find(CRUDManage._selectors.createButtonContainer)
                .append($createButton);
        }

        // render search input
        if (this.searchFunc !== null) {
            let $search = this._compileTemplate(CRUDManage._selectors.searchTemplate);
            this.$wrapper.find(CRUDManage._selectors.searchContainer)
                .append($search);
        }

        // render pagination elements
        if (this.pagination) {

            // add on change for show per page
            this.$wrapper.on(
                'change',
                CRUDManage._selectors.showPerPage,
                (e) => {
                    e.preventDefault();

                    const perPage = $(e.currentTarget).val();
                    this.showPerPage = parseInt(perPage);
                    this.page = 1;

                    this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

                    this._refreshPagination();
                }
            );

            let $perPage = this._compileTemplate(CRUDManage._selectors.showPerPageTemplate);
            this.$wrapper.find(CRUDManage._selectors.perPageContainer)
                .append($perPage);
        }

        // render manage col with
        let $manageButtons = this.$wrapper.find(CRUDManage._selectors.manageButtons);

        console.log($manageButtons.width());
        console.log(this.buttonColWidth);
        $manageButtons.css( "width", this.buttonColWidth );
        console.log($manageButtons.width());
        //
        // switch (this.totalButtons) {
        //     case 1:
        //         $manageButtons.css( "width", "54" );
        //         break;
        //     case 2:
        //         $manageButtons.css( "width", "90" );
        //         break;
        //     case 3:
        //         $manageButtons.css( "width", "128" );
        //         break;
        //     case 4:
        //         $manageButtons.css( "width", "165" );
        //         break;
        //     default:
        // }

        this.loadRows();
    }

    _compileTemplate(selector, compile) {
        const tplText = $(selector).html();
        const tpl = _.template(tplText);

        let html = '';

        if (compile !== null || typeof compile !== 'undefined') {
            html = tpl(compile);
        }

        return $.parseHTML(html);
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

                if (!this.pagination) {
                    $.each(this.records, (index, entity) => {
                        this.addRow(entity, index);
                    });

                    return;
                }

                this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

                let $pagination = this.$wrapper.find($(CRUDManage._selectors.pagination));
                $pagination.each((index, ul) => {
                    let $ul = $(ul);
                    this._showPagination($ul);
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
                throw Error('Endpoint ' + endpoint + ' not supported');
        }

        return route
    }

    _refreshPagination(options) {
        let $parent = this._removePagination();

        let $pagination = new $('<ul id="pagination" class="pagination-sm pagination js-manage-pagination pull-right"></ul>')
        $parent.append($pagination);

        this._showPagination($pagination, options);
    }

    _removePagination(){
        let $pagination = this.$wrapper.find($(CRUDManage._selectors.pagination));
        let $parent = $pagination.parent();

        $pagination.remove();

        return $parent;
    }

    _showPagination($pagination, options) {
        // init all the pagination variables
        let _options = _.defaults(options || {}, {
            totalPages: this.totalPages,
            showPerPage: this.showPerPage,
            records: this.records,
            page: this.page,
            totalRecords: this.totalRecords,
        });

        let showPerPage = _options.showPerPage;
        let records = _options.records;
        let totalPages = _options.totalPages;
        let currentPage = _options.page;
        let totalRecords = _options.totalRecords;

        // tweak pagination info
        let $paginationInfo = this.$wrapper.find($(CRUDManage._selectors.paginationInfo));
        $paginationInfo.parent().css('margin', '24px 0');

        self = this;

        // init pagination object
        $pagination.twbsPagination({
            totalPages: totalPages,
            visiblePages: 6,
            onPageClick: function (event, page) {
                let displayRecordsIndex = Math.max(page - 1, 0) * showPerPage;
                let endRec = (displayRecordsIndex) + showPerPage;

                let displayRecords = records.slice(displayRecordsIndex, endRec);

                self.cleanRows();

                // create the rows of the table based on the records to display
                $.each(displayRecords, (index, entity) => {
                    self.addRow(entity, displayRecordsIndex + index);
                });

                // update pagination text
                let pageInfo = $paginationInfo.data('text')
                    .replace(/:from/g, displayRecordsIndex + 1)
                    .replace(/:to/g, displayRecordsIndex + displayRecords.length)
                    .replace(/:of/g, totalRecords)
                ;

                $paginationInfo.html(pageInfo);

                // to keep the current page
                self.page = page
            },
            startPage: currentPage
        });

        if (totalRecords <= this.showPerPage) {
            $pagination.hide();
        }
    }

    /**
     * Clean the rows from the table.
     */
    cleanRows() {
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
     */
    addRow(entity, index) {
        const $table = this.$wrapper.find(CRUDManage._selectors.table);

        let data = entity;
        data.index = index + 1;
        let row = this._createRow(data);

        $table.find('tbody').append(row);

        if (this.expanded) {
            let $cell = $table.find(CRUDManage._selectors.extraCell);
            $cell.addClass(CRUDManage._selectors.extraCellShown.slice(1));
        }
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
     *          editButton: boolean
     *          deleteButton: boolean
     *          viewButton: boolean
     *        }} compile
     *
     * @return {Object}
     *
     * @private
     */
    _createRow(compile) {
        compile.editButton = this.editButton;
        compile.deleteButton = this.deleteButton;
        compile.viewButton = this.viewButton;

        return this._compileTemplate(CRUDManage._selectors.rowTemplate, compile);
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
        this.createButton = true;

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
                    let entity = result.value.item;

                    this.records.push(entity);
                    this.totalRecords++;
                    this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

                    this._refreshPagination();

                    this.form.onCreated(entity);
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
        this.editButton = true;
        this.withExtraButton();

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

        // find entity to edit
        const $row = $(e.currentTarget).closest('tr');
        const id = $row.data('id');

        let entity = null;
        let recordIndex = 0;
        for (let i = 0; i < this.records.length; i++) {
            let record = this.records[i];

            if (record.id === id) {
                entity = record;
                recordIndex = i;

                break;
            }
        }

        // fetch the entity from the server because it is not loaded yet.
        // So far there is not clear use case where the application hit this scope, but we will like to
        // keep it.
        if (entity === null) {
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

            return
        }

        this._createFrom(entity)
        // update the row by creating a new row base on the row template and
        // replace the old row
            .then((result) => {
                if (result.value) {
                    let entity = result.value.item;

                    this.records[recordIndex] = entity;
                    entity.index = $row.data('i');

                    $row.fadeOut('normal', () => {
                        $row.replaceWith(this._createRow(entity));
                    });
                }
            });
    }

    /**
     * Enables a delete button for the rows table and adds it handler function.
     */
    withDeleteButton() {
        this.deleteButton = true;
        this.withExtraButton();

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
                        for( let i = 0; i < this.records.length; i++){
                            if ( this.records[i].id === id) {
                                this.records.splice(i, 1);
                            }
                        }

                        this.totalRecords--;

                        let newTotalPages = Math.ceil(this.totalRecords / this.showPerPage);
                        if (this.totalPages !== newTotalPages ) {
                            // Once here it is because the newTotalPages is minor than this.totalPages.
                            // This is a delete method this.totalRecords always decrease, therefore
                            // this.totalPages will never be bigger
                            if (this.page > newTotalPages) {
                                this.page = newTotalPages ? newTotalPages : 1;
                            }

                            this.totalPages = newTotalPages;
                        }

                        this._refreshPagination();
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

    /**
     *
     * @param {function} searchFunc
     */
    withSearch(searchFunc) {
        this.searchFunc = searchFunc;

        this.$wrapper.on(
            'click',
            CRUDManage._selectors.searchClear,
            this.handlerSearchClear.bind(this)
        );

        this.$wrapper.on(
            'keyup',
            CRUDManage._selectors.search,
            this.handlerSearch.bind(this)
        );
    }

    /**
     * Clear the search input
     * @param e
     */
    handlerSearchClear(e) {
        e.preventDefault();

        let $searchClear = $(e.currentTarget);
        let $search = this.$wrapper.find(CRUDManage._selectors.search);

        $search.val('');
        $searchClear.hide();

        if (!this.pagination) {
            this.cleanRows();

            // create the rows of the table based on the records to display
            $.each(this.records, (index, entity) => {
                this.addRow(entity, index);
            });

            return;
        }

        this.page = 1;
        this._refreshPagination();
    }

    /**
     * Handle on keyup event for search input.
     *
     * @param e The event
     */
    handlerSearch(e) {
        e.preventDefault();

        let $search = $(e.currentTarget);
        let $searchClear = this.$wrapper.find(CRUDManage._selectors.searchClear);

        let search = $search.val();
        if (search == '') {
            // clear pagination and hide clear button
            $searchClear.hide();
        } else {
            // show clear button
            $searchClear.show();
        }

        let matches = this.searchFunc(this.records, $search.val());
        if (matches === null) {
            if (!this.pagination) {
                this.cleanRows();

                // create the rows of the table based on the records to display
                $.each(this.records, (index, entity) => {
                    this.addRow(entity, index);
                });

                return;
            }

            this.page = 1;
            this._refreshPagination();

            return;
        }

        if (!this.pagination) {
            this.cleanRows();

            // create the rows of the table based on the records to display
            $.each(matches, (index, entity) => {
                this.addRow(entity, index);
            });

            return;
        }

        let totalRecords = matches.length;

        try {
            this._refreshPagination({
                records: matches,
                totalRecords: totalRecords,
                totalPages: Math.ceil(totalRecords / this.showPerPage),
                page: 1,
            });
        } catch (err) {
            let $paginationInfo = this.$wrapper.find($(CRUDManage._selectors.paginationInfo));

            $paginationInfo.html('');

            this.cleanRows();
        }
    }

    /**
     * Set whether the table is expanded or not
     * @param expanded
     */
    setExpanded(expanded) {
        this.expanded = expanded;
    }

    toggleExpanded() {
        this.setExpanded(!this.expanded);

        const $table = this.$wrapper.find(CRUDManage._selectors.table);

        if (this.expanded) {
            let $cell = $table.find(CRUDManage._selectors.extraCell);

            $cell.fadeIn('fast', function () {
                $cell.addClass(CRUDManage._selectors.extraCellShown.slice(1));
            });
        } else {
            let $cell = $table.find(CRUDManage._selectors.extraCell);

            $cell.fadeOut('fast', function () {
                $cell.removeClass(CRUDManage._selectors.extraCellShown.slice(1));
            });
        }
    }

    /**
     * Enables a view button for the table and adds it handler function.
     */
    withViewButton(swalViewOptions, viewTemplate) {
        this.viewButton = true;
        this.withExtraButton();
        this.swalViewOptions = swalViewOptions;
        this.viewTemplate = viewTemplate;

        // Delegate selector
        this.$wrapper.on(
            'click',
            '.js-entity-view',
            this.handleView.bind(this)
        );
    }


    /**
     * Handle on click event for create button.
     * Create a new row when create success.
     *
     * @param e
     */
    handleView(e) {
        e.preventDefault();

        // find entity to view
        const $row = $(e.currentTarget).closest('tr');
        const id = $row.data('id');

        let entity = null;
        let recordIndex = 0;
        for (let i = 0; i < this.records.length; i++) {
            let record = this.records[i];

            if (record.id === id) {
                entity = record;
                recordIndex = i;

                break;
            }
        }

        // Build form html base on the template.
        const html = this._compileTemplate(this.viewTemplate, entity);
        const title = this._compileTemplate('#js-view-title-template', entity);

        // Swal form modal
        const swalForm = Swal.mixin(this.swalViewOptions);

        return swalForm.fire({
            html,
            title,
            onBeforeOpen: () => {
                this.form.onPreview(entity);
            },
        });
    }

    /**
     * Enables a view button for the table and adds it handler function.
     */
    withExtraButton(extraWith) {
        this.totalButtons++;

        if (extraWith) {
            this.buttonColWidth += extraWith;
        }

        if (this.totalButtons !== 1) {
            this.buttonColWidth += 37;
        } else {
            this.buttonColWidth = 54;
        }
    }
}

global.CRUDManage = CRUDManage;
