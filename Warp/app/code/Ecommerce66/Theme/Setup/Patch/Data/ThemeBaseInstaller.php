<?php
declare(strict_types=1);

namespace Ecommerce66\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

class ThemeBaseInstaller implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $writerInterface;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var ThemeProviderInterface
     */
    private $themeInterface;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $writerInterface
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ThemeProviderInterface $themeInterface
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $writerInterface,
        WebsiteRepositoryInterface $websiteRepository,
        ThemeProviderInterface $themeInterface
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->writerInterface = $writerInterface;
        $this->websiteRepository = $websiteRepository;
        $this->themeInterface = $themeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $scopeId = 0;
        $scope = 'default';
        $websiteConfigs = $this->getWebsiteConfigs();
        foreach ($websiteConfigs as $config) {
            foreach ($config as $path => $value) {
                $this->setConfigData($path, $value, $scope, $scopeId);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public function getWebsiteConfigs()
    {
        return [
            'default' => [
                'web/cookie/cookie_lifetime' => '86000',
                'catalog/review/active' => '0',
                'catalog/layered_navigation/display_product_count' => '0',
                'sales/reorder/allow' => '1',
                'sales/instant_purchase/active' => '0',
                'checkout/options/guest_checkout' => '1',
                'checkout/options/enable_agreements' => '0',
                'checkout/cart/enable_clear_shopping_cart' => '1',
                'carriers/freeshipping/active' => '1',
                'carriers/freeshipping/title' => 'Shipping costs (will be calculated after delivery)',
                'admin/security/session_lifetime' => '21000',
                'admin/security/password_is_forced' => '0',
                'dev/debug/template_hints_storefront' => '0',
                'dev/debug/template_hints_admin' => '0',
                'dev/debug/template_hints_blocks' => '0',
                'general/country/default' => 'AR',
                'general/locale/timezone' => 'America/Argentina/Buenos_Aires',
                'general/locale/code' => 'es_AR',
                'general/locale/weight_unit' => 'kgs',
                'general/store_information/country_id' => 'AR',
                'system/security/max_session_size_admin' => '1500000',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies() :array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases() :array
    {
        return [];
    }

    /**
     * @param $path
     * @param $value
     * @param $scope
     * @param $scopeId
     */
    public function setConfigData($path, $value, $scope = 'default', $scopeId = 0)
    {
        $this->writerInterface->save($path, $value, $scope, $scopeId);
    }
}
