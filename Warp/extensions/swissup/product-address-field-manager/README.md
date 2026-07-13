# Address Field Manager

### Installation

```bash
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/product-address-field-manager
bin/magento module:enable\
    Swissup_Core\
    Swissup_FieldManager\
    Swissup_AddressFieldManager
bin/magento setup:upgrade
```
