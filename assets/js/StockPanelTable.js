'use strict';

import $ from 'jquery';

const moment = require('moment');

class StockPanelTable extends PanelTable {
    /**
     * Create a row table with the entity value.
     *
     * @param {{
     *          delisted: boolean
     *          delistedAt: string
     *        }} entity
     * @param {int} index
     */
    addRow(entity, index) {
        const $table = this.getTable();

        let data = entity;
        data.index = index + 1;
        let row = this.createRow(data);

        if (data.delisted) {
            $(row).find('td:last-child').replaceWith(
                '<td style="vertical-align: middle;">' + moment(new Date(data.delistedAt)).format('DD/MM/YYYY') + '</td>'
            );
        }

        $table.find('tbody').append(row);
    }
}

global.StockPanelTable = StockPanelTable;
