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

namespace Lof\Formbuilder\Controller\Adminhtml\Form;

use Lof\Formbuilder\Controller\Adminhtml\Form;
use Lof\Formbuilder\Model\Message;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;

class Save extends Form
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected Filesystem\Directory\WriteInterface $directory;
    /**
     * @var FileFactory
     */
    protected FileFactory $fileFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutInterface $layout
     * @param Message $message
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        LayoutInterface $layout,
        Message $message,
        Filesystem $filesystem,
        FileFactory $fileFactory
    ) {
        parent::__construct($context, $coreRegistry);
        $this->layout = $layout;
        $this->message = $message;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($this->getRequest()->getParam("export_csv") && $this->getRequest()->getParam('form_id')) {
            $id = $this->getRequest()->getParam('form_id');
            $model = $this->_objectManager->create(\Lof\Formbuilder\Model\Form::class)->load($id);
            $messages = $this->message->getCollection();
            $messages->addFieldToFilter("form_id", (int)$id);
            $params = [];
            foreach ($messages as $message) {
                $params[] = json_decode($message->getFormData(), true);
            }
            $name = $model->getTitle() . '-messages';
            $file = 'export/formbuilder/' . $name . '.csv';
            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();
            $headers = $fields = [];
            foreach ($params as $row) {
                foreach ($row as $v) {
                    if (!isset($fields[$v['cid']])) {
                        $fields[$v['cid']] = '';
                        $headers[] = $v['label'];
                    }
                }
            }
            $stream->writeCsv($headers);
            foreach ($params as $row) {
                $rowData = $fields;
                foreach ($row as $v) {
                    $rowData[$v['cid']] = strip_tags($v['value']);
                }
                $stream->writeCsv($rowData);
            }
            $stream->unlock();
            $stream->close();
            $file = [
                'type' => 'filename',
                'value' => $file,
                'rm' => true
            ];
            return $this->fileFactory->create($name . '.csv', $file, 'var');
        }
        if ($data) {
            if (!empty($data['design'])) {
                $id = $this->getRequest()->getParam('form_id');
                $model = $this->_objectManager->create(\Lof\Formbuilder\Model\Form::class)->load($id);
                if (!$model->getId() && $id) {
                    $this->messageManager->addErrorMessage(__('This form no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                $model->setData($data);
                try {
                    $model->save();
                    $this->messageManager->addSuccessMessage(__('You saved the form.'));
                    $this->_objectManager->get(Session::class)->setFormData(false);

                    if ($this->getRequest()->getParam("duplicate")) {
                        unset($data['form_id']);
                        $data['identifier'] = $data['identifier'] . time();

                        $form = $this->_objectManager->create(\Lof\Formbuilder\Model\Form::class);
                        $form->setData($data);
                        try {
                            $form->save();
                            $this->messageManager->addSuccessMessage(__('You duplicated this form.'));
                            return $resultRedirect->setPath('*/*/edit', ['form_id' => $model->getId()]);
                        } catch (LocalizedException | \RuntimeException $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                        } catch (\Exception $e) {
                            $this->messageManager->addExceptionMessage(
                                $e,
                                __('Something went wrong while duplicating the form.')
                            );
                        }
                    }

                    if ($this->getRequest()->getParam("new")) {
                        return $resultRedirect->setPath('*/*/new');
                    }
                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath('*/*/edit', ['form_id' => $model->getId()]);
                    }
                    return $resultRedirect->setPath('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->_objectManager->get(Session::class)->setFormData($data);
                    return $resultRedirect->
                    setPath('*/*/edit', ['form_id' => $this->getRequest()->getParam('form_id')]);
                }
            }
            $this->getMessageManager()->addErrorMessage('Errors: No response fields');
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }
}
