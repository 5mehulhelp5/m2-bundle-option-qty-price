<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Block;

use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Infrangible\Core\Helper\Registry;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

/**
 * Passes the quantities of the linked bundle options to the script of the product page.
 */
class QtyPrice extends Template
{
    /** @var array<int, array<int, array<int, float>>>|null the configuration once it is loaded */
    private ?array $configData = null;

    /**
     * @param Template\Context $context
     * @param Registry $registryHelper
     * @param Data $helper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected Registry $registryHelper,
        protected Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the product of the block, by default the current product.
     *
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        if (! $this->hasData('product')) {
            $this->setData('product', $this->registryHelper->registry('current_product'));
        }

        $product = $this->getData('product');

        return $product instanceof Product ? $product : null;
    }

    /**
     * Returns the configuration of the script as JSON: the default quantities of the linked bundle options.
     *
     * @return string
     */
    public function getConfig(): string
    {
        return $this->helper->encodeConfig($this->getConfigData());
    }

    /**
     * Returns the default quantities of the products of the linked bundle options, loaded once.
     *
     * @return array<int, array<int, array<int, float>>>
     */
    private function getConfigData(): array
    {
        if ($this->configData === null) {
            $product = $this->getProduct();

            $this->configData = $product === null ? [] : $this->helper->getConfigData($product);
        }

        return $this->configData;
    }

    /**
     * Renders nothing if no custom option of the product is linked to a bundle option.
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if (count($this->getConfigData()) === 0) {
            return '';
        }

        return parent::_toHtml();
    }
}
