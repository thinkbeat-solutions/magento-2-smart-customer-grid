define([], function () {
    'use strict';

    return function (target) {
        return target.extend({
            /**
             * Activates editing of the provided record.
             * Intercepts and blocks if customer_type is guest.
             *
             * @param {(Number|String)} id
             * @param {Boolean} [isIndex=false]
             * @returns {Editor} Chainable
             */
            startEdit: function (id, isIndex) {
                var record = this.getRowData(id, isIndex);

                // If customer is a guest, prevent inline edit mode from starting
                if (record && record.is_guest_customer) {
                    return this;
                }

                return this._super(id, isIndex);
            }
        });
    };
});
