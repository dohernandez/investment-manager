'use strict';

import Routing from "./Components/Routing";
import Template from "./Components/Template";
import _ from 'underscore';

class Homepage {
    constructor(
        stockMoversDailyPanelTable,
        stockShakersDailyPanelTable,
        stockMarketPanel
    ) {
        this.stockMoversDailyPanelTable = stockMoversDailyPanelTable;
        this.stockShakersDailyPanelTable = stockShakersDailyPanelTable;
        this.stockMarketPanel = stockMarketPanel;
    }

    render() {
        this.stockMoversDailyPanelTable.render();
        this.stockShakersDailyPanelTable.render();
    }

    load() {
        this.stockMoversDailyPanelTable.loadRows();
        this.stockShakersDailyPanelTable.loadRows();
        this.stockMarketPanel.loadRows();
    }

    toggle() {
        this.stockMoversDailyPanelTable.toggleExpanded();
        this.stockShakersDailyPanelTable.toggleExpanded();
    }
}

class StockMarketPanel {
    /**
     *
     * @param {object} options {{
     *      entityType: string,
     *      selectors: string,
     *      template: string,
     *      wrapper: object
     * }}
     */
    constructor(options) {
        let _options = _.defaults(options || {}, {
            template: options.template === undefined ? '#js-panel-' + options.entityType + '-row-template' : options.template,
        });

        this.entityType = _options.entityType;
        this.$wrapper = _options.wrapper;
        this.selectors = _options.selectors;
        this.template = _options.template;
    }

    loadRows() {
        $.ajax({
            url: Routing.generate(this.entityType + '_list'),
            success: (data) => {
                this.setData(data);
            }
        });
    }

    setData(data) {
        // this is because the server is serializing arrays sometimes like an array
        // and another times like an object. We have to cast to array.
        let items = data.items;
        if (typeof items === 'object') {
            items = Object.values(items);
        }

        if (items.length === 0) {
            return
        }

        const $body = this.getBody();

        const colSize = 12/items.length;
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            item.colSize = colSize;

            const row = this.createRow(item);
            $body.append(row);
        }
    }

    getBody() {
        if (!this.body) {
            this.body = this.$wrapper.find(this.selectors.body);
        }

        return this.body;
    }

    createRow(data) {
        return Template.compile(this.template, data);
    }
}

global.Homepage = Homepage;
global.StockMarketPanel = StockMarketPanel;
