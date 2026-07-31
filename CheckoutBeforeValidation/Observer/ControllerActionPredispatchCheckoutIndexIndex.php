<?php
declare(strict_types=1);
namespace Ceb\CheckoutBeforeValidation\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;

class ControllerActionPredispatchCheckoutIndexInde implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect
    ) {
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
    }

    /**
     * Valida si el carrito tiene producto cuando posee giftcad o envoltorio
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        $controller = $observer->getControllerAction();

        $this->messageManager->addNoticeMessage(__('Es necesario agregar un producto.'));
        $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
    }
}