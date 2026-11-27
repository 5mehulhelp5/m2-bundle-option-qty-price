# Changelog

## 2.0.0

### Breaking changes

- Requires Magento 2.4.6 or newer and PHP 8.1 or newer. Older versions of Magento and PHP 7.1 to 8.0 are no longer
  supported; use version 1.x for these.
- Requires `infrangible/m2-bundle-option-selection` 2.0, `infrangible/m2-catalog-product-option` 2.0,
  `infrangible/m2-core` 5.0, `infrangible/m2-foundation` 3.0 and `fewedev/base` 2.4.3.
  `infrangible/m2-catalog-product-option-wrapper` is no longer required, the module does not use it.
- The helper has new methods: `getSelectionQty()` and `getOrderItemSelectionQty()` replace `getProductOptionQty()`,
  `encodeConfig()` replaces `getConfig()`. The constant `CustomOptions::FIELD_BUNDLE_OPTION_QTY_PRICE_NAME` is replaced
  by `Helper\Data::BUNDLE_OPTION_QTY_PRICE`. The classes have native types and use constructor property promotion.
- The column `bundle_option_qty_price` is declared in `etc/db_schema.xml` instead of an install script; existing
  installations keep the column and its data.

### Fixed

- With a quantity of more than 1 in the cart, the price of a linked custom option was multiplied with the difference
  between the quantity of the bundle option and the quantity in the cart: with 3 pieces in the bundle option and 2
  bundles in the cart it counted twice instead of three times, with 1 piece and 2 bundles not at all.
- A custom option linked to a bundle option with checkboxes or a multiple select was not multiplied, and adding such a
  bundle to the cart failed in developer mode ("Array to string conversion").
- The product page multiplied the price with the default quantity of the bundle option instead of the quantity the
  shopper entered, so it showed a lower price than the cart.
- The order showed the value of a linked custom option without its quantity; it now shows it like the cart, for
  example "3 x ENGRAVED".

### Changed

- With Infrangible Catalog Product Option Price, the option prices of the order items are multiplied too.

### Added

- Translations into 24 languages.
- Unit tests.
