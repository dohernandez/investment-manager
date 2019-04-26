'use strict';

(function (window, $, Routing) {
    /**
     * Create a CRUD Manage Table instance to add, remove row in the table defined in
     * @see templates/Components/Table/crud-manage-table.html.twig
     *
     * @constructor
     *
     * @param $wrapper
     * @param $deleteModal
     * @constructor
     */
    window.CRUDManageTable = function ($wrapper, $deleteModal) {
        this.$wrapper = $wrapper;
        this.$deleteModal = $deleteModal;

        // Delegate selector
        //
        // Define a second argument, which is the selector for the element that you truly want to react to.
        this.$wrapper.on(
            'click',
            '.entity-delete',
            this.handlerEntityDelete.bind(this)
        );
        
        this.loadEntities();
    };

    $.extend(window.CRUDManageTable.prototype, {
        /**
         * Handle on click event for delete buttons
         *
         * @param e The event
         */
        handlerEntityDelete: function (e) {
            e.preventDefault();

            let $form = $(e.currentTarget);

            // Setting form data
            let itemId = $form.data('id');
            let itemTitle = $form.data('title');

            this.$deleteModal.find('#itemId').val(itemId);
            this.$deleteModal.find('.modal-body p span').text(itemTitle);


            let $confirmDelete = this.$deleteModal.find('#confirm-delete');

            // To remove any previous click handler added due to reusability issue.
            // Without this line of code every time the delete dialog is evoke, a new click event is added,
            // and when the #confirm-delete is clicked, all the events added are fire, therefore sending unwanted
            // requests to the server.
            $confirmDelete.unbind('click');

            // Assign this to be able to pass it to the anonymous function.
            let _self = this;

            // Attach on click listener to the form the modal button when confirm delete is clicked.
            $confirmDelete.on('click', function () {
                let $modalDialog = _self.$deleteModal.find('.modal-dialog');

                // Activating processing panel
                $modalDialog.hide();
                _self.$deleteModal.css('text-align', 'center');

                let $processingBackground = $('#background-modal-delete');
                $processingBackground.show();

                let deleteUrl = $form.data('url');
                let $row = $form.closest('tr');

                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    success: function () {
                        $processingBackground.hide();

                        // To remove any previous hidden.bs.modal handler added due to reusability issue
                        // Without this line of code there is not big deal, but it is nice to avoid trigger the
                        // same function many times.
                        $confirmDelete.unbind('hidden.bs.modal');

                        _self.$deleteModal.on('hidden.bs.modal', function () {
                            // Deactivating processing panel
                            _self.$deleteModal.css('text-align', 'left');
                            $modalDialog.show();
                        });

                        _self.$deleteModal.modal('hide');

                        $row.fadeOut('normal', function () {
                            $(this).remove();

                            _self._recalculateRowIndex();
                        });
                    },
                    error: function (jqXHR) {
                        // TODO implement error form handling
                        console.log(jqXHR.responseText);
                    }
                });
            });
        },

        /**
         * Loads all entities into the table
         */
        loadEntities: function () {
            let _self = this;

            $.ajax({
                url: Routing.generate('transfer_list'),
                success: function(data) {
                    $.each(data.items, function (key, entity) {
                        entity.index = key + 1;

                        _self._addRow(entity);
                    });
                }
            });
        },

        /**
         * Create a row table with the entity value.
         *
         * @param {Object} entity
         * @param {int} entity.id
         * @param {string} entity.date
         * @param {string} entity.beneficiaryParty.name
         * @param {string} entity.beneficiaryParty.iban
         * @param {string} entity.debtorParty.name
         * @param {string} entity.debtorParty.iban
         * @param {float} entity.amount
         *
         * @private
         */
        _addRow: function(entity) {
            var tplText = $('#js-manager-row-template').html();
            var tpl = _.template(tplText);

            var html = tpl(entity);
            this.$wrapper.find('tbody').append($.parseHTML(html));
        },

        /**
         * Recalculate the index fo the row table base on the new list.
         *
         * When adding or removing a new row, indexes get messy, so this functions helps to reorder them again.
         *
         * @private
         */
        _recalculateRowIndex: function () {
            let $rowIndexThs = this.$wrapper.find('.js-manager-row-index');

            $.each($rowIndexThs, function (key, th) {
                $(th).html(key + 1);
            })
        }
    });
})(window, jQuery, Routing);
