<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Ui\Component\Form\Element;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\Request\Http;

class FormIdValue extends \Magento\Ui\Component\Form\Element\Input
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @param ContextInterface $context
     * @param Http $request
     * @param array $components = []
     * @param array $data = []
     */
    public function __construct(
        ContextInterface $context,
        Http $request,
        array $components = [],
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct($context, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $config = $this->getData('config');

        if (isset($config['dataScope']) && $config['dataScope']=='form_id') {
            $config['default']= $this->request->getParam('form_id');
            $this->setData('config', (array)$config);
        }
    }
}
