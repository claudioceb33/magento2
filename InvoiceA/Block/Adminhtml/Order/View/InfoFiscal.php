<?php
namespace Ceb\InvoiceA\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\OrderRepository;

/**
 * Class InfoFiscal
 *
 * @package Ceb\InvoiceA\Block
 */
class InfoFiscal extends Template
{

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;

    /**
     * @var
     */
    protected $data;

    /**
     * Constructor
     *
     * @param Context $context
     * @param OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderRepository $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_orderRepository = $orderRepository;
    }

    /**
     * Prepare information to show
     *
     */
    public function getInfoFiscal()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        /* @var $order \Magento\Sales\Api\Data\OrderInterface */
        $order = $this->_orderRepository->get($orderId);

        $emitirFactA = $order->getCustomerTaxSituation() === '1'?'Si':'No';
        $this->setDataContent('Emitir Factura A', $emitirFactA,'No');

        $this->setDataContent('Empresa / Razón Social',$order->getCustomerCompany());

        $this->setDataContent('CUIT',$order->getCustomerCuit());

        return $this->data;
    }

    protected function setDataContent($_title, $value, $default = null)
    {
        if ($value != '') {
            $title = __($_title);
            $this->data[$title->__toString()] = $value;
        }

        if (($value === null || $value == '') && $default !== null) {
            $title = __($_title);
            $this->data[$title->__toString()] = $default;
        }
    }
}
