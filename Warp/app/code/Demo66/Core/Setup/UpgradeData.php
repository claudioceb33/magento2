<?php
// phpcs:ignoreFile
namespace Demo66\Core\Setup;

class UpgradeData extends \Ecommerce66\Core\Lib\Setup\UpgradeData
{
    protected const MODULE_NAME = 'Demo66_Core';

    /**
     * @return \string[][]
     */
    protected function _getConfigArray()
    {
        /*
         * You can add scope and scope_id to array:
         *      'scope' => 'default', 'scope_id' => 0,
         * Note:
         *      if you want to use heredoc notation for value <<<HTML / HTML this must be the last array element without
         *      ending colon or semicolon to work properly
         *
         * @return Array with configurations
         */
        return [
            /*[ //example element
                'version' => '0.1.1',
                'path' => 'path/to/config',
                'scope' => 'default',
                'scope_id' => 0,
                'value' => '' //last element if you want to use heredoc here, example:
                'value' => <<<HTML
value with heredoc
multiline
HTML
            ],*/
            /*[
                'version' => '0.1.1',
                'path' => 'test/config/value',
                'value' => 'test'
            ],*/

            [
                'version' => '0.1.1',
                'path' => 'catalog/frontend/list_mode',
                'value' => 'grid'
            ],

            [
                'version' => '0.1.1',
                'path' => 'currency/options/base',
                'value' => 'ARS'
            ],

            [
                'version' => '0.1.1',
                'path' => 'currency/options/default',
                'value' => 'ARS'
            ],

            [
                'version' => '0.1.1',
                'path' => 'currency/options/allow',
                'value' => 'ARS'
            ],
        ];
    }

    /**
     * @return array
     */
    protected function _getPageArray()
    {
        /*
         * @return Array with list od cms pages
         */
        return [
            /*[
                'version' => '0.1.1',
                'identifier' => 'cms_page_id',
                'stores' => [0],
                'options' => [
                    'content_heading' => '',
                    'meta_keywords' => 'test keywords',
                    'meta_description' => 'test description',
                ],
                'title' => 'Page title',
                'content_filename' => 'page_identifier.htm'
            ],*/

            [
                'version' => '0.1.2',
                'identifier' => 'home',
                'stores' => [0],
                'options' => [
                    'content_heading' => 'Demo66',
                    'meta_keywords' => 'Demo66',
                    'meta_description' => 'Demo66',
                ],
                'title' => 'Demo66 - Home',
                'content_filename' => 'page_home.htm'
            ],

            [
                'version' => '0.1.1',
                'identifier' => 'no-route',
                'stores' => [0],
                'options' => [
                    'content_heading' => 'Demo66',
                    'meta_keywords' => 'Demo66',
                    'meta_description' => 'Demo66',
                ],
                'title' => 'Página no encontrada - Demo66',
                'content_filename' => 'page_404.htm'
            ],

        ];
    }

    /**
     * @return array
     */
    protected function _getBlockArray()
    {
        /*
         * @return Array with list od cms blocks
         */
        return [

            [
                'version' => '0.1.1',
                'identifier' => 'newsletter',
                'stores' => [0],
                'title' => 'Newsletter',
                'content_filename' => 'block_newsletter.htm'
            ],

            [
                'version' => '0.1.1',
                'identifier' => 'footer_blocks',
                'stores' => [0],
                'title' => 'Footer Blocks',
                'content_filename' => 'block_footer_blocks.htm'
            ],

            [
                'version' => '0.1.1',
                'identifier' => 'copyright_footer',
                'stores' => [0],
                'title' => 'Copyright',
                'content_filename' => 'block_copyright.htm'
            ],

        ];
    }

}
