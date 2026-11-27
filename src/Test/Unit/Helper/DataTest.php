<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Test\Unit\Helper;

use Infrangible\BundleOptionQtyPrice\Test\Unit\AbstractTestCase;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Tests the quantities of the linked bundle options in the cart and in orders.
 */
#[AllowMockObjectsWithoutExpectations]
class DataTest extends AbstractTestCase
{
    /**
     * Only positive numbers link a custom option to a bundle option; "0" is "--Please Select--".
     *
     * @return void
     */
    public function testGetBundleOptionId(): void
    {
        $helper = $this->createHelper();

        $this->assertSame(5, $helper->getBundleOptionId($this->createOption('5')));
        $this->assertNull($helper->getBundleOptionId($this->createOption('0')));
        $this->assertNull($helper->getBundleOptionId($this->createOption(null)));
        $this->assertNull($helper->getBundleOptionId($this->createOption('x')));
    }

    /**
     * The quantity is the one of the selected product of a radio button option, the sum of the selected products of a
     * checkbox option, and null without link, selection or request.
     *
     * @return void
     */
    public function testGetSelectionQty(): void
    {
        $helper = $this->createHelper();
        $product = $this->createCartProduct();

        $this->assertSame(3.0, $helper->getSelectionQty($product, $this->createOption('2')));
        $this->assertSame(4.0, $helper->getSelectionQty($product, $this->createOption('5')));
        $this->assertNull($helper->getSelectionQty($product, $this->createOption('3')));
        $this->assertNull($helper->getSelectionQty($product, $this->createOption(null)));
        $this->assertNull(
            $helper->getSelectionQty($this->createCartProduct([], [], false), $this->createOption('2'))
        );
    }

    /**
     * Order items use the quantities that the bundle item stored, the items of selected products those of their
     * bundle item.
     *
     * @return void
     */
    public function testGetOrderItemSelectionQty(): void
    {
        $helper = $this->createHelper();
        $bundleItem = $this->createBundleOrderItem();

        $this->assertSame(3.0, $helper->getOrderItemSelectionQty($bundleItem, $this->createOption('2')));
        $this->assertSame(4.0, $helper->getOrderItemSelectionQty($bundleItem, $this->createOption('5')));
        $this->assertNull($helper->getOrderItemSelectionQty($bundleItem, $this->createOption('3')));

        $childItem = $this->getMockBuilder(OrderItem::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $childItem->setParentItem($bundleItem);

        $this->assertSame(4.0, $helper->getOrderItemSelectionQty($childItem, $this->createOption('5')));
    }
}
