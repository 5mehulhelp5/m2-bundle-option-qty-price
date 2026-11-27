<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Test\Unit\Observer;

use Infrangible\BundleOptionQtyPrice\Observer\CatalogProductGetOptionPrice;
use Infrangible\BundleOptionQtyPrice\Test\Unit\AbstractTestCase;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Tests the option prices of Infrangible Catalog Product Option Price for the cart and for orders.
 */
#[AllowMockObjectsWithoutExpectations]
class CatalogProductGetOptionPriceTest extends AbstractTestCase
{
    /**
     * Dispatches the event for the option and returns the option price afterwards.
     *
     * @param Product $product
     * @param Option $option
     * @param float $optionPrice
     *
     * @return mixed
     */
    private function getOptionPrice(Product $product, Option $option, float $optionPrice): mixed
    {
        $transportObject = new DataObject(
            ['product' => $product, 'product_option' => $option, 'option_price' => $optionPrice, 'qty' => 2.0]
        );

        (new CatalogProductGetOptionPrice($this->createHelper()))->execute(
            new Observer(['data' => $transportObject])
        );

        return $transportObject->getData('option_price');
    }

    /**
     * In the cart, the price is multiplied with the quantity of the selected products, not with the quantity of the
     * bundle; unlinked options keep their price.
     *
     * @return void
     */
    public function testCart(): void
    {
        $product = $this->createCartProduct();

        $this->assertSame(6.0, $this->getOptionPrice($product, $this->createOption('2'), 2.0));
        $this->assertSame(6.0, $this->getOptionPrice($product, $this->createOption('5'), 1.5));
        $this->assertSame(1.0, $this->getOptionPrice($product, $this->createOption(null), 1.0));
    }

    /**
     * When the order is placed, the product has no options of the cart; the quantities come from the order item.
     *
     * @return void
     */
    public function testOrder(): void
    {
        $option = $this->createOption('5');
        $option->setData('item', $this->createBundleOrderItem());

        $this->assertSame(6.0, $this->getOptionPrice($this->createStub(Product::class), $option, 1.5));
    }
}
