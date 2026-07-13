<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Hexamarvel\FlexibleForm\Helper\Data as CustomRouteHelper;
use Hexamarvel\FlexibleForm\Model\ResourceModel\Form\CollectionFactory as FormFactory;
use Magento\Store\Model\StoreManagerInterface;

class Router implements RouterInterface
{
    /**
     * @var bool
     */
    private $dispatched = false;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var CustomRouteHelper
     */
    protected $helper;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param EventManagerInterface $eventManager
     * @param CustomRouteHelper $helper
     * @param FormFactory $formFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ActionFactory $actionFactory,
        EventManagerInterface $eventManager,
        CustomRouteHelper $helper,
        FormFactory $formFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->actionFactory  = $actionFactory;
        $this->eventManager   = $eventManager;
        $this->helper         = $helper;
        $this->formFactory    = $formFactory;
        $this->_storeManager  = $storeManager;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        if (!$this->dispatched) {
            if ($this->helper->isEnabled()) {
                $identifier = trim($request->getPathInfo(), '/');
                $this->eventManager->dispatch('core_controller_router_match_before', [
                    'router' => $this,
                    'condition' => new DataObject(['identifier' => $identifier, 'continue' => true])
                ]);
                $pathInfo = explode('/', $identifier);
                $form = $this->formFactory->create()->addFieldToFilter(
                    'url_key',
                    $pathInfo[0]
                )->addFieldToFilter(
                    'store',
                    [
                        ['eq' => 0],
                        ['finset' => $this->_storeManager->getStore()->getId()]
                    ]
                )->getFirstItem();

                if ($form->getId() && $form->getIsActive()) {
                    $request->setModuleName('hexaform')
                        ->setControllerName('index')
                        ->setActionName('form')
                        ->setParam('form_object', $form);
                    ;
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                    $this->dispatched = true;

                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward'
                    );
                }
            }

            return null;
        }
    }
}
