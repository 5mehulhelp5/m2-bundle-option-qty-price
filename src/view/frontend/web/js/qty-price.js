/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'domReady',
    'priceUtils',
    'Infrangible_BundleOptionSelection/js/model/selection'
], function ($, domReady, utils, selection) {
    'use strict';

    var globalOptions = {
        config: {},
        optionsSelector: '.product-custom-option',
        productBundleTriggerSelector: '.bundle-options-container',
        productBundleOptionContainerSelector: null,
    };

    $.widget('infrangible.bundleOptionQtyPrice', {
        options: globalOptions,

        _create: function createBundle() {
        },

        _init: function initBundle() {
            var self = this;

            domReady(function() {
                $(self.options.optionsSelector).on('product_option_changes', function(event, optionId, optionHash, option, optionsConfig, changes) {
                    var optionChanges = changes[optionHash];

                    if (optionChanges && ! $.isEmptyObject(optionChanges)) {
                        var optionQtyPrice = self.options.config[optionId];

                        if (optionQtyPrice) {
                            var selectedProductIds = selection.collectSelectedProductIds(
                                self.options.productBundleOptionContainerSelector ?
                                    self.options.productBundleOptionContainerSelector : self.element);

                            var selectionQty = 0;

                            $.each(optionQtyPrice, function(bundleOptionId, bundleOptionData) {
                                $.each(selectedProductIds, function(selectedBundleOptionId, selectedBundleOptionProductIds) {
                                    if (bundleOptionId === selectedBundleOptionId) {
                                        $.each(selectedBundleOptionProductIds, function(i, selectedBundleOptionProductId) {
                                            var selectedBundleOptionProductQty = bundleOptionData[selectedBundleOptionProductId];

                                            if (selectedBundleOptionProductQty) {
                                                selectionQty += parseInt(selectedBundleOptionProductQty);
                                            }
                                        });
                                    }
                                });
                            });

                            if (selectionQty > 0) {
                                $.each(optionChanges, function(priceCode, priceData) {
                                    if (priceData.orgAmount) {
                                        priceData.amount = priceData.orgAmount * selectionQty;
                                    } else if (priceData.amount) {
                                        priceData.orgAmount = priceData.amount;
                                        priceData.amount = priceData.amount * selectionQty;
                                    }
                                });
                            }
                        }
                    }
                });

                $(self.options.productBundleTriggerSelector).on('bundle_option_changed', function(event, bundleOptionId) {
                    $.each(self.options.config, function(optionId, optionQtyPrice) {
                        var bundleOptionQtyPrice = optionQtyPrice[bundleOptionId];

                        if (bundleOptionQtyPrice) {
                            $(self.options.optionsSelector).each(function() {
                                if (optionId === utils.findOptionId($(this)[0])) {
                                    $(this).trigger('change');
                                }
                            });
                        }
                    });
                });
            });
        },
    });

    return $.infrangible.bundleOptionQtyPrice;
});
