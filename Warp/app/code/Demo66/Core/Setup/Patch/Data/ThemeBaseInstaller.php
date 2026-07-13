<?php
declare(strict_types=1);

namespace Demo66\Core\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Theme\Model\Config as ThemeConfig;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;
use \Magento\Theme\Model\Theme\Registration;

class ThemeBaseInstaller implements DataPatchInterface, PatchRevertableInterface
{
    protected const WIDS = [
        'default'   => 0,
        'base'      => 1
    ];

    protected const THEMES = [
        'Ecommerce66/base'    => 0,
        'Demo66/base'         => 1
    ];
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
     * @var ThemeConfig
     */
    private $themeConfig;

    /**
     * @var ThemeCollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * @var Registration
     */
    private $registration;

    /**
     * ThemeBaseInstaller constructor.
     *
     * @param ModuleDataSetupInterface   $moduleDataSetup
     * @param WriterInterface            $writerInterface
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ThemeProviderInterface     $themeInterface
     * @param ThemeCollectionFactory     $collectionFactory
     * @param ThemeConfig                $config
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $writerInterface,
        WebsiteRepositoryInterface $websiteRepository,
        ThemeProviderInterface $themeInterface,
        ThemeCollectionFactory $themeCollectionFactory,
        ThemeConfig $themeConfig,
        Registration $registration
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->writerInterface = $writerInterface;
        $this->websiteRepository = $websiteRepository;
        $this->themeInterface = $themeInterface;
        $this->themeConfig = $themeConfig;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->registration = $registration;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $installer = $this->moduleDataSetup->getConnection()->startSetup();

        $this->registration->register();

        $scope = 'website';
        $websiteConfigs = $this->getWebsiteConfigs();
        foreach ($websiteConfigs as $code => $config) {
            foreach ($config as $path => $value) {
                $scopeId = isset(self::WIDS[$code]) ? self::WIDS[$code] : 0;
                $this->setConfigData(
                    $path,
                    $value,
                    ($code != 'default') ? $scope : $code,
                    ($code != 'default') ? $scopeId : 0
                );
            }
        }

        $themeTableName = $installer->getTableName('theme');
        $installer->delete($themeTableName, 'theme_id IN (4,5,6)');

        $this->assignThemes();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public function getWebsiteConfigs()
    {
        return [
            'default' => [
                'design/theme/theme_id' => '0'
            ],
            'base' => [
                'design/theme/theme_id' => '9'
            ]
        ];
    }

    /**
     *
     */
    protected function assignThemes()
    {
        $themes = $this->themeCollectionFactory->create()->loadRegisteredThemes();
        $themeCodes = array_keys(self::THEMES);
        foreach ($themes as $theme) {
            if (in_array($theme->getCode(), $themeCodes)) {
                $scopeId = self::THEMES[$theme->getCode()];
                $this->themeConfig->assignToStore(
                    $theme,
                    ($scopeId == 0) ? 'default' : 'websites' //ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            }
        }
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
