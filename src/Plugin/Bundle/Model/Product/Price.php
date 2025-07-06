<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Plugin\Bundle\Model\Product;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Price
{
    /** @var Variables */
    protected $variables;

    /** @var Json */
    protected $json;

    /** @var Arrays */
    protected $arrays;

    public function __construct(Variables $variables, Json $json, Arrays $arrays)
    {
        $this->variables = $variables;
        $this->json = $json;
        $this->arrays = $arrays;
    }

    /**
     * @throws LocalizedException
     */
    public function afterGetFinalPrice(Product\Type\Price $subject, float $result, ?float $qty, Product $product): float
    {
        $optionIdsOption = $product->getCustomOption('option_ids');

        if ($optionIdsOption) {
            $optionIdsOptionValue = $optionIdsOption->getValue();

            if (! $this->variables->isEmpty($optionIdsOptionValue)) {
                $basePrice = $subject->getBasePrice(
                    $product,
                    $qty
                );

                $optionIds = explode(
                    ',',
                    $optionIdsOptionValue
                );

                foreach ($optionIds as $optionId) {
                    $productOption = $product->getOptionById($optionId);

                    if ($productOption) {
                        $bundleOptionQtyPrice = $productOption->getData('bundle_option_qty_price');

                        if ($bundleOptionQtyPrice) {
                            $buyRequestOption = $product->getCustomOption('info_buyRequest');

                            if ($buyRequestOption) {
                                $buyRequestOptionValue = $buyRequestOption->getValue();

                                $buyRequest = $this->json->decode($buyRequestOptionValue);

                                $bundleOptionSelectionId = $this->arrays->getValue(
                                    $buyRequest,
                                    sprintf(
                                        'bundle_option:%s',
                                        $bundleOptionQtyPrice
                                    ),
                                    []
                                );

                                if ($bundleOptionSelectionId) {
                                    $selectionQtyOption = $product->getCustomOption(
                                        sprintf(
                                            'selection_qty_%s',
                                            $bundleOptionSelectionId
                                        )
                                    );

                                    if ($selectionQtyOption) {
                                        $selectionQty = $selectionQtyOption->getValue();

                                        if ($selectionQty != $qty) {
                                            $customOption = $product->getCustomOption(
                                                sprintf(
                                                    'option_%s',
                                                    $productOption->getId()
                                                )
                                            );

                                            $group = $productOption->groupFactory($productOption->getType());

                                            $group->setOption($productOption);
                                            $group->setData(
                                                'configuration_item_option',
                                                $customOption
                                            );

                                            $optionPrice = $group->getOptionPrice(
                                                $customOption->getValue(),
                                                $basePrice
                                            );

                                            $qtyDiff = $selectionQty - $qty;

                                            $result += $optionPrice * $qtyDiff;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $product->setFinalPrice($result);
            }
        }

        return $result;
    }
}
