'use strict';

import SwalForm from "./Components/SwalForm";
import $ from 'jquery';

import 'select2';
import '../css/StockMarketForm.scss';

const eventBus = require('js-event-bus')();

/**
 * Form manage how the account form should be build when a crud manager invokes a create or an update action.
 */
class StockMarketForm extends SwalForm {
    constructor(swalOptions, table, template = '#js-table-form-template', selector = '.js-entity-from') {
        super(swalOptions, template, selector);

        this.table = table;

        eventBus.on("entity_created", this.onCreated.bind(this));
        eventBus.on("entity_updated", this.onUpdated.bind(this));
        eventBus.on("entity_deleted", this.onDeleted.bind(this));
    }

    /**
     * Defines how inputs inside the form must be parser.
     *
     * @param {Object} data
     * @param $wrapper
     */
    onBeforeOpen(data, $wrapper) {
        if (data) {
            let $form = $wrapper.find(this.selector);
            for (const property in data) {
                let $input = $form.find('#' + property);

                $input.val(data[property]);
            }
        }

        $(".js-country-matcher").select2({
            dropdownParent: $wrapper,
            matcher: (params, data) => {// If there are no search terms, return all of the data
                if ($.trim(params.term) === '') {
                    return data;
                }

                // Do not display the item if there is no 'text' property
                if (typeof data.text === 'undefined') {
                    return null;
                }

                // `params.term` should be the term that is used for searching
                // `data.text` is the text that is displayed for the data object
                if (data.text.indexOf(params.term) > -1) {
                    var modifiedData = $.extend({}, data, true);

                    // You can return modified objects from here
                    // This includes matching the `children` how you want in nested data sets
                    return modifiedData;
                }

                // Return `null` if the term should not be displayed
                return null;
            },
            placeholder: 'Search for a country',
            minimumResultsForSearch: 4
        });
    }

    onCreated(entity) {
        this.table.addRecord(entity);
    }

    onUpdated(entity, $row) {
        this.table.replaceRecord(entity, entity.id);

        $row.fadeOut('normal', () => {
            $row.replaceWith(this.table.createRow(entity));
        });
    }

    onDeleted(id) {
        this.table.removeRecord(id);
    }
}

global.StockMarketForm = StockMarketForm;

