<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Plugin\Bundle\Model\Product;

use FeWeDev\Base\Variables;
use Infrangible\BundleOptionQtyPrice\Helper\Data;
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

    /** @var Data */
    protected $helper;

    public function __construct(Variables $variables, Data $helper)
    {
        $this->variables = $variables;
        $this->helper = $helper;
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
                        $productOptionQty = $this->helper->getProductOptionQty(
                            $product,
                            $productOption,
                            $qty
                        );

                        if ($productOptionQty != $qty) {
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

                            $qtyDiff = $productOptionQty - $qty;

                            $result += $optionPrice * $qtyDiff;
                        }
                    }
                }

                $product->setFinalPrice($result);
            }
        }

        return $result;
    }
}
