<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Helper;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Data
{
    /** @var Json */
    protected $json;

    /** @var Arrays */
    protected $arrays;

    public function __construct(Json $json, Arrays $arrays)
    {
        $this->json = $json;
        $this->arrays = $arrays;
    }

    public function getProductOptionQty(Product $product, Option $productOption, ?float $qty): ?float
    {
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
                        return floatval($selectionQtyOption->getValue());
                    }
                }
            }
        }

        return $qty;
    }
}
