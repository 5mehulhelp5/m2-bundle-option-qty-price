<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Observer;

use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductGetOptionPrice implements ObserverInterface
{
    /** @var Data */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer): void
    {
        /** @var DataObject $transportObject */
        $transportObject = $observer->getData('data');

        /** @var Product $product */
        $product = $transportObject->getData('product');

        /** @var Option $productOption */
        $productOption = $transportObject->getData('product_option');

        /** @var float|null $qty */
        $qty = $transportObject->getData('qty');

        /** @var float $optionPrice */
        $optionPrice = $transportObject->getData('option_price');

        $productOptionQty = $this->helper->getProductOptionQty(
            $product,
            $productOption,
            $qty
        );

        if ($productOptionQty != $qty) {
            $qtyDiff = $productOptionQty - $qty;

            $optionPrice += $optionPrice * $qtyDiff;

            $transportObject->setData(
                'option_price',
                $optionPrice
            );
        }
    }
}
