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

namespace Lof\Formbuilder\Controller\Form;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Image extends Action
{

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param ImageHelper $imageHelper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        ImageHelper $imageHelper,
        JsonFactory $resultJsonFactory
    ) {
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        header('Content-Type: text/javascript');
        $payload = $this->getRequest()->getPost();
        $product =  $this->getProductBySku(($payload['sku']));
        $imageUrl = $this->getImageUrl($product);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['url' => $imageUrl]);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getImageUrl(Product $product): string
    {
        return $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
    }
}
