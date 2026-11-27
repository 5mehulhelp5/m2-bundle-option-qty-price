<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Test\Unit;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Sales\Model\Order\Item as OrderItem;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

/**
 * Creates the products of the cart, custom options and order items of the tests: a bundle whose bundle option 2 has
 * the selection 4 with 3 pieces and whose bundle option 5 (checkboxes) has the selections 9 and 10 with 2 pieces each.
 */
#[AllowMockObjectsWithoutExpectations]
abstract class AbstractTestCase extends TestCase
{
    /**
     * Returns the helper with its real dependencies.
     *
     * @return Data
     */
    protected function createHelper(): Data
    {
        $variables = new Variables();

        return new Data(new Json($variables), $variables);
    }

    /**
     * Returns a bundle of the cart with the selected bundle options and the given further custom options.
     *
     * @param array<string, string> $customOptions further custom options of the cart item by code
     * @param array<int, Option> $options the custom options of the product by ID
     * @param bool $withBuyRequest
     *
     * @return Product
     */
    protected function createCartProduct(
        array $customOptions = [],
        array $options = [],
        bool $withBuyRequest = true
    ): Product {
        $buyRequest = json_encode(['qty' => 2, 'bundle_option' => ['1' => '1', '2' => '4', '5' => ['9', '10', '']]]);
        $values = ['selection_qty_4' => '3', 'selection_qty_9' => '2', 'selection_qty_10' => '2'] + $customOptions;

        if ($withBuyRequest && $buyRequest !== false) {
            $values[ 'info_buyRequest' ] = $buyRequest;
        }

        $customOptionStubs = [];

        foreach ($values as $code => $value) {
            $customOption = $this->createStub(OptionInterface::class);
            $customOption->method('getValue')->willReturn($value);
            $customOptionStubs[ $code ] = $customOption;
        }

        $product = $this->createStub(Product::class);
        $product->method('getCustomOption')->willReturnCallback(
            fn(string $code): ?OptionInterface => $customOptionStubs[ $code ] ?? null
        );
        $product->method('getOptionById')->willReturnCallback(fn(int $id): ?Option => $options[ $id ] ?? null);

        return $product;
    }

    /**
     * Returns a custom option linked to the bundle option, with a group that calculates the price.
     *
     * @param string|null $bundleOptionId
     * @param float $price
     *
     * @return Option
     */
    protected function createOption(?string $bundleOptionId, float $price = 0.0): Option
    {
        $group = $this->createStub(DefaultType::class);
        $group->method('getOptionPrice')->willReturn($price);

        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['groupFactory'])
            ->getMock();
        $option->method('groupFactory')->willReturn($group);
        $option->setData(Data::BUNDLE_OPTION_QTY_PRICE, $bundleOptionId);
        $option->setData('type', 'field');

        return $option;
    }

    /**
     * Returns the order item of a bundle with the quantities of the selected products per bundle option, as Magento
     * stores them in the product options.
     *
     * @return OrderItem
     */
    protected function createBundleOrderItem(): OrderItem
    {
        $orderItem = $this->getMockBuilder(OrderItem::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $orderItem->setData(
            'product_options',
            [
                'bundle_options' => [
                    2 => ['option_id' => '2', 'value' => [['title' => 'Brick', 'qty' => '3', 'price' => 12]]],
                    5 => [
                        'option_id' => '5',
                        'value'     => [
                            ['title' => 'Brick', 'qty' => '2', 'price' => 2],
                            ['title' => 'Roller', 'qty' => '2', 'price' => 2]
                        ]
                    ]
                ]
            ]
        );

        return $orderItem;
    }
}
