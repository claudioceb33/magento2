# Swissup SEO Core

Dummy SEO Core module. It's purpose is to add swissup menu and config sections.

## Installation

### For clients

Please do not install this module. It will be installed automatically as a dependency.

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-seo-core --prefer-source
bin/magento module:enable Swissup_SeoCore
bin/magento setup:upgrade
```
