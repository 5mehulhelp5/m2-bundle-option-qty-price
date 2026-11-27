<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\BundleOptionQtyPrice\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use FeWeDev\Base\Arrays;
use Infrangible\BundleOptionQtyPrice\Helper\Data;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

/**
 * Adds the field "Bundle Option Qty Price" to the custom options of bundle products in the product form of the admin.
 */
class CustomOptions
{
    /** Sort order of the field among the common fields of custom options. */
    public const SORT_ORDER = 215;

    /**
     * @param Arrays $arrays
     * @param LocatorInterface $locator
     */
    public function __construct(protected Arrays $arrays, protected LocatorInterface $locator)
    {
    }

    /**
     * Adds the field "Bundle Option Qty Price" with the bundle options of the product to the custom options.
     *
     * Only saved bundle products with bundle options get the field. The value "0" ("--Please Select--") links the
     * custom option to no bundle option.
     *
     * @param CustomOptionsModifier $subject
     * @param array $meta
     * @phpstan-param array<int|string, mixed> $meta
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     *
     * @noinspection PhpUnusedParameterInspection the signature of the plugin is given
     */
    public function afterModifyMeta(CustomOptionsModifier $subject, array $meta): array
    {
        $product = $this->locator->getProduct();

        if (! $product instanceof Product || ! $product->getId()) {
            return $meta;
        }

        $typeInstance = $product->getTypeInstance();

        if (! $typeInstance instanceof BundleType) {
            return $meta;
        }

        $bundleOptions = [];

        foreach ($typeInstance->getOptionsCollection($product) as $option) {
            if ($option instanceof Option) {
                $bundleOptions[] = ['value' => $option->getId(), 'label' => $option->getTitle()];
            }
        }

        if (count($bundleOptions) === 0) {
            return $meta;
        }

        array_unshift($bundleOptions, ['value' => '0', 'label' => __('--Please Select--')->render()]);

        return $this->arrays->addDeepValue(
            $meta,
            [
                CustomOptionsModifier::GROUP_CUSTOM_OPTIONS_NAME,
                'children',
                CustomOptionsModifier::GRID_OPTIONS_NAME,
                'children',
                'record',
                'children',
                CustomOptionsModifier::CONTAINER_OPTION,
                'children',
                CustomOptionsModifier::CONTAINER_COMMON_NAME,
                'children',
                Data::BUNDLE_OPTION_QTY_PRICE
            ],
            $this->getFieldConfig($bundleOptions)
        );
    }

    /**
     * Returns the configuration of the field: a select of the bundle options.
     *
     * @param array $bundleOptions
     * @phpstan-param list<array{value: mixed, label: mixed}> $bundleOptions
     *
     * @return array
     * @phpstan-return array<string, mixed>
     */
    private function getFieldConfig(array $bundleOptions): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType'         => Text::NAME,
                        'formElement'      => Select::NAME,
                        'componentType'    => Field::NAME,
                        'component'        => 'Magento_Ui/js/form/element/ui-select',
                        'elementTmpl'      => 'ui/grid/filters/elements/ui-select',
                        'disableLabel'     => true,
                        'filterOptions'    => false,
                        'multiple'         => false,
                        'showCheckbox'     => true,
                        'levelsVisibility' => 1,
                        'dataScope'        => Data::BUNDLE_OPTION_QTY_PRICE,
                        'label'            => __('Bundle Option Qty Price')->render(),
                        'options'          => $bundleOptions,
                        'sortOrder'        => self::SORT_ORDER
                    ]
                ]
            ]
        ];
    }
}
