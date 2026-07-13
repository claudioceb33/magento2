<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller\Adminhtml\FormData;

use Magento\Backend\App\Action;

class Export extends Action
{

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\ResourceModel\FormData\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem
     */

    protected $filesystem;

    /**
     * @var Hexamarvel\FlexibleForm\Model\FieldFactory
    */

    protected $fieldFactory;

    /**
     * @var Magento\Framework\App\Response\Http\FileFactory
    */
      protected $_fileFactory;

    /**
      * @var Magento\Framework\Serialize\Serializer\Serialize
    */
       protected $serializer;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param FormFactory $formFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Hexamarvel\FlexibleForm\Model\ResourceModel\FormData\CollectionFactory $collectionFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->fieldFactory = $fieldFactory;
        $this->_fileFactory = $fileFactory;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->getCollection($this->collectionFactory->create());
        $dataFinal = [];
        $formId = $collection->getFirstItem()->getFormId();
        $fieldCollection = $this->fieldFactory->create()->getCollection()
                                              ->addFieldToFilter('form_id', $formId);
        $headerFinal = [];
        $header = [];
        foreach ($fieldCollection as $key => $field) {
            $header[$key] = $field->getTitle();
        }

        foreach ($collection->getItems() as $data) {
            $exportData = [];
            if ($this->is_serialized($data->getValue())) {
                $value = $this->serializer->unserialize($data->getValue());
            } else {
                $value = json_decode($data->getValue(), true);
            }

            foreach ($header as $key => $label) {
                if (isset($value[$key]) && is_array($value[$key])) {
                    if (isset($value[$key][0])) {
                        $exportData[$key] = $value[$key][0];
                    } else {
                        $exportData[$key] = json_encode($value[$key]);
                    }
                } else {
                    $exportData[$key] = (isset($value[$key])) ? $value[$key] : '';
                }
            }

            array_push($dataFinal, $exportData);
        }

        array_push($headerFinal, $header);
        $final = array_merge($headerFinal, $dataFinal);

        $fileDirectoryPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);

        if (!is_dir($fileDirectoryPath)) {
            mkdir($fileDirectoryPath, 0777, true);
        }

        $fileName = 'form_builder_export.csv';
        $filePath =  $fileDirectoryPath . '/' . $fileName;

        $this->csvProcessor
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath, $final);

        $content = [];
        $content['type'] = 'filename';
        $content['value'] = $fileName;
        $content['rm'] = '1';

        return $this->_fileFactory->create(
            $fileName,
            $content,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }

    public function is_serialized($value, &$result = null)
    {

        if (!is_string($value)) {
            return false;
        }

        if ($value === 'b:0;') {
            $result = false;
            return true;
        }
        $length = strlen($value);
        $end    = '';
        switch ($value[0]) {
            case 's':
                if ($value[$length - 2] !== '"') {
                    return false;
                }
            case 'b':
            case 'i':
            case 'd':
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';
                if ($value[1] !== ':') {
                    return false;
                }
                switch ($value[2]) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                        break;
                    default:
                        return false;
                }
            case 'N':
                $end .= ';';
                if ($value[$length - 1] !== $end[0]) {
                    return false;
                }
                break;
            default:
                return false;
        }

        if (($result = $this->serializer->unserialize($value)) === false) {
            $result = null;
            return false;
        }

        return true;
    }


    private function getCollection($collection)
    {
        $selected = $this->getRequest()->getParam('selected');

        try {
            if (is_array($selected) && !empty($selected)) {
                $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), ['in' => $selected]);
            } else {
            }
        } catch (\Exception $e) {
        }

        return $collection;
    }
}
