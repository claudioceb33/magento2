# Mage2 Module Ecommerce66 Widgets

    ``ecommerce66/module-widgets``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Banner countdown widget

## Installation

### Type 1: Zip file

 - Unzip the zip file in `app/code/Ecommerce66`
 - Enable the module by running `php bin/magento module:enable Ecommerce66_Widgets`
 - Apply database updates by running `php bin/magento setup:upgrade --keep-generated`
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require ecommerce66/module-widgets`
 - enable the module by running `php bin/magento module:enable Ecommerce66_Widgets`
 - apply database updates by running `php bin/magento setup:upgrade --keep-generated`
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - Enabled (countdown/options/enabled)


## Specifications

 - Helper
	- Ecommerce66\Widgets\Helper\Data

 - Widget
	- Countdown


## Attributes



