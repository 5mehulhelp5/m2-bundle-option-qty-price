# Infrangible Bundle Option Qty Price

Multiplies the price of a custom option of a bundle product with the quantity of the products selected in a linked
bundle option, for example an engraving of 2.00 per brick: with 3 bricks in the kit, the engraving costs 6.00.

## How it works

- Custom options of saved bundle products with bundle options get the field "Bundle Option Qty Price" in the admin
  (Catalog > Products > Customizable Options): a select of the bundle options of the product. "--Please Select--"
  removes the link.
- The quantity is the one of the product selected in the linked bundle option; for checkboxes and multiple selects it
  is the sum of the quantities of all selected products. Magento adds the price of a custom option once per bundle; the
  module adds it once more for every further piece. The quantity of the bundle in the cart multiplies the result as
  usual.
- The product page shows the multiplied price as soon as the shopper selects a product or changes the quantity in the
  linked bundle option.
- The cart and the order show the quantity in front of the value of the custom option, for example "3 x ENGRAVED".
- With [Infrangible Catalog Product Option Price](https://www.infrangible.dev/modules/catalog-product-option-price),
  the separately stored option prices of the quote items and the order items are multiplied too.
- Magento uses the prices of custom options only for bundles with a fixed price, so the module has no effect on
  bundles with a dynamic price.
- The links are saved in the column `bundle_option_qty_price` of `catalog_product_option`.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.6 or newer
- PHP 8.1 or newer
- The product page part is built for the standard Magento frontend (Luma and Luma-based themes).

## Installation

```bash
composer require infrangible/m2-bundle-option-qty-price
bin/magento module:enable Infrangible_BundleOptionQtyPrice
bin/magento setup:upgrade
```

In production mode, also run `bin/magento setup:di:compile` and `bin/magento setup:static-content:deploy`.

## License

Infrangible Bundle Option Qty Price is licensed under the MIT License - see the LICENSE file for details.
