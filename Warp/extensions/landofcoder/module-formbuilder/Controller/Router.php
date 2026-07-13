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

namespace Lof\Formbuilder\Controller;

use Lof\Formbuilder\Helper\Data;
use Lof\Formbuilder\Model\FormFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var bool
     */
    protected bool $dispatched = false;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;
    /**
     * @var Session
     */
    protected $customerSession;
    protected $response;

    /**
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param FormFactory $formFactory
     * @param StoreManagerInterface $storeManager
     * @param Data $data
     * @param Session $customerSession
     * @param Registry $registry
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        FormFactory $formFactory,
        StoreManagerInterface $storeManager,
        Data $data,
        Session $customerSession,
        Registry $registry
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->formFactory = $formFactory;
        $this->storeManager = $storeManager;
        $this->helper = $data;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $registry;
    }

    /**
     * @param RequestInterface $request
     * @return ActionInterface|void|null
     * @throws NoSuchEntityException|LocalizedException
     */
    public function match( $request)
    {
        if (!$this->dispatched) {
            $identifier = @trim($request->getPathInfo(), '/');
            $origUrlKey = $identifier;
            $route = $this->helper->getConfig('general_settings/route');
            $route = $route ? $route : 'formbuilder';
            $submitRoute = !empty($route) ? ($route . 'SubmitForm') : '';
            $identifiers = explode('/', $identifier);
            if (count($identifiers) >= 2) {
                $identifier = $identifiers[0];
            }

            if ($submitRoute != '' && $submitRoute == $identifiers[0]) {
                $this->dispatched = true;
                $request->setDispatched(true);
                $request->setModuleName('formbuilder')
                    ->setControllerName('form')
                    ->setActionName('post');
                $request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
            } else {
                if ($route != '' && $route != $identifier) {
                    return;
                }

                if (
                    count($identifiers) == 3 && $identifiers[1] == 'view' &&
                    $identifiers[2] && $identifiers[2] == "ajax"
                ) {
                    $this->dispatched = true;
                    $request->setDispatched(true);
                    $request->setModuleName('formbuilder')
                        ->setControllerName('view')
                        ->setActionName('ajax')
                        ->setParam('code', $identifiers[2]);
                    $request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                } elseif (
                    count($identifiers) == 3 && $identifiers[1] == 'view' &&
                    $identifiers[2] && $identifiers[2] !== "scan"
                ) {
                    $this->dispatched = true;
                    $request->setDispatched(true);
                    $request->setModuleName('formbuilder')
                        ->setControllerName('view')
                        ->setActionName('index')
                        ->setParam('code', $identifiers[2]);
                    $request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                } else {
                    if (count($identifiers) == 2) {
                        $identifier = $identifiers[1];
                    }
                    $form = $this->formFactory->create();
                    $formId = $form->checkIdentifier($identifier, $this->storeManager->getStore()->getId());

                    if ($formId) {
                        $this->dispatched = true;
                        $request->setDispatched(true);
                        $request->setModuleName('formbuilder')
                            ->setControllerName('form')
                            ->setActionName('view')
                            ->setParam('form_id', $formId);
                        $request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    } else {
                        return;
                    }
                }
            }
            $condition = new DataObject(
                [
                    'identifier' => $identifier,
                    'request' => $request,
                    'continue' => true
                ]
            );

            $this->eventManager->dispatch(
                'formbuilder_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );

            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $this->dispatched = true;
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    Redirect::class,
                    ['request' => $request]
                );
            }

            if (!$condition->getContinue()) {
                return null;
            }

            if (!$request->getModuleName()) {
                return null;
            }

            $request->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
            return $this->actionFactory->create(
                Forward::class,
                ['request' => $request]
            );
        }
    }
}
