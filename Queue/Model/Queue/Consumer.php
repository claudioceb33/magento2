<?php
namespace Ceb\Queue\Model\Queue;

use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Consumer
{

    /**
     * @var NotifierInterface
     */
    protected $notifier;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var string
     */
    protected $type = null;

     /**
     * @param NotifierInterface $notifier
     * @param Json $json
     * @param Process $process
     * 
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        NotifierInterface $notifier,
        Json $json
    ) {
        $this->notifier = $notifier;
        $this->json = $json;
    }

    /**
     * @param string $orderSearchResult
     */
    public function process($orderSearchResult)
    {
        try{
            $this->execute($orderSearchResult);
        }catch (\Exception $e)
        {
            $errorCode = $e->getCode();
            $message = __('Ocurrio un error al agregar las ordenes a la queue.');
            $this->notifier->addCritical(
                $errorCode,
                $message
            );
        }
    }

    /**
     * @param $orderItems
     *
     * @throws LocalizedException
     */
    public function execute($orderItems)
    {
        $orderItems = $this->json->unserialize($orderItems);
        if(is_array($orderItems))
        {
            foreach ($orderItems as $type => $orderIds)
            {
                foreach($orderIds as $orderId)
                {
                    $this->processOrder($orderId);
                }
                $this->type = $type;
            }
        }
    }

    public function processOrder($orderId)
    {
        return true;
    }
}

