<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Block;

use FeWeDev\Base\Json;
use Infrangible\Core\Helper\Registry;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class QtyPrice extends Template
{
    /** @var Registry */
    protected $registryHelper;

    /** @var Json */
    protected $json;

    public function __construct(Template\Context $context, Registry $registryHelper, Json $json, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->registryHelper = $registryHelper;
        $this->json = $json;
    }

    public function getProduct(): Product
    {
        if (! $this->hasData('product')) {
            $this->setData(
                'product',
                $this->registryHelper->registry('current_product')
            );
        }

        return $this->getData('product');
    }

    public function getConfig(): string
    {
        $product = $this->getProduct();

        $config = [];

        /** @var Option $productOption */
        foreach ($product->getOptions() as $productOption) {
            $bundleOptionId = $productOption->getData('bundle_option_qty_price');

            if ($bundleOptionId) {
                $productOptionId = $productOption->getId();

                /** @var Type $typeInstance */
                $typeInstance = $product->getTypeInstance();

                $selectionsCollection = $typeInstance->getSelectionsCollection(
                    [$bundleOptionId],
                    $product
                );

                /** @var Product $selection */
                foreach ($selectionsCollection as $selection) {
                    $config[ $productOptionId ][ $bundleOptionId ][ $selection->getId() ] =
                        $selection->getData('selection_qty');
                }
            }
        }

        return $this->json->encode($config);
    }
}
