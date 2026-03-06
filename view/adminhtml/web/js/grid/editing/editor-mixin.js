/**
 * Thinkbeat_SmartCustomerGrid Editor Mixin
 *
 * Prevents inline editing for Guest customers in the admin customer grid.
 * Compatible with Magento 2.3.7, 2.4.x, and 2.4.8+.
 *
 * Only intercepts startEdit (the user-facing entry point).
 * Server-side PreventGuestInlineEdit.php provides a safety net for saves.
 */
define(['underscore'], function (_) {
    'use strict';

    return function (target) {
        return target.extend({

            /**
             * Polyfill getRowData for older Magento versions (e.g. 2.3.7)
             * where the method does not exist on the editor UI component.
             *
             * @param {(Number|String)} id - Record ID or row index
             * @param {Boolean} [isIndex=false] - Whether id is an array index
             * @returns {Object|undefined}
             */
            _getSafeRowData: function (id, isIndex) {
                // If the core method exists (Magento 2.4.x+), use it
                if (typeof this.getRowData === 'function') {
                    return this.getRowData(id, isIndex);
                }

                // Polyfill for older versions (e.g. Magento 2.3.7)
                var rowsData = this.rowsData || [];
                var recordId = id;

                if (isIndex === true) {
                    var record = rowsData[id];
                    recordId = record ? record[this.indexField] : false;
                }

                return _.find(rowsData, function (row) {
                    return row[this.indexField] === recordId;
                }, this);
            },

            /**
             * Check if a record belongs to a guest customer.
             * Robust check across all Magento 2 versions.
             *
             * @param {Object} record - Row data object
             * @returns {Boolean}
             */
            _isGuestRecord: function (record) {
                if (!record) {
                    return false;
                }

                // 1. Check is_guest_customer (integer 1/0 from PHP)
                // This might be stripped by DataProvider in Magento 2.4.8+ if column is hidden
                if (record.is_guest_customer !== undefined) {
                    return !!parseInt(record.is_guest_customer, 10);
                }

                // 2. Fallback: check customer_type_raw (preserved raw string)
                if (record.customer_type_raw !== undefined) {
                    return record.customer_type_raw === 'guest';
                }

                // 3. Fallback for Magento 2.4.8+ missing JSON payload:
                // Parse the visible HTML string from the customer_type column.
                // It looks like: <span style="...">Guest</span>
                if (record.customer_type && typeof record.customer_type === 'string') {
                    if (record.customer_type.indexOf('>Guest<') !== -1 || record.customer_type.indexOf('Guest') !== -1) {
                        return true;
                    }
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
                var record = this._getSafeRowData(id, isIndex);

                if (this._isGuestRecord(record)) {
                    return this; // Intercepted: guest customers cannot be inline-edited
                }

                return this._super(id, isIndex);
            }
        });
    };
});
