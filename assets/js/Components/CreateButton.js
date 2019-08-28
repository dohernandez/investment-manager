'use strict';

import Button from "./Button";

class CreateButton extends Button {
    constructor(selector, template, container, handler) {
        if (handler === null) {
            handler = function (e) {
                e.preventDefault();

                let manager = this.manager;

                manager.createFrom()
                    .then((result) => {
                        if (result.value) {
                            let entity = result.value.item;

                            manager.addEntity(entity);
                        }
                    });
            }
        }

        super(selector, template, container, handler);

        this.manager = null;
    }

    setManager(manager) {
        this.manager = manager;
    }
}

export default CreateButton;
