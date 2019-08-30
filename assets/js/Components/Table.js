'use strict';

import Template from "./Template";
import Form from "./Form";

import _ from 'underscore';
import $ from 'jquery';

import 'twbs-pagination';

class Table {
    constructor(options) {
        let _options = _.defaults(options || {}, {
            pagination: false,
            showPerPage: 10,
            visiblePages: 6,
            sort: null,
            selectors: _.defaults(options.selectors || {}, Table._selectors),
            buttonColWidth: 0,
            afterCleanSearchFunc: null,
            afterSearchFunc: null,
            showSearchBox: true,
            limit: 0,
        });

        // Start binding functions for $wrapper
        this.$wrapper = _options.wrapper;

        this.table = null;
        this.selectors = _options.selectors;

        // set whether the table is shows all it cols
        this.expanded = false;

        // The total records object of array.
        this.records = [];
        // The total number of records fetch from database.
        this.totalRecords = 0;

        // Variables related to pagination
        this.pagination = _options.pagination;
        // The records per page will show into table.
        this.showPerPage = _options.showPerPage;
        // Number of visible pages
        this.visiblePages = _options.visiblePages;
        // The current page number.
        this.page = 1;
        // The total pages based on records.
        this.totalPages = 0;

        this.sort = _options.sort;

        // search
        this.searchFunc = _options.searchFunc;
        this.showSearchBox = _options.showSearchBox === true ? !!this.searchFunc : false;
        this.afterCleanSearchFunc = _options.afterCleanSearchFunc;
        this.afterSearchFunc = _options.afterSearchFunc;

        // row buttons
        this.rowButtons = [];
        this.buttonColWidth = _options.buttonColWidth;

        // limit
        this.limit = _options.pagination === false ? _options.limit : 0;
    }

    static get _selectors() {
        return {
            // containers
            table: '.js-manager-table',

            // expanded show/hide column
            extraCellShown: '.js-manager-table-extra-cell-show',
            extraCellHide: '.js-manager-table-extra-cell-hide',

            // pagination
            perPageContainer: '.js-manage-header-per-page-container',
            showPerPageTemplate: '#js-manager-show-per-page-template',
            showPerPage: '.js-manage-show-per-page',
            pagination: '.js-manage-pagination',
            paginationInfo: '.js-manage-pagination-info',

            // serach
            searchContainer: '.js-manage-header-search-container',
            searchTemplate: '#js-manager-search-template',
            searchBox: '.js-manage-search',
            searchClearButton: '.js-manage-search-clear',

            // row buttons
            rowButtons: '.js-row-buttons'
        }
    }

    getTable() {
        if (!this.table) {
            this.table = this.$wrapper.find(this.selectors.table);
        }

        return this.table;
    }

    setData(data) {
        this.records = data.items;
        this.totalRecords = data.items.length;

        this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

        this.refreshPagination();
    }

    /**
     * Clean the rows from the table.
     */
    cleanRows() {
        const $table = this.getTable();

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
        const $table = this.getTable();

        let data = entity;
        data.index = index + 1;
        let row = this.createRow(data);

        $table.find('tbody').append(row);
    }

    /**
     * Create a row base on row template.
     *
     * @param {Object} data
     *
     * @return {Object}
     *
     * @protected
     */
    createRow(data) {
        return Template.compile(this.selectors.rowTemplate, data);
    }

    getRecord(id) {
        let recordIndex = this.indexOfById(id);

        return recordIndex != null ? this.records[recordIndex] : null;
    }

    /**
     * @param id
     * @return {null|number}
     */
    indexOfById(id) {
        for (let i = 0; i < this.records.length; i++) {
            let record = this.records[i];

            if (record.id === id) {
                return i;
            }
        }

        return null
    }

    addRecord(record) {
        this.records.push(record);
        this.totalRecords++;
        this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

        this.refreshPagination();
    }

    replaceRecord(record, id) {
        let recordIndex = this.indexOfById(id);

        if (recordIndex == null) {
            return;
        }

        this.records[recordIndex] = record;
    }

    removeRecord(id) {
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

        this.refreshPagination();
    }

    // Expanded section
    /**
     * Set whether the table is expanded or not
     * @param expanded
     */
    setExpanded(expanded) {
        this.expanded = expanded;
    }

    /**
     * get whether the table is expanded or not
     */
    isExpanded() {
        return this.expanded;
    }

    toggleExpanded() {
        this.setExpanded(!this.expanded);

        this.toggleTableHeader();
        this.toggleTableCell();
    }

    toggleTableHeader() {
        this.toggleTableExtra('th');
    }

    toggleTableExtra(cell) {
        const $table = this.getTable();

        let $cellShown = $table.find(cell + this.selectors.extraCellShown);
        let $cellHidden = $table.find(cell + this.selectors.extraCellHide);

        $cellShown.addClass(this.selectors.extraCellHide.slice(1));
        $cellShown.removeClass(this.selectors.extraCellShown.slice(1));

        $cellHidden.addClass(this.selectors.extraCellShown.slice(1));
        $cellHidden.removeClass(this.selectors.extraCellHide.slice(1));
    }

    toggleTableCell() {
        this.toggleTableExtra('td');
    }

    refreshExpanded() {
        if (this.isExpanded() === true) {
            this.toggleTableCell();
        }
    }

    /**
     * Create/update the pagination. Due to the way pagination works, this function destroy the current
     * pagination if exists and create new pagination.
     *
     * @param options
     */
    refreshPagination(options) {
        // init all the pagination variables
        let _options = _.defaults(options || {}, this.getDefaultPaginatorOptions());

        if (this.sort) {
            _options.records = _options.records.sort(this.sort);
        }

        if (this.pagination === false) {
            let displayRecords = _options.records;

            if (this.limit) {
                console.log(_options.records);
                displayRecords = _options.records.slice(0, this.limit);
            }

            this.recreateTableWithRecords(displayRecords, 0);

            return;
        }

        // show pagination
        // remove pagination
        let $paginator = this.$wrapper.find(this.selectors.pagination);
        let $parent = $paginator.parent();
        $paginator.remove();

        // recreate the pagination
        $paginator = new $('<ul id="pagination" class="pagination-sm pagination ' + this.selectors.pagination.slice(1) + ' pull-right"></ul>');
        $parent.append($paginator);


        // tweak pagination info
        let $paginationInfo = this.$wrapper.find(this.selectors.paginationInfo);
        $paginationInfo.parent().css('margin', '24px 0');

        // init pagination object
        $paginator.twbsPagination({
            totalPages: _options.totalPages,
            visiblePages: _options.visiblePages,
            onPageClick: function (event, page) {
                let displayRecordsIndex = Math.max(page - 1, 0) * _options.showPerPage;
                let endRec = (displayRecordsIndex) + _options.showPerPage;

                let displayRecords = _options.records.slice(displayRecordsIndex, endRec);

                this.recreateTableWithRecords(displayRecords, displayRecordsIndex);

                // update pagination text
                let pageInfo = $paginationInfo.data('text')
                    .replace(/:from/g, displayRecordsIndex + 1)
                    .replace(/:to/g, displayRecordsIndex + displayRecords.length)
                    .replace(/:of/g, _options.totalRecords)
                ;

                $paginationInfo.html(pageInfo);

                // to keep the current page
                this.page = page
            }.bind(this),
            startPage: _options.page
        });

        if (_options.totalRecords <= _options.showPerPage) {
            $paginator.hide();
        }
    }

    getDefaultPaginatorOptions() {
        return {
            totalPages: this.totalPages,
            showPerPage: this.showPerPage,
            records: this.records,
            page: this.page,
            totalRecords: this.totalRecords,
            visiblePages: this.visiblePages,
        }
    }

    recreateTableWithRecords(records, staringIndex) {
        staringIndex = staringIndex ? staringIndex : 0;

        this.cleanRows();

        $.each(records, (index, entity) => {
            this.addRow(entity, staringIndex + index);
        });

        this.refreshExpanded();
    }

    // Render
    render(renderFunc) {
        let $wrapper = this.$wrapper;

        // render search input
        if (this.showSearchBox) {
            let $search = Template.compile(this.selectors.searchTemplate);
            $wrapper.find(this.selectors.searchContainer)
                .append($search);

            $wrapper.on(
                'click',
                this.selectors.searchClearButton,
                this.handlerSearchClear.bind(this)
            );

            $wrapper.on(
                'keyup',
                this.selectors.searchBox,
                this.handlerSearch.bind(this)
            );
        }

        $.each(this.rowButtons, function (index, button) {
            button.render($wrapper);
        });

        // render pagination elements
        if (this.pagination === true) {
            // add on change for show per page
            $wrapper.on(
                'change',
                this.selectors.showPerPage,
                (e) => {
                    e.preventDefault();

                    const perPage = $(e.currentTarget).val();
                    this.showPerPage = parseInt(perPage);
                    this.page = 1;

                    this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

                    this.refreshPagination();
                }
            );

            let $perPage = Template.compile(this.selectors.showPerPageTemplate);
            $wrapper.find(this.selectors.perPageContainer)
                .append($perPage);

            let $showPerPage = $wrapper.find(this.selectors.showPerPage);
            $showPerPage.val(this.showPerPage);
        }

        if (renderFunc) {
            renderFunc.call(this);
        }

        // render manage col with
        let $manageButtons = $wrapper.find(this.selectors.rowButtons);
        $manageButtons.css( "width", this.buttonColWidth);
    }

    /**
     * Handle click event for search input.
     * @param e
     */
    handlerSearchClear(e) {
        e.preventDefault();

        let $searchClearButton = $(e.currentTarget);
        let $search = this.$wrapper.find(this.selectors.searchBox);

        $search.val('');
        $searchClearButton.hide();

        this.cleanSearch();

        if (this.afterCleanSearchFunc) {
            this.afterCleanSearchFunc();
        }
    }

    /**
     * Clean the search
     */
    cleanSearch() {
        this.page = 1;
        this.refreshPagination();
    }

    /**
     * Handle on keyup event for search input.
     *
     * @param e The event
     */
    handlerSearch(e) {
        e.preventDefault();

        let $search = $(e.currentTarget);
        let $searchClearButton = this.$wrapper.find(this.selectors.searchClearButton);

        let search = $search.val();
        if (search == '') {
            // clear pagination and hide clear button
            $searchClearButton.hide();
        } else {
            // show clear button
            $searchClearButton.show();
        }

        this.search(search);

        if (this.afterSearchFunc) {
            this.afterSearchFunc(search);
        }
    }

    /**
     * search.
     */
    search(val) {
        let matches = this.searchFunc(this.records, val);
        if (matches === null) {
            this.cleanSearch();

            return;
        }

        let totalRecords = matches.length;

        try {
            this.refreshPagination({
                records: matches,
                totalRecords: totalRecords,
                totalPages: Math.ceil(totalRecords / this.showPerPage),
                page: 1,
            });
        } catch (err) {
            let $paginationInfo = this.$wrapper.find($(this.selectors.paginationInfo));

            $paginationInfo.html('');

            this.cleanRows();
        }
    }

    addRowButton(button) {
        this.rowButtons.push(button);
        button.setTable(this);

        let buttonWidth = button.width;

        if (buttonWidth) {
            this.buttonColWidth += buttonWidth;

            return;
        }

        if (this.rowButtons.length !== 1) {
            this.buttonColWidth += 37;
        } else {
            this.buttonColWidth = 54;
        }
    }
}

export default Table;
