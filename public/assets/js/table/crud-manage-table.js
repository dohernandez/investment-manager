'use strict';

(function (window, $) {
    /**
     * Create a CRUD Manage Table instance to add, remove row in the table defined in
     * @see templates/Components/Table/crud-manage-table.html.twig
     *
     * @constructor
     *
     * @param $wrapper
     * @param $modal
     * @constructor
     */
    window.CRUDManageTable = function ($wrapper, $modal) {
        this.$wrapper = $wrapper;
        this.$modal = $modal;

        console.log('attaching onclick event to .entity-delete');
        // Delegate selector
        //
        // Define a second argument, which is the selector for the element that you truly want to react to.
        this.$wrapper.on(
            'click',
            '.entity-delete',
            this.handlerEntityDelete.bind(this)
        );
    };

    $.extend(window.CRUDManageTable.prototype, {
        handlerEntityDelete: function (e) {
            e.preventDefault();

            let $form = $(e.currentTarget);

            // Setting form data
            let itemId = $form.data('id');
            let itemTitle = $form.data('title');

            this.$modal.find('#itemId').val(itemId);
            this.$modal.find('.modal-body p span').text(itemTitle);


            let $confirmDelete = this.$modal.find('#confirm-delete');

            // To remove any previous click handler added due to reusability issue.
            // Without this line of code every time the delete dialog is evoke, a new click event is added,
            // and when the #confirm-delete is clicked, all the events added are fire, therefore sending unwanted
            // requests to the server.
            $confirmDelete.unbind('click');

            let _self = this;

            $confirmDelete.on('click', function () {
                let $modalDialog = _self.$modal.find('.modal-dialog');

                // Activating processing panel
                $modalDialog.hide();
                _self.$modal.css('text-align', 'center');

                let $processingBackground = $('#background-modal-delete');
                $processingBackground.show();

                let deleteUrl = $form.data('url');
                let $row = $form.closest('tr');

                console.log('yes', deleteUrl);
                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    success: function () {
                        $processingBackground.hide();

                        // To remove any previous hidden.bs.modal handler added due to reusability issue
                        // Without this line of code there is not big deal, but it is nice to avoid trigger the
                        // same function many times.
                        $confirmDelete.unbind('hidden.bs.modal');

                        _self.$modal.on('hidden.bs.modal', function () {
                            // Deactivating processing panel
                            _self.$modal.css('text-align', 'left');
                            $modalDialog.show();
                        });

                        _self.$modal.modal('hide');

                        $row.fadeOut('normal', function () {
                            $(this).remove();
                        });
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR.responseText);
                    }
                });
            });
        }
    });
})(window, jQuery);
