# ProLabelsConfigurableProduct module

The `ProLabelsConfigurableProduct` module extands `ProLabels` module with additional settings for Magento's configurable products.

## Installation

### Swisssuplabs and Argento customer

Check this article - http://docs.swissuplabs.com/m2/extensions/prolabels/installation/composer/

### Internal Swissuplabs developer

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-pro-labels-configurable-product --prefer-source
bin/magento setup:upgrade
```
