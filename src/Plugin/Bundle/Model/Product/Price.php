<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Plugin\Bundle\Model\Product;

use FeWeDev\Base\Variables;
use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

/**
 * Multiplies the prices of custom options that are linked to a bundle option with the quantity of the bundle option.
 */
class Price
{
    /**
     * @param Variables $variables
     * @param Data $helper
     */
    public function __construct(protected Variables $variables, protected Data $helper)
    {
    }

    /**
     * Adds the prices of the selected custom options that are linked to a bundle option once per further piece.
     *
     * Magento adds the price of a custom option once per bundle; with a quantity of 3 in the linked bundle option it
     * counts three times. The quantity of the bundle in the cart does not matter here, it multiplies the final price.
     *
     * @param BundlePrice $subject
     * @param float $result
     * @param float|null $qty
     * @param Product $product
     *
     * @return float
     */
    public function afterGetFinalPrice(BundlePrice $subject, float $result, ?float $qty, Product $product): float
    {
        $optionIdsOption = $product->getCustomOption('option_ids');
        $optionIds = $optionIdsOption === null ? '' :
            $this->variables->stringValueOrDefault($optionIdsOption->getValue());

        if ($optionIds === '') {
            return $result;
        }

        $basePrice = null;
        $additionalPrice = 0.0;

        foreach (explode(',', $optionIds) as $optionId) {
            $productOption = is_numeric($optionId) ? $product->getOptionById((int)$optionId) : null;
            $selectionQty = $productOption === null ? null : $this->helper->getSelectionQty($product, $productOption);
            $customOption = $productOption === null ? null : $product->getCustomOption('option_' . $optionId);

            if ($productOption === null || $selectionQty === null || $customOption === null) {
                continue;
            }

            try {
                $group = $productOption->groupFactory($productOption->getType());
            } catch (LocalizedException) {
                continue;
            }

            $group->setOption($productOption);
            $group->setData('configuration_item_option', $customOption);

            $basePrice ??= $subject->getBasePrice($product, $qty);

            $optionPrice = $group->getOptionPrice(
                $this->variables->stringValueOrDefault($customOption->getValue()),
                $basePrice
            );

            $additionalPrice += $optionPrice * ($selectionQty - 1);
        }

        if ($additionalPrice == 0) {
            return $result;
        }

        $result = max(0, $result + $additionalPrice);

        $product->setFinalPrice($result);

        return $result;
    }
}
