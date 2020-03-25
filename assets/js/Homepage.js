'use strict';

class Homepage {
    constructor(
        stockMoversDailyPanelTable,
        stockShakersDailyPanelTable
    ) {
        this.stockMoversDailyPanelTable = stockMoversDailyPanelTable;
        this.stockShakersDailyPanelTable = stockShakersDailyPanelTable;
    }

    render() {
        this.stockMoversDailyPanelTable.render();
        this.stockShakersDailyPanelTable.render();
    }

    load() {
        this.stockMoversDailyPanelTable.loadRows();
        this.stockShakersDailyPanelTable.loadRows();
    }

    toggle() {
        this.stockMoversDailyPanelTable.toggleExpanded();
        this.stockShakersDailyPanelTable.toggleExpanded();
    }
}

global.Homepage = Homepage;
