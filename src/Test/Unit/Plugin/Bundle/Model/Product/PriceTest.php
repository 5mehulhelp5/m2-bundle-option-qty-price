<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Test\Unit\Plugin\Bundle\Model\Product;

use FeWeDev\Base\Variables;
use Infrangible\BundleOptionQtyPrice\Plugin\Bundle\Model\Product\Price;
use Infrangible\BundleOptionQtyPrice\Test\Unit\AbstractTestCase;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Tests the final price of bundles with custom options linked to bundle options.
 */
#[AllowMockObjectsWithoutExpectations]
class PriceTest extends AbstractTestCase
{
    /**
     * The prices of linked custom options count once per piece of the bundle option, independent of the quantity of
     * the bundle in the cart: Magento's final price contains them once, the plugin adds them twice more for 3 pieces
     * (2.00) and three times more for 4 pieces (1.50).
     *
     * @return void
     */
    public function testAfterGetFinalPrice(): void
    {
        $product = $this->createCartProduct(
            ['option_ids' => '1,2,3', 'option_1' => 'ENGRAVED', 'option_2' => 'Happy birthday', 'option_3' => 'WRAP'],
            [
                1 => $this->createOption('2', 2.0),
                2 => $this->createOption(null, 1.0),
                3 => $this->createOption('5', 1.5)
            ]
        );
        $subject = $this->createStub(BundlePrice::class);
        $subject->method('getBasePrice')->willReturn(50.0);
        $plugin = new Price(new Variables(), $this->createHelper());

        foreach ([1.0, 2.0, 3.0, null] as $qty) {
            $this->assertSame(73.5 + 4.0 + 4.5, $plugin->afterGetFinalPrice($subject, 73.5, $qty, $product));
        }
    }

    /**
     * Products without selected custom options keep their price.
     *
     * @return void
     */
    public function testWithoutOptions(): void
    {
        $plugin = new Price(new Variables(), $this->createHelper());

        $this->assertSame(
            54.0,
            $plugin->afterGetFinalPrice($this->createStub(BundlePrice::class), 54.0, 1.0, $this->createCartProduct())
        );
    }
}
