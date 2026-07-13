<?php
namespace Swissup\FieldManager\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Eav\Api\AttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Eav\Api\Data\AttributeInterface;

class Move extends \Magento\Backend\App\Action
{
    const ENTITY_CODE = '';
    const MAPPING = ['region' => 'region_id'];

    /** @var AttributeRepository  */
    protected $attributeRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param AttributeRepository $attributeRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->attributeRepository = $attributeRepository;
        $this->jsonFactory = $jsonFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        try {
            $attribute = $this->getAttribute($this->getRequest()->getParam('item'));

            $after  = $this->getRequest()->getParam('after');
            $before = $this->getRequest()->getParam('before');
            $anchor = $before ? $this->getAttribute($before) : $this->getAttribute($after);
            $order  = $anchor->getSortOrder();

            if (!$before) {
                $order += 10;
            }

            foreach ($this->getAttributesAfter($anchor) as $attr) {
                $this->saveSortOrder($attr, $attr->getSortOrder() + 10);
            }

            $this->saveSortOrder($attribute, $order);

            if ($before) {
                $this->saveSortOrder($anchor, $order + 10);
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param AttributeInterface $attr
     * @param int $sortOrder
     */
    private function saveSortOrder($attr, $sortOrder)
    {
        $attr->setSortOrder($sortOrder)->save();
        if (array_key_exists($attr->getAttributeCode(), self::MAPPING)) {
            $this->getAttribute(self::MAPPING[$attr->getAttributeCode()])
                ->setSortOrder($sortOrder)->save();
        }
    }

    /**
     * @param $attributeCode
     * @return AttributeInterface
     */
    private function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get(static::ENTITY_CODE, $attributeCode);
    }

    /**
     * @param $attribute
     * @return AttributeInterface[]
     */
    private function getAttributesAfter($attribute)
    {
        $this->searchCriteriaBuilder
            ->addFilter('additional_table.sort_order', $attribute->getSortOrder(), 'gt');

        return $this->attributeRepository
            ->getList(static::ENTITY_CODE, $this->searchCriteriaBuilder->create())
            ->getItems();
    }
}
