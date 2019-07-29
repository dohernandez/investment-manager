'use strict';

import Template from "./Template";
import Form from "./Form";

import _ from 'underscore';
import $ from 'jquery';

class Table {
    constructor(options) {
        let _options = _.defaults(options || {}, {
            pagination: false,
            showPerPage: 10,
            visiblePages: 6,
            sort: null,
            selectors: _.defaults(options.selectors || {}, Table._selectors),
            showSearchBox: options.showSearchBox ? options.showSearchBox : options.searchButton,
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
        this.showSearchBox = _options.showSearchBox;
        this.searchButton = false;
        this.afterCleanSearchFunc = null;
        this.afterSearchFunc = null;
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

        this.recreateTableWithRecords(this.records);

        this.totalPages = Math.ceil(this.totalRecords / this.showPerPage);

        this.refreshPagination();
    }

    recreateTableWithRecords(records, staringIndex) {
        staringIndex = staringIndex ? staringIndex : 0;

        this.cleanRows();

        $.each(records, (index, entity) => {
            this.addRow(entity, staringIndex + index);
        });

        this.refreshExpanded();
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
     * @param {Object} compile
     *
     * @return {Object}
     *
     * @protected
     */
    createRow(compile) {
        return Template.compile(this.selectors.rowTemplate, compile);
    }

    getRecord(id) {
        let recordIndex = this.indexOfById(id);

        return recordIndex !== null ? this.records[recordIndex] : null;
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
        // remove pagination
        let $paginator = this.$wrapper.find($(this.selectors.pagination));
        let $parent = $paginator.parent();
        $paginator.remove();

        // recreate the pagination
        $paginator = new $('<ul id="pagination" class="pagination-sm pagination js-manage-pagination pull-right"></ul>');
        $parent.append($paginator);

        // show pagination
        // init all the pagination variables
        let _options = _.defaults(options || {}, this.getDefaultPaginatorOptions());

        if (this.sort) {
            _options.records = _options.records.sort(this.sort);
        }

        // tweak pagination info
        let $paginationInfo = this.$wrapper.find($(this.selectors.paginationInfo));
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
            startPage: _options.currentPage
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

    // Render
    render(renderFunc) {
        // render search input
        if (this.showSearchBox) {
            let $search = Template.compile(this.selectors.searchTemplate);
            this.$wrapper.find(this.selectors.searchContainer)
                .append($search);

            this.$wrapper.on(
                'click',
                this.selectors.searchClearButton,
                this.handlerSearchClear.bind(this)
            );

            this.$wrapper.on(
                'keyup',
                this.selectors.searchBox,
                this.handlerSearch.bind(this)
            );
        }

        // render pagination elements
        if (this.pagination === true) {
            // add on change for show per page
            this.$wrapper.on(
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
            this.$wrapper.find(this.selectors.perPageContainer)
                .append($perPage);

            let $showPerPage = this.$wrapper.find(this.selectors.showPerPage);
            $showPerPage.val(this.showPerPage);
        }

        if (renderFunc) {
            renderFunc.call(this);
        }
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
        this.recreateTableWithRecords(this.records);

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

        this.recreateTableWithRecords(matches);

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
}

export default Table;
