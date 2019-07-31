'use strict';

import Template from "./Template";
import Button from "./Button";
import InvestmentManagerClient from "./InvestmentManagerClient";

class EditButton extends Button {
    constructor(selector, template, container) {
        super(selector, template, container, function (e) {
            e.preventDefault();

            // find entity to edit
            const $row = $(e.currentTarget).closest('tr');
            const id = $row.data('id');

            let entity = this.manager.getRecord(id);

            // fetch the entity from the server because it is not loaded yet.
            // So far there is not clear use case where the application hit this scope, but we will like to
            // keep it.
            if (entity === null) {
                let url = this.manager.routing(this.entityType, 'get', id);

                InvestmentManagerClient.sendRPC(url, 'GET')
                    .then((data) => {
                        entity = data.item;
                    });
            }

            this.manager.createFrom(entity)
            // update the row by creating a new row base on the row template and
            // replace the old row
                .then((result) => {
                    if (result.value) {
                        let entity = result.value.item;

                        this.manager.replaceRecord(entity, id);

                        $row.fadeOut('normal', () => {
                            $row.replaceWith(this.manager.createRow(entity));
                        });
                    }
                });
        });

        this.manager = null;
    }

    setManager(manager) {
        this.manager = manager;
    }
}

export default EditButton;
