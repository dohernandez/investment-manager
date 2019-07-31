'use strict';

import Template from "./Template";
import Button from "./Button";

class CreateButton extends Button {
    constructor(selector, template, container) {
        super(selector, template, container, function (e) {
            e.preventDefault();

            let manager = this.manager;

            manager.createFrom()
                .then((result) => {
                    if (result.value) {
                        let entity = result.value.item;

                        manager.addEntity(entity);
                    }
                });
        });

        this.manager = null;
    }

    setManager(manager) {
        this.manager = manager;
    }
}

export default CreateButton;
