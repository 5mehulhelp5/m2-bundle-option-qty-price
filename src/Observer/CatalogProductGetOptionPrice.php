<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Observer;

use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Multiplies the option prices that Infrangible Catalog Product Option Price stores separately (in the quote item and
 * in the options of the order item), like the final price.
 *
 * The event "catalog_product_get_option_price" is dispatched by the module infrangible/m2-catalog-product-option-price;
 * without it, the observer is not called.
 */
class CatalogProductGetOptionPrice implements ObserverInterface
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Multiplies the price of a custom option that is linked to a bundle option with the quantity of the bundle option.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $transportObject = $observer->getData('data');

        if (! $transportObject instanceof DataObject) {
            return;
        }

        $product = $transportObject->getData('product');
        $productOption = $transportObject->getData('product_option');
        $optionPrice = $transportObject->getData('option_price');

        if (! $product instanceof Product || ! $productOption instanceof Option || ! is_numeric($optionPrice)) {
            return;
        }

        // when an order is placed, the product of the order item has no options of the cart; the module stores the
        // order item in the custom option
        $orderItem = $productOption->getData('item');
        $selectionQty = $orderItem instanceof OrderItem ?
            $this->helper->getOrderItemSelectionQty($orderItem, $productOption) :
            $this->helper->getSelectionQty($product, $productOption);

        if ($selectionQty !== null) {
            $transportObject->setData('option_price', (float)$optionPrice * $selectionQty);
        }
    }
}
