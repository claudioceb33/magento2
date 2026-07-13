<?php
namespace Ecommerce66\Brands\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Ecommerce66\Brands\Helper\Data as RouteHelper;

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
     * @var RouteHelper
     */
    protected $helper;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param EventManagerInterface $eventManager
     * @param RouteHelper $helper
     */
    public function __construct(
        ActionFactory $actionFactory,
        EventManagerInterface $eventManager,
        RouteHelper $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        if (!$this->dispatched) {
            $identifier = trim($request->getPathInfo(), '/');
            $this->eventManager->dispatch('core_controller_router_match_before', [
                'router' => $this,
                'condition' => new DataObject(['identifier' => $identifier, 'continue' => true])
            ]);

            // get brand routes for brands and landing
            $routes = $this->helper->getModuleRoutes();
            // get url paths, first one is the frontend name for controller
            $paths = explode('/', $identifier);
            // get the frontend name
            $frontend = reset($paths);
            // var_dump($routes);var_dump($frontend);die;
            // if !empty && frontend matches with config names for brands or landing
            if (!empty($routes) && isset($routes[$frontend])) {
                // todo: redirect to category if brands/something, load catalog/category/view/id/[related_brand=>cat_id]
                // set the path as param
                unset($paths[0]);
                $request->setParam('path', implode('/', $paths));
                // set the module-controller-action for this request
                $request->setModuleName('brands')
                    ->setControllerName($routes[$frontend])
                    ->setActionName('view');
                // set the path alias
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $frontend);
                // dispatch the page
                $this->dispatched = true;
                return $this->actionFactory->create(
                    \Magento\Framework\App\Action\Forward::class
                );
            }

            return null;
        }
    }
}
