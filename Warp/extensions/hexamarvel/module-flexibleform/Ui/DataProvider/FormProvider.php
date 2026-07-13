<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\DataProvider;

use Hexamarvel\FlexibleForm\Model\ResourceModel\Form\CollectionFactory;

class FormProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $categoryFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categoryFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $categoryFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $items = $this->collection->getItems();
        $data = [];
        foreach ($items as $category) {
            $data[$category->getId()]['form_configuration'] = [
                'id' => $category->getData('id'),
                'title' => $category->getData('title'),
                'url_key' => $category->getData('url_key'),
                'is_active' => $category->getData('is_active'),
                'top_content' => $category->getData('top_content'),
                'bottom_content' => $category->getData('bottom_content'),
                'success_message' => $category->getData('success_message'),
                'failure_message' => $category->getData('failure_message'),
                'submit_button' => $category->getData('submit_button'),
            ];

            $data[$category->getId()]['genral_settings'] = [
                'redirect_url' => $category->getData('redirect_url'),
                'captcha' => $category->getData('captcha'),
                'store' => explode(',', $category->getData('store')),
            ];

            $data[$category->getId()]['email_settings'] = [
                'admin_email_active' => $category->getData('admin_email_active'),
                'admin_email' => $category->getData('admin_email'),
                'customer_email_active' => $category->getData('customer_email_active'),
                'customer_to_email' => $category->getData('customer_to_email'),
                'email_field' => $category->getData('email_field'),
            ];
        }

        if (!empty($data)) {
            return $data;
        }
    }
}
