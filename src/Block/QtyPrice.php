<?php

declare(strict_types=1);

namespace Infrangible\BundleOptionQtyPrice\Block;

use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Product;
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

    /** @var Data */
    protected $helper;

    public function __construct(
        Template\Context $context,
        Registry $registryHelper,
        Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->registryHelper = $registryHelper;
        $this->helper = $helper;
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

        return $this->helper->getConfig($product);
    }
}
