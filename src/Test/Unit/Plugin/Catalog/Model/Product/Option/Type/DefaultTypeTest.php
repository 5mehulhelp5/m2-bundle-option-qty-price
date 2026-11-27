<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Test\Unit\Plugin\Catalog\Model\Product\Option\Type;

use Infrangible\BundleOptionQtyPrice\Plugin\Catalog\Model\Product\Option\Type\DefaultType;
use Infrangible\BundleOptionQtyPrice\Test\Unit\AbstractTestCase;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType as OptionType;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Tests the quantity in front of the values of linked custom options.
 */
#[AllowMockObjectsWithoutExpectations]
class DefaultTypeTest extends AbstractTestCase
{
    /**
     * Returns the option type of the custom option, configured for a quote item or, without, for the product.
     *
     * @param Option $option
     * @param bool $withItem
     *
     * @return OptionType
     */
    private function createOptionType(Option $option, bool $withItem): OptionType
    {
        $product = $this->createCartProduct();
        $item = $this->createStub(Item::class);
        $item->method('getProduct')->willReturn($product);

        $optionType = $this->getMockBuilder(OptionType::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $optionType->setOption($option);
        $optionType->setProduct($product);

        if ($withItem) {
            $optionType->setData('configuration_item', $item);
        }

        return $optionType;
    }

    /**
     * The cart and the order show the quantity of more than one piece; unlinked options keep their value.
     *
     * @return void
     */
    public function testAfterGetFormattedOptionValue(): void
    {
        $plugin = new DefaultType($this->createHelper());

        foreach ([true, false] as $withItem) {
            $this->assertSame(
                '3 x ENGRAVED',
                $plugin->afterGetFormattedOptionValue(
                    $this->createOptionType($this->createOption('2'), $withItem),
                    'ENGRAVED'
                )
            );
            $this->assertSame(
                'Happy birthday',
                $plugin->afterGetFormattedOptionValue(
                    $this->createOptionType($this->createOption(null), $withItem),
                    'Happy birthday'
                )
            );
        }
    }
}
