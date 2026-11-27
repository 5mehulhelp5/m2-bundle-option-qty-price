<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Plugin\Catalog\Model\Product\Option\Type;

use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Type\DefaultType as OptionType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Shows the quantity of the linked bundle option in front of the value of a custom option in the cart and the order.
 */
class DefaultType
{
    /**
     * @param Data $helper
     */
    public function __construct(protected Data $helper)
    {
    }

    /**
     * Puts the quantity in front of the value of a custom option that is linked to a bundle option.
     *
     * For example "3 x ENGRAVED", if the custom option of a quote item or of the product for an order item is linked to
     * a bundle option with more than one piece.
     *
     * @param OptionType $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetFormattedOptionValue(OptionType $subject, string $result): string
    {
        // the cart sets the quote item, the order options of a product type set the product
        $item = $subject->getData('configuration_item');

        try {
            $product = $item instanceof AbstractItem ? $item->getProduct() : $subject->getProduct();
            $productOption = $subject->getOption();
        } catch (LocalizedException) {
            return $result;
        }

        if (! $product instanceof Product) {
            return $result;
        }

        $selectionQty = $this->helper->getSelectionQty($product, $productOption);

        if ($selectionQty === null || $selectionQty <= 1) {
            return $result;
        }

        return sprintf('%s x %s', round($selectionQty, 4), $result);
    }
}
