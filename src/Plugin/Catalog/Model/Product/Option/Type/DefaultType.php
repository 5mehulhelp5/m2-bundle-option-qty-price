<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Plugin\Catalog\Model\Product\Option\Type;

use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class DefaultType
{
    /** @var Data */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @throws LocalizedException
     */
    public function afterGetFormattedOptionValue(
        \Magento\Catalog\Model\Product\Option\Type\DefaultType $subject,
        string $result
    ): string {
        /** @var Item $item */
        $item = $subject->getData('configuration_item');

        if ($item) {
            $product = $item->getProduct();
            $productOption = $subject->getOption();

            if ($product && $productOption) {
                $productOptionQty = $this->helper->getProductOptionQty(
                    $product,
                    $productOption,
                    1
                );

                if ($productOptionQty > 1) {
                    $result = sprintf(
                        '%d x %s',
                        $productOptionQty,
                        $result
                    );
                }
            }
        }

        return $result;
    }
}
