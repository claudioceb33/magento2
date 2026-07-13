# MAGENTO 2 FORM BUILDER

    ``landofcoder/module-formbuilder``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)

## Main Functionalities
Link: https://landofcoder.com/magento-2-form-builder.html

Magento 2 Form Builder with drag n drop & visual interface lets you build multiple forms in minutes.

This module helps to create multiple website forms quickly and effectively to gather customers’ information. No specialized knowledge or coding experience required, Store owners can believe that it won’t take long and take much effort to get used to the system.

- 75% Faster Than Default - 30% decrease in cost
- Drag n Drop Magento 2 Form Builder FEATURED
- 20+ Premade Form - 14 pre-made form templates
- Display multiple forms on one page
- Collect and analyze customer data
- ReCaptcha Security, Spam Protection
- Visual Design Options: Background, Font, Icon, Border
- [New] Support PWA, REST API, GraphQL

## Installation
\* = in production please use the `--keep-generated` option

### 1. The module require setup libraries via composer:
Run commands to setup libs:
```
composer require picqer/php-barcode-generator
composer require mpdf/mpdf
```

### 2. Upload Zip file via FTP to server then run magento 2 commands:

 - Unzip the zip file in `app/code/Lof`
 - Enable the module by running `php bin/magento module:enable Lof_Formbuilder`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`
# module-formbuilder-upgrade
