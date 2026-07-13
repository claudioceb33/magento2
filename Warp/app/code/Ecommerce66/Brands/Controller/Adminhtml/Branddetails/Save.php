<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Controller\Adminhtml\Branddetails;

use Magento\Backend\App\Action\Context;
use \Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Validation\ValidationException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Ecommerce66\Brands\Helper\Data as HelperBrands;
use Ecommerce66\Brands\Model\BrandDetails;
use Ecommerce66\Brands\Model\ImageUploader;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     *
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var HelperBrands
     */
    protected $helperBrands;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Save constructor.
     *
     * @param Context                $context
     * @param DataPersistorInterface $dataPersistor
     * @param UploaderFactory        $uploaderFactory
     * @param Filesystem             $filesystem
     * @param ImageUploader          $imageUploader
     * @param Json                   $json
     * @param CollectionFactory      $collectionFactory
     * @param HelperBrands           $helperBrands
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ImageUploader $imageUploader,
        Json $json,
        CollectionFactory $collectionFactory,
        HelperBrands $helperBrands
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageUploader = $imageUploader;
        $this->json = $json;
        $this->categoryCollection = $collectionFactory;
        $this->helperBrands = $helperBrands;

        parent::__construct($context);
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ecommerce66_Brands::brand_details_save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('brand_details_id');

            $model = $this->_objectManager->create(BrandDetails::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Brand Details no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->addData($this->prepareImagesData($data));
                $model->save();
                $this->updateCategory($model);
                $this->messageManager->addSuccessMessage(__('You saved the Brand Details.'));
                $this->dataPersistor->clear('ecommerce66_brands_brand_details');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['brand_details_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Brand Details.')
                );
            }

            $this->dataPersistor->set('ecommerce66_brands_brand_details', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['brand_details_id' => $this->getRequest()->getParam('brand_details_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws LocalizedException
     */
    protected function prepareImagesData(array $data)
    {
        $imageIds = [
            'logo',
            'image',
            'mobile_image',
            'landing_image',
            'landing_mobile_image',
            'product_banner_img'
        ];

        foreach ($imageIds as $imageId) {
            if (isset($data[$imageId]) && count($data[$imageId])) {
                $image = $data[$imageId][0];
                if (isset($image['name']) && isset($image['tmp_name'])) {
                    $imageName = $this->imageUploader->moveFileFromTmp($image['name']);
                    $data[$imageId] = 'e66/brands/' . $imageName;
                } elseif (isset($image['name']) && !isset($image['tmp_name'])) {
                    $imageName = explode('media/', $image['url']);
                    $data[$imageId] = end($imageName);
                }
            }
            if (!isset($data[$imageId])) {
                $data[$imageId] = null;
            }
        }

        return $data;
    }

    /**
     * @param BrandDetails $brand
     */
    protected function updateCategory(BrandDetails $brand)
    {
        $catId = $brand->getCategoryId();
        if ((int)$catId > 0) {
            try {
                $collection = $this->categoryCollection->create();
                $collection->addFieldToSelect('*')
                    ->addFieldToFilter('entity_id', ['eq' => $catId]);
                $category = $collection->getFirstItem();
                if ($category->getId()) {
                    $category->setIsBrand($brand->getActive());
                    $category->setRelatedBrand((int)$brand->getOptionId());
                    $category->save();
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
    }
}
