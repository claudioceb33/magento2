<?php
// phpcs:ignoreFile
namespace Ecommerce66\Core\Lib\Setup;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader as DirReader;
use Magento\Framework\Exception\FileSystemException;

class UpgradeData implements UpgradeDataInterface
{
    protected const MODULE_NAME = 'Ecommerce66_Core';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var PageInterfaceFactory
     */
    protected $pageInterfaceFactory;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var BlockInterfaceFactory
     */
    protected $blockInterfaceFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var WriterInterface
     */
    protected $writerInterface;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var DirReader
     */
    protected $moduleReader;

    /**
     * @var string
     */
    protected $moduleSetupPath;

    /**
     * UpgradeData constructor.
     *
     * @param Config                   $scopeConfig
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockInterfaceFactory    $blockInterfaceFactory
     * @param WriterInterface          $writerInterface
     * @param PageRepositoryInterface  $pageRepository
     * @param PageInterfaceFactory     $pageInterfaceFactory
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param DirReader                $dirReader
     * @param File                     $file
     */
    public function __construct(
        Config $scopeConfig,
        BlockRepositoryInterface $blockRepository,
        BlockInterfaceFactory $blockInterfaceFactory,
        WriterInterface $writerInterface,
        PageRepositoryInterface $pageRepository,
        PageInterfaceFactory $pageInterfaceFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DirReader $dirReader,
        File $file
    ) {
        $this->config = $scopeConfig;
        $this->blockRepository = $blockRepository;
        $this->blockInterfaceFactory = $blockInterfaceFactory;
        $this->writerInterface = $writerInterface;
        $this->pageRepository = $pageRepository;
        $this->pageInterfaceFactory = $pageInterfaceFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleReader = $dirReader;
        $this->file = $file;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setup = $setup;
        $this->setup->startSetup();

        /*
         * Update config values
         */
        $this->proccessConfigs($context);

        /*
         * Update cms pages
         */
        $this->proccessPages($context);

        /*
         * Update cms blocks
         */
        $this->proccessBlocks($context);

        $this->setup->endSetup();
    }

    /**
     * @param $context
     */
    protected function proccessConfigs($context)
    {
        foreach ($this->_getConfigArray() as $config) {
            if (isset($config['version']) && version_compare($context->getVersion(), $config['version'], '<')) {
                $this->setConfigData($config['path'], $config['value'],
                    isset($config['scope']) ? $config['scope'] : 'default',
                    isset($config['scope_id']) ? $config['scope_id'] : 0
                );
            }
        }
    }

    /**
     * @param $context
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function proccessPages($context)
    {
        foreach ($this->_getPageArray() as $page) {
            if (isset($page['version']) && version_compare($context->getVersion(), $page['version'], '<')) {
                $content = $this->getFileContent($page['content_filename']);
                $this->createCmsPage(
                    $page['identifier'],
                    $content,
                    $page['title'],
                    $page['options'],
                    $page['stores']
                );
            }
        }
    }

    /**
     * @param $context
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function proccessBlocks($context)
    {
        foreach ($this->_getBlockArray() as $block) {
            if (isset($block['version']) && version_compare($context->getVersion(), $block['version'], '<')) {
                $content = $this->getFileContent($block['content_filename']);
                $this->createCmsBlock(
                    $block['identifier'],
                    $content,
                    $block['title'],
                    $block['stores']
                );
            }
        }
    }

    /**
     * @return string
     */
    protected function getModuleName()
    {
        return static::MODULE_NAME;
    }

    /**
     * @return string
     */
    protected function getModuleSetupPath()
    {
        if (!$this->moduleSetupPath)
            $this->moduleSetupPath = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_SETUP_DIR,
            $this->getModuleName()
        );

        return $this->moduleSetupPath;
    }

    /**
     * @param string $contentFileName
     *
     * @return string
     */
    protected function getFileContent($contentFileName = '')
    {
        $content = '';
        try {
            $setupPath = $this->getModuleSetupPath();
            $content = $this->file->fileGetContents($setupPath.'/cms/'.$contentFileName);
        } catch (FileSystemException $e) {
            $content = $e->getMessage();
        }

        return $content;
    }

    /**
     * @param        $path
     * @param        $value
     * @param string $scope
     * @param int    $scopeId
     */
    public function setConfigData($path, $value, $scope = 'default', $scopeId = 0){
        $this->writerInterface->save($path, $value, $scope, $scopeId);
    }

    /**
     * @param        $id
     * @param string $html
     * @param string $title
     * @param int[]  $stores
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function createCmsBlock($id, $html = '', $title = '', $stores = [0])
    {
        try {
            // Create a search criteria for loading the block
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('identifier', $id)
                ->addFilter('store_id', reset($stores))
                ->create();

            // Use the repository to find the block
            $blockList = $this->blockRepository->getList($searchCriteria);

            if ($blockList->getTotalCount() > 0) {
                $blocks = $blockList->getItems();
                $cmsBlock = reset($blocks); // Return the first page (there should be only one)
                $cmsBlock->setTitle($title)
                    ->setContent($html);
            } else {
                $cmsBlock = $this->blockInterfaceFactory->create();

                $cmsBlock->setTitle($title)
                    ->setIdentifier($id)
                    ->setStoreId($stores)
                    ->setStores($stores)
                    ->setContent($html);
            }

        } catch (NoSuchEntityException $ex) {
            $cmsBlock = $this->blockInterfaceFactory->create();

            $cmsBlock->setTitle($title)
                ->setIdentifier($id)
                ->setStoreId($stores)
                ->setStores($stores)
                ->setContent($html);
        }

        $this->blockRepository->save($cmsBlock);
    }

    /**
     * @param        $id
     * @param string $html
     * @param string $title
     * @param array  $extraOptions
     * @param int[]  $stores
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function createCmsPage($id, $html = '', $title = '' , $extraOptions = [], $stores = [0])
    {
        try {
            // Create a search criteria for loading the block
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('identifier', $id)
                ->addFilter('store_id', reset($stores))
                ->create();

            // Use the repository to find the block
            $pageList = $this->pageRepository->getList($searchCriteria);

            if ($pageList->getTotalCount() > 0) {
                $pages = $pageList->getItems();
                $cmsPage = reset($pages); // Return the first page (there should be only one)
                $cmsPage->setTitle($title)
                    ->setContent($html)
                    ->setMetaTitle($title)
                    ->setMetaKeywords($title)
                    ->setMetaDescription($title);
            } else {
                $cmsPage = $this->pageInterfaceFactory->create();
                $cmsPage->setIdentifier($id)
                    ->setTitle($title)
                    ->setContent($html)
                    ->setMetaTitle($title)
                    ->setMetaKeywords($title)
                    ->setMetaDescription($title)
                    ->setPageLayout('1column')
                    ->setLayoutUpdateXml('')
                    ->setStoreId(reset($stores))
                    ->setStores($stores)
                    ->setIsActive(true);
            }
        } catch (NoSuchEntityException $ex) {
            $cmsPage = $this->pageInterfaceFactory->create();
            $cmsPage->setIdentifier($id)
                ->setTitle($title)
                ->setContent($html)
                ->setMetaTitle($title)
                ->setMetaKeywords($title)
                ->setMetaDescription($title)
                ->setPageLayout('1column')
                ->setLayoutUpdateXml('')
                ->setStoreId(reset($stores))
                ->setStores($stores)
                ->setIsActive(true);

            if (count($extraOptions)){
                $data = $cmsPage->getData();
                foreach ($extraOptions as $key => $value){
                    $data[$key] = $value;
                }
                $cmsPage->setData($data);
            }
        }

        $this->pageRepository->save($cmsPage);
    }

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
        ];
    }

    /**
     * @return array
     */
    protected function _getPageArray()
    {
        /*
         * Note: heredoc notation <<<HTML / HTML must be the last array element to work properly
         *
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
        ];
    }

    /**
     * @return array
     */
    protected function _getBlockArray()
    {
        /*
         * Note: heredoc notation <<<HTML / HTML must be the last array element to work properly
         *
         * @return Array with list od cms blocks
         */
        return [
            /*[
                'version' => '0.1.1',
                'identifier' => 'cms_block_id',
                'stores' => [0],
                'title' => 'Block Title',
                'content_filename' => 'block_identifier.html'
            ],*/
        ];
    }

}
