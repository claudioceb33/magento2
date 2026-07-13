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

use JetBrains\PhpStorm\NoReturn;
use Lof\Formbuilder\Model\Model;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Modeldropdown extends Action
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;


    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Model $model
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Model $model,
        Escaper $escaper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry     = $registry;
        $this->model            = $model;
        $this->escaper          = $escaper;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        header('Content-Type: text/javascript');
        $post = $this->getRequest()->getPost();
        $dataReturn = 'Element.update(';
        if ($post) {
            $targetId = $post['target_id'] ?? "";
            $value = isset($post['value']) ? (int)$post['value'] : 0;
            $dataReturn .= '"' . $targetId . '",' . "'";

            if ($value) {
                $collection = $this->model->getCollection();
                $collection->addFieldToFilter("parent_id", $value)->getSelect()->order('position', 'asc');
                $title = __("Select a option");
                $title = str_replace("'", "\'", $title);
                $tmp = '<option data-id="0" value="">' . $this->escaper->escapeHtml($title) . '</option>';
                $dataReturn .= $tmp;

                if (0 < $collection->getSize()) {
                    foreach ($collection as $item) {
                        $title = $item->getTitle();
                        $title = str_replace("'", "\'", $title);
                        $tmp = '<option data-id="' . $item->getId() . '" value="' .
                            $this->escaper->escapeHtml($title) . '">' .
                            $this->escaper->escapeHtml($title) . '</option>';

                        $dataReturn .= $tmp;
                    }
                }
            }

            $dataReturn .= "'";
        }
        $dataReturn .= ')';
        echo str_replace("\n", "", $dataReturn);
        die();
    }
}
