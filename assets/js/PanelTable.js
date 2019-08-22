'use strict';

import Table from "./Components/Table";
import Routing from "./Components/Routing";
import CreateButton from "./Components/CreateButtonForm";

import $ from 'jquery';
import _ from 'underscore';

import '../css/PanelTable.scss';
import EditRowButton from "./Components/EditRowButton";
import DeleteRowButton from "./Components/DeleteRowButton";

class PanelTable extends Table {
    constructor(options) {
        let _options = _.defaults(options || {}, {
            selectors: _.defaults(options.selectors || {}, PanelTable._selectors),
        });

        super(_options);

        this.entityType = _options.entityType;

        // Start binding functions for $wrapper
        this.$wrapper = _options.wrapper;

        // buttons
        this.buttons = [];
    }

    static get _selectors() {
        return {
            // containers
            table: '.js-table',

            // expanded show/hide column
            extraCellShown: '.js-manager-table-extra-cell-show',
            extraCellHide: '.js-manager-table-extra-cell-hide',

            // pagination
            perPageContainer: '.js-table-header-per-page-container',
            showPerPageTemplate: '#js-table-show-per-page-template',
            showPerPage: '.js-table-show-per-page',
            pagination: '.js-table-pagination',
            paginationInfo: '.js-table-pagination-info',

            // serach
            searchContainer: '.js-table-header-search-container',
            searchTemplate: '#js-table-search-template',
            searchBox: '.js-table-search',
            searchClearButton: '.js-table-search-clear',

            // row buttons
            rowButtons: '.js-table-row-buttons',
        }
    }

    addButton(button) {
        this.buttons.push(button);
    }

    render() {
        let $wrapper = this.$wrapper;

        $.each(this.buttons, function (index, button) {
            button.render($wrapper);
        });

        super.render();
    }

    /**
     * Loads all entities via ajax and create its respective row into the table.
     */
    loadRows() {
        $.ajax({
            url: Routing.generate(this.entityType + '_list'),
            // url: this.routing(this.entityType, 'list'),
            success: (data) => {
                super.setData(data);
            }
        });
    }
}

global.PanelTable = PanelTable;
global.CreateButton = CreateButton;
global.EditRowButton = EditRowButton;
global.DeleteRowButton = DeleteRowButton;
