<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Helper;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * The quantity of the linked bundle option that the price of a custom option is multiplied with.
 */
class Data
{
    /** The column of custom options with the ID of the linked bundle option. */
    public const BUNDLE_OPTION_QTY_PRICE = 'bundle_option_qty_price';

    /**
     * @param Json $json
     * @param Variables $variables
     */
    public function __construct(protected Json $json, protected Variables $variables)
    {
    }

    /**
     * Returns the ID of the bundle option that the custom option is linked to, null if it is not linked.
     *
     * @param Option $productOption
     *
     * @return int|null
     */
    public function getBundleOptionId(Option $productOption): ?int
    {
        $bundleOptionId = $productOption->getData(self::BUNDLE_OPTION_QTY_PRICE);

        if (! is_numeric($bundleOptionId) || (int)$bundleOptionId <= 0) {
            return null;
        }

        return (int)$bundleOptionId;
    }

    /**
     * Returns the quantity that the price of the custom option is multiplied with in the cart.
     *
     * It is the sum of the quantities of the selected products of the linked bundle option in the product configured
     * for the cart.
     *
     * Null if the custom option is not linked or the product has no selected product in the bundle option.
     *
     * @param Product $product
     * @param Option $productOption
     *
     * @return float|null
     */
    public function getSelectionQty(Product $product, Option $productOption): ?float
    {
        $bundleOptionId = $this->getBundleOptionId($productOption);

        if ($bundleOptionId === null) {
            return null;
        }

        $selectionIds = $this->getSelectionIds($product, $bundleOptionId);

        if (count($selectionIds) === 0) {
            return null;
        }

        $qty = 0.0;

        foreach ($selectionIds as $selectionId) {
            $selectionQtyOption = $product->getCustomOption('selection_qty_' . $selectionId);

            if ($selectionQtyOption !== null && is_numeric($selectionQtyOption->getValue())) {
                $qty += (float)$selectionQtyOption->getValue();
            }
        }

        return $qty > 0 ? $qty : null;
    }

    /**
     * Returns the quantity that the price of the custom option is multiplied with in an order item.
     *
     * It is the sum of the quantities of the selected products of the linked bundle option, as the bundle item stored
     * them when the order was placed. For the item of a selected product, the bundle item is used.
     *
     * Null if the custom option is not linked or the order item has no selected product in the bundle option.
     *
     * @param OrderItem $orderItem
     * @param Option $productOption
     *
     * @return float|null
     */
    public function getOrderItemSelectionQty(OrderItem $orderItem, Option $productOption): ?float
    {
        $bundleOptionId = $this->getBundleOptionId($productOption);

        if ($bundleOptionId === null) {
            return null;
        }

        $parentItem = $orderItem->getParentItem();
        $bundleItem = $parentItem instanceof OrderItem ? $parentItem : $orderItem;

        // Magento declares the product options as array, they are null for items without options
        /** @var array<string, mixed>|null $productOptions */
        $productOptions = $bundleItem->getProductOptions();
        $bundleOptions = $productOptions[ 'bundle_options' ] ?? null;
        $bundleOption = is_array($bundleOptions) ? $bundleOptions[ $bundleOptionId ] ?? null : null;
        $selections = is_array($bundleOption) ? $bundleOption[ 'value' ] ?? null : null;
        $qty = 0.0;

        foreach (is_array($selections) ? $selections : [] as $selection) {
            $selectionQty = is_array($selection) ? $selection[ 'qty' ] ?? null : null;

            if (is_numeric($selectionQty)) {
                $qty += (float)$selectionQty;
            }
        }

        return $qty > 0 ? $qty : null;
    }

    /**
     * Returns the IDs of the selections of the bundle option in the request of the cart.
     *
     * Radio buttons and drop-downs have one, checkboxes and multiple selects any number.
     *
     * @param Product $product
     * @param int $bundleOptionId
     *
     * @return int[]
     */
    private function getSelectionIds(Product $product, int $bundleOptionId): array
    {
        $buyRequestOption = $product->getCustomOption('info_buyRequest');

        if ($buyRequestOption === null) {
            return [];
        }

        $buyRequest = $this->json->decode($this->variables->stringValueOrDefault($buyRequestOption->getValue()));
        $bundleOptions = is_array($buyRequest) ? $buyRequest[ 'bundle_option' ] ?? null : null;
        $selection = is_array($bundleOptions) ? $bundleOptions[ $bundleOptionId ] ?? null : null;
        $selectionIds = [];

        foreach (is_array($selection) ? $selection : [$selection] as $selectionId) {
            if (is_numeric($selectionId) && (int)$selectionId > 0) {
                $selectionIds[] = (int)$selectionId;
            }
        }

        return array_values(array_unique($selectionIds));
    }

    /**
     * Returns the default quantities of the products of the linked bundle options for the product page.
     *
     * The quantities are indexed by the ID of the custom option, the ID of the bundle option and the ID of the product.
     *
     * @param Product $product
     *
     * @return array<int, array<int, array<int, float>>>
     */
    public function getConfigData(Product $product): array
    {
        $typeInstance = $product->getTypeInstance();

        if (! $typeInstance instanceof BundleType) {
            return [];
        }

        $config = [];

        foreach ($product->getOptions() ?? [] as $productOption) {
            if (! $productOption instanceof Option) {
                continue;
            }

            $bundleOptionId = $this->getBundleOptionId($productOption);

            if ($bundleOptionId === null) {
                continue;
            }

            $productOptionId = $this->variables->intValue($productOption->getId());
            $config[ $productOptionId ][ $bundleOptionId ] = [];

            foreach ($typeInstance->getSelectionsCollection([$bundleOptionId], $product) as $selection) {
                $selectionQty = $selection instanceof Product ? $selection->getData('selection_qty') : null;

                if ($selection instanceof Product && is_numeric($selectionQty)) {
                    $config[ $productOptionId ][ $bundleOptionId ][ $this->variables->intValue($selection->getId()) ] =
                        (float)$selectionQty;
                }
            }
        }

        return $config;
    }

    /**
     * Returns the configuration of the script of the product page as JSON object.
     *
     * @param array $configData
     * @phpstan-param array<int, array<int, array<int, float>>> $configData
     *
     * @return string
     */
    public function encodeConfig(array $configData): string
    {
        return $this->json->encode((object)$configData) ?? '{}';
    }
}
