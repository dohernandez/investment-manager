'use strict';

import Form from './Components/Form';
import Table from './Components/Table';
import Template from "./Components/Template";
import InvestmentManagerClient from './Components/InvestmentManagerClient';

import Routing from './Components/Routing';
import Swal from 'sweetalert2';
import $ from 'jquery';
import _ from 'underscore';

import 'twbs-pagination';

import '../css/CrudManager.scss';


/**
 * CRUD Manage Table for adding, updating and removing rows (entities) in the table defined in
 * @see templates/Components/Table/crud-manage-table.html.twig.
 *
 */
class CRUDManage extends Table {
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
        super(options);

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
            selectors: _.defaults(options.selectors || {}, CRUDManage._selectors),
            sort: null,
            searchFunc: null,
        });

        this.entityType = _options.entityType;

        this.selectors = _.defaults(_options.selectors || {}, CRUDManage._selectors);

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

        this.sort = _options.sort;

        // set whether the table is shows all it cols
        this.expanded = 0;

        // search
        this.searchFunc = _options.searchFunc;
        this.searchButton = false;
        this.afterCleanSearchFunc = null;
        this.afterSearchFunc = null;

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

            // template
            rowTemplate: '#js-manager-row-template',
            createButtonTemplate: '#js-manager-create-button-template',

            manageButtons: '.js-manage-buttons'
        };
    }

    setRouteGenerating(routing) {
        this.routing = routing.bind(this);
    }

    render(renderFunc) {
        // render create button
        if (this.createButton) {
            let $createButton = Template.compile(this.selectors.createButtonTemplate);
            this.$wrapper.find(this.selectors.createButtonContainer)
                .append($createButton);
        }

        super.render(renderFunc);

        // render manage col with
        let $manageButtons = this.$wrapper.find(this.selectors.manageButtons);

        $manageButtons.css( "width", this.buttonColWidth);

        this.loadRows();
    }

    /**
     * Loads all entities via ajax and create its respective row into the table.
     */
    loadRows() {
        $.ajax({
            url: this.routing(this.entityType, 'list'),
            success: (data) => {
                super.setData(data);
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

    /**
     * @deprecated Use createRow instead
     * @param compile
     * @private
     */
    _createRow(compile) {
        compile.editButton = this.editButton;
        compile.deleteButton = this.deleteButton;
        compile.viewButton = this.viewButton;

        return super.createRow(compile);
    }

    createRow(compile) {
        compile.editButton = this.editButton;
        compile.deleteButton = this.deleteButton;
        compile.viewButton = this.viewButton;

        return super.createRow(compile);
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
    withCreateButton(selector, handler) {
        this.createButton = true;

        let func = this.handleCreate;

        if (handler) {
            func = handler;
        }

        let sel = '.js-entity-create';

        if (selector) {
            sel = selector;
        }

        // Delegate selector
        this.$wrapper.on(
            'click',
            sel,
            func.bind(this)
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

        this.createFrom()
            .then((result) => {
                if (result.value) {
                    let entity = result.value.item;

                    this.addEntity(entity);
                }
            });
    }

    /**
     * Create a form to create or edit entity.
     *
     * @param {Object} data Use to pre populate the from.
     * @param {string} force Use to for an action.
     *
     * @return {*|Promise|Promise<T | never>}
     */
    createFrom(data = null, force = '') {
        // Set the action to performance.
        let action = data === null ? 'create' : 'update';

        if (force == 'create' || force == 'update') {
            action = force;
        }

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

                return InvestmentManagerClient.sendRPC(url, method, formData)
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
     * Add entity.
     *
     * @param {Object} entity
     */
    addEntity(entity) {
        super.addRecord(entity);

        this.form.onCreated(entity);
    }

    /**
     * Enables a edit button for the rows table and adds it handler function.
     */
    withEditButton(selector) {
        this.editButton = true;
        this.withExtraButton();

        let sel = '.js-entity-edit';

        if (selector) {
            sel = selector;
        }

        // Delegate selector
        this.$wrapper.on(
            'click',
            sel,
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

        let entity = this.getRecord(id);

        // fetch the entity from the server because it is not loaded yet.
        // So far there is not clear use case where the application hit this scope, but we will like to
        // keep it.
        if (entity === null) {
            let getUrl = this.routing(this.entityType, 'get', id);

            InvestmentManagerClient.sendRPC(getUrl, 'GET')
                .then((data) => {
                    this.createFrom(data.item)
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

        this.createFrom(entity)
        // update the row by creating a new row base on the row template and
        // replace the old row
            .then((result) => {
                if (result.value) {
                    let entity = result.value.item;

                    this.replaceRecord(entity, id);

                    $row.fadeOut('normal', () => {
                        $row.replaceWith(this._createRow(entity));
                    });
                }
            });
    }

    /**
     * Enables a delete button for the rows table and adds it handler function.
     */
    withDeleteButton(selector) {
        this.deleteButton = true;
        this.withExtraButton();

        let sel = '.js-entity-delete';

        if (selector) {
            sel = selector;
        }

        // Delegate selector
        this.$wrapper.on(
            'click',
            sel,
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

        const itemTitle = $form.data('title');
        const id = $form.data('id');

        // Create delete text confirmation.
        const text = this.swalConfirmOptions.text.replace(/\{0\}/g, '"' + itemTitle + '"');

        // Swal confirmation modal
        const swalConfirm = Swal.mixin(this.swalConfirmOptions);

        swalConfirm.fire({
            text,
            preConfirm: () => {
                return InvestmentManagerClient.sendRPC(this.routing(this.entityType, 'delete', id), 'DELETE')
                    // Remove the row from the table.
                    .then(() => {
                        super.removeRecord(id);
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
     * @deprecated use constructor option.
     * Example:
     *          New CRUDManage({
     *            ...,
     *            showSearchBox: true
     *          })
     *
     * @param {function} searchFunc
     */
    withSearch(searchFunc) {
        this.showSearchBox = true;

        if (searchFunc !== null) {
            this.searchFunc = searchFunc;
        }
    }

    /**
     * @deprecated use constructor option.
     * Example:
     *          New CRUDManage({
     *            ...,
     *            afterCleanSearchFunc: function () {},
     *          })
     *
     * Callback function after clean search
     */
    withAfterCleanSearch(afterCleanSearchFunc) {
        this.afterCleanSearchFunc = afterCleanSearchFunc
    }


    /**
     * @deprecated use constructor option.
     * Example:
     *          New CRUDManage({
     *            ...,
     *            afterSearchFunc: function (search) {},
     *          })
     *
     * Callback function after search
     */
    withAfterSearch(afterSearchFunc) {
        this.afterSearchFunc = afterSearchFunc
    }


    /**
     * Enables a view button for the table and adds it handler function.
     */
    withViewButton(selector, swalViewOptions, viewTemplate) {
        this.viewButton = true;
        this.withExtraButton();
        this.swalViewOptions = swalViewOptions;
        this.viewTemplate = viewTemplate;

        let sel = '.js-entity-view';

        if (selector) {
            sel = selector;
        }

        // Delegate selector
        this.$wrapper.on(
            'click',
            sel,
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

        let recordIndex = this.indexOfById(id);
        let entity = recordIndex !== null ? this.records[recordIndex] : null;

        // Build form html base on the template.
        const html = Template.compile(this.viewTemplate, entity);
        const title = Template.compile('#js-view-title-template', entity);

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
