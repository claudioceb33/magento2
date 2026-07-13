# Customer Field Manager

### Installation

```bash
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/product-customer-field-manager
bin/magento module:enable\
    Swissup_Core\
    Swissup_FieldManager\
    Swissup_CustomerFieldManager
bin/magento setup:upgrade
```
