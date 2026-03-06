/**
 * Thinkbeat_SmartCustomerGrid Editor Mixin
 *
 * Prevents inline editing for Guest customers in the admin customer grid.
 * Compatible with Magento 2.3.7, 2.4.x, and 2.4.8+.
 *
 * Only intercepts startEdit (the user-facing entry point).
 * Server-side PreventGuestInlineEdit.php provides a safety net for saves.
 */
define([], function () {
    'use strict';

    return function (target) {
        return target.extend({

            /**
             * Check if a record belongs to a guest customer.
             *
             * @param {Object} record - Row data object
             * @returns {Boolean}
             */
            _isGuestRecord: function (record) {
                if (!record) {
                    return false;
                }

                // Check is_guest_customer (integer 1/0 from PHP)
                if (record.is_guest_customer !== undefined) {
                    return !!parseInt(record.is_guest_customer, 10);
                }

                // Fallback: check customer_type_raw (preserved raw string)
                if (record.customer_type_raw !== undefined) {
                    return record.customer_type_raw === 'guest';
                }

                return false;
            },

            /**
             * Intercept startEdit to block guest customers.
             * This is the only user-facing entry point for inline editing.
             *
             * @param {(Number|String)} id - Record ID or row index
             * @param {Boolean} [isIndex=false] - Whether id is an array index
             * @returns {Editor} Chainable
             */
            startEdit: function (id, isIndex) {
                var record = this.getRowData(id, isIndex);

                if (this._isGuestRecord(record)) {
                    return this;
                }

                return this._super(id, isIndex);
            }
        });
    };
});
