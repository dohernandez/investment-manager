'use strict';

import Form from './Components/Form';
import Table from './Components/Table';
import Template from "./Components/Template";
import InvestmentManagerClient from './Components/InvestmentManagerClient';
import CreateButton from "./Components/CreateButton";
import Button from "./Components/Button";
import EditButton from "./Components/EditButton";

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
            viewButton: false,
            createButton: false,
            editButton: false,
            deleteButton: false,
            totalButtons: 0,
            buttonColWidth: 0,
            swalViewOptions: null,
            viewTemplate: '',
            selectors: _.defaults(options.selectors || {}, CRUDManage._selectors),
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

        this.routing = this._getRouting;

        this.tableButtons = [];
        this.rowButtons = [];
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
        let $wrapper = this.$wrapper;

        $.each(this.tableButtons, function (index, button) {
            button.render($wrapper);
        });

        super.render(renderFunc);

        $.each(this.rowButtons, function (index, button) {
            button.register($wrapper);
        });

        // render manage col with
        let $manageButtons = $wrapper.find(this.selectors.manageButtons);

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

    createRow(row) {
        row.editButton = this.editButton;
        row.deleteButton = this.deleteButton;
        row.viewButton = this.viewButton;

        return super.createRow(row);
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

        let sel = '.js-entity-create';

        if (selector) {
            sel = selector;
        }

        let createButton = null;
        if (handler) {
            createButton = new Button(
                sel,
                this.selectors.createButtonTemplate,
                this.selectors.createButtonContainer,
                handler
            );
        } else {
            createButton = new CreateButton(
                sel,
                this.selectors.createButtonTemplate,
                this.selectors.createButtonContainer
            );
        }

        this.addTableButton(createButton);
    }

    addTableButton(button) {
        button.setManager(this);

        this.tableButtons.push(button);
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

        let url = '';
        if (action === 'create') {
            url = this.routing(this.entityType, 'new');
        } else {
            url = this.routing(this.entityType, 'edit', data.id);
        }

        // Swal form modal
        const swalForm = Swal.mixin(this.swalFormOptions);

        return this.form.create(
            swalForm,
            url,
            action,
            data,
            force
        ).then((result) => {
            // Show popup with success message
            if (result.value) {
                this._showStatusMessage(this.form.toastTitleText(action));
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

        let editButton = new EditButton(
            sel,
            this.selectors.createButtonTemplate,
            this.selectors.createButtonContainer
        );

        this.addRowButton(editButton);
    }

    addRowButton(button) {
        button.setManager(this);

        this.rowButtons.push(button);
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
                this._showStatusMessage(this.form.toastTitleText('delete'))
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
