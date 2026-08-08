<?php
namespace Ceb\Queue\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class Publisher
{
    const TOPIC_NAME = 'modulename.queue.order';
    const SIZE = 1000;
    const TYPE = 'orders_id';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSING_SHIPMENT = 'processing_shipment';

    /**
     * @var CollectionFactory
     */
    protected $_orderColFactory;

    /**
     * @var PublisherInterface
     */
    protected $_publisher;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @param CollectionFactory $orderColFactory
     * @param PublisherInterface $publisher
     * @param Json $json
     * @param OrderRepositoryInterface $orderRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CollectionFactory $orderColFactory,
        PublisherInterface $publisher,
        Json $json,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_orderColFactory = $orderColFactory;
        $this->_publisher = $publisher;
        $this->_json = $json;
        $this->_orderRepository = $orderRepository;
    }

    /**
     *
     * @return $this
     */
    public function process()
    {
        try {
            $orderCollection = $this->_orderColFactory->create()
                ->addFieldToSelect('entity_id')
                ->addFieldToFilter('notify_to_sap', ['eq' => 0])
                ->addFieldToFilter('attempts_to_sap', ['in' => [0, 1, 2]])
                ->addFieldToFilter('status', ['in' => [self::STATUS_PROCESSING]])
                ->getAllIds();

            $this->publishData($orderCollection, self::TYPE);

            return $this;
        } catch (\Exception $e) {
            return;
        }
        return $this;
    }

    /**
 	* @param $data
 	* @param $type
 	*/
	public function publishData($data,$type)
	{
    	if(is_array($data)){
        	$chunks = array_chunk($data, self::SIZE);
        	foreach ($chunks as $chunk){
                $rawData = [$type => $chunk];
                $this->_publisher->publish(self::TOPIC_NAME, $this->_json->serialize($rawData));
            }
    	}
	}
}

