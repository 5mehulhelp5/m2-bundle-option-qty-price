/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

/**
 * Multiplies the prices of custom options that are linked to a bundle option with the quantity of the selected products
 * of the bundle option on the product page.
 */
define([
    'jquery',
    'domReady',
    'priceUtils',
    'Infrangible_BundleOptionSelection/js/model/selection'
], function ($, domReady, utils, selection) {
    'use strict';

    const globalOptions = {
        // the default quantities by custom option ID, bundle option ID and product ID
        config: {},
        optionsSelector: '.product-custom-option',
        productBundleTriggerSelector: '.bundle-options-container',
        productBundleOptionContainerSelector: null,
        qtySelector: 'input[name^="bundle_option_qty["]'
    };

    // $.widget() returns the constructor, which is $.infrangible.bundleOptionQtyPrice
    return $.widget('infrangible.bundleOptionQtyPrice', {
        options: globalOptions,

        _create: function () {
        },

        _init: function () {
            const self = this;

            domReady(function () {
                $(self.options.optionsSelector).on('product_option_changes',
                    function (event, optionId, optionHash, option, optionsConfig, changes) {
                        self.multiplyChanges(optionId, changes[optionHash]);
                    });

                $(self.options.productBundleTriggerSelector).on('bundle_option_changed',
                    function (event, bundleOptionId) {
                        self.updateLinkedOptions(bundleOptionId);
                    });

                $(self.options.qtySelector).on('change', function () {
                    const match = /^bundle_option_qty\[(\d+)]$/.exec($(this).attr('name'));

                    if (match) {
                        self.updateLinkedOptions(match[1]);
                    }
                });
            });
        },

        /**
         * Multiplies the price changes of a custom option with the quantity of its linked bundle option.
         *
         * @param {string} optionId the ID of the custom option
         * @param {Object} optionChanges the price changes of the custom option by price code
         */
        multiplyChanges: function (optionId, optionChanges) {
            if (!optionChanges || $.isEmptyObject(optionChanges) || !this.options.config[optionId]) {
                return;
            }

            const qty = this.getSelectionQty(this.options.config[optionId]);

            if (qty <= 0) {
                return;
            }

            $.each(optionChanges, function (priceCode, priceData) {
                if (priceData.orgAmount === undefined) {
                    priceData.orgAmount = priceData.amount;
                }

                priceData.amount = priceData.orgAmount * qty;
            });
        },

        /**
         * Returns the sum of the quantities of the selected products of the linked bundle options. A bundle option with
         * one selected product and a quantity field uses the entered quantity, otherwise the default quantity applies.
         *
         * @param {Object} bundleOptionQtys the default quantities by bundle option ID and product ID
         * @return {number}
         */
        getSelectionQty: function (bundleOptionQtys) {
            const self = this,
                selectedProductIds = selection.collectSelectedProductIds(
                    this.options.productBundleOptionContainerSelector ?
                        $(this.options.productBundleOptionContainerSelector) : this.element);

            let qty = 0;

            $.each(bundleOptionQtys, function (bundleOptionId, productQtys) {
                const productIds = $.grep(selectedProductIds[bundleOptionId] || [], function (productId) {
                        return productQtys[productId] !== undefined;
                    }),
                    qtyField = $('input[name="bundle_option_qty[' + bundleOptionId + ']"]', self.element);

                if (productIds.length === 1 && qtyField.length && parseFloat(qtyField.val()) > 0) {
                    qty += parseFloat(qtyField.val());

                    return;
                }

                $.each(productIds, function (index, productId) {
                    qty += parseFloat(productQtys[productId]);
                });
            });

            return qty;
        },

        /**
         * Lets the custom options that are linked to the bundle option update their price.
         *
         * @param {string} bundleOptionId
         */
        updateLinkedOptions: function (bundleOptionId) {
            const self = this;

            $.each(this.options.config, function (optionId, bundleOptionQtys) {
                if (bundleOptionQtys[bundleOptionId] === undefined) {
                    return;
                }

                $(self.options.optionsSelector).each(function () {
                    if (String(utils.findOptionId(this)) === String(optionId)) {
                        $(this).trigger('change');
                    }
                });
            });
        }
    });
});
