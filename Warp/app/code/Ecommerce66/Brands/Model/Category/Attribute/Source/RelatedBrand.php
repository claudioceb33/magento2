<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Model\Category\Attribute\Source;

use Ecommerce66\Brands\Helper\Data;

class RelatedBrand extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var array
     */
    protected $options;

    /**
     * RelatedBrand constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllOptions()
    {
        if ($this->options === null) {
            $options = $this->helperData->getBrandOptions();
            foreach ($options as $key => $value) {
                $this->options[] = ['value' => $key, 'label' => $value];
            }
        }

        return $this->options;
    }
}
