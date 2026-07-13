<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
namespace Lof\Formbuilder\Block;

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\Model;
use Lof\Formbuilder\Model\Modelcategory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;

class Field extends Template
{
    /**
     * @var Modelcategory
     */
    private $modelCategory;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var Data
     */
    protected $formbuilderHelper;

    /**
     * Field constructor.
     * @param Context $context
     * @param Modelcategory $modelCategory
     * @param Model $model
     * @param Data $formbuilderHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Modelcategory $modelCategory,
        Model $model,
        Data $formbuilderHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->modelCategory = $modelCategory;
        $this->model = $model;
        $this->formbuilderHelper = $formbuilderHelper;
    }

    /**
     * @param $str
     * @return string
     * @throws \Exception
     */
    public function filterMessage($str)
    {
        return $this->formbuilderHelper->filter($str);
    }

    /**
     * @param int $categoryId
     * @param int $maxLevel
     * @return array
     */
    public function getCategories(int $categoryId = 0, int $maxLevel = 2)
    {
        $return = [];
        if ($categoryId) {
            $category = $this->modelCategory->load($categoryId);
            $countLevel = 0;
            if (1 == $category->getStatus()) {
                $tmp = [
                    "id" => $category->getId(),
                    "label" => $category->getTitle()
                ];
                $modelCollection = $this->model->getCollection();
                $modelCollection->addFieldToFilter("category_id", $categoryId)
                    ->addFieldToFilter("status", 1)
                    ->setOrder("position", "ASC");
                $tmp["models"] = $modelCollection;
                $return[] = $tmp;
                $countLevel++;

                //get category children
                if ($countLevel < $maxLevel) {
                    $return = array_merge($return, $this->getTreeCategories($categoryId, $maxLevel, $countLevel));
                }
            }
        }
        return $return;
    }

    /**
     * @param int $categoryId
     * @param int $maxLevel
     * @param int $countLevel
     * @return array
     */
    public function getTreeCategories(int $categoryId = 0, int $maxLevel = 2, int $countLevel = 2)
    {
        $return = [];
        $collection = $this->modelCategory->getCollection();
        $collection->addFieldToFilter("parent_id", $categoryId)
            ->addFieldToFilter("status", 1)
            ->setOrder("position", "ASC")
            ->getSelect()
            ->limit(1);
        if (0 < $collection->getSize()) {
            foreach ($collection as $item) {
                $tmp = ["id" => $item->getId(), "label" => $item->getTitle(), "models" => []];
                $return[] = $tmp;
                if ($countLevel < $maxLevel) {
                    $return = array_merge(
                        $return,
                        $this->getTreeCategories($item->getId(), $maxLevel, $countLevel + 1)
                    );
                }
            }
        }
        return $return;
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @param $sku
     * @return mixed
     */
    public function getDataProducts($sku)
    {
        $objectManager = ObjectManager::getInstance();
        $productCollection = $objectManager->create(Collection::class);
        $productCollection->addAttributeToSelect('name', 'price', 'image')->addAttributeToFilter('sku', ['eq' => $sku]);
        $collection = $productCollection->load();
        return $collection->getData();
    }

    /**
     * @param $price
     * @return mixed
     */
    public function formatPrice($price)
    {
        $objectManager = ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objectManager->create(\Magento\Framework\Pricing\Helper\Data::class);
        return $priceHelper->currency($price, true, false);
    }

    /**
     * @param string $string
     * @param bool $escapeSingleQuote
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        return $this->_escaper->escapeHtmlAttr($string, $escapeSingleQuote);
    }
}
