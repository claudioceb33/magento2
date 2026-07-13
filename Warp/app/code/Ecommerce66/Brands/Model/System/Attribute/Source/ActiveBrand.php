<?php
declare(strict_types=1);

namespace Ecommerce66\Brands\Model\System\Attribute\Source;

use Ecommerce66\Brands\Helper\Data;

class ActiveBrand extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
     * return array|array[]
     */
    public function getAllOptions()
    {
        if ($this->options === null) {
            $options = $this->helperData->getActiveBrandOptions();
            foreach ($options as $key => $value) {
                $this->options[] = ['value' => $key, 'label' => $value];
            }
        }

        return $this->options;
    }
}
