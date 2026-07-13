<?php

namespace Swissup\AddressFieldManager\Model\Address\Validator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\NotEmptyFactory;
use Swissup\AddressFieldManager\Model\ResourceModel\Customer\Form\AddressAttribute\CollectionFactory;

class General implements ValidatorInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryData = null;

    /**
     * @var NotEmpty
     */
    private $notEmpty;

    /**
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param NotEmptyFactory $notEmptyFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Directory\Helper\Data $directoryData,
        NotEmptyFactory $notEmptyFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->directoryData = $directoryData;
        $this->notEmpty = $notEmptyFactory->create(['options' => NotEmpty::ALL]);
    }

    public function validate(AbstractAddress $address)
    {
        $errors = [];

        $collection = $this->collectionFactory->create();
        foreach ($collection as $attribute) {
            if (!$attribute->getIsRequired() || !$attribute->getIsVisible()) {
                continue;
            }

            $code  = $attribute->getAttributeCode();
            $value = $address->getData($code);
            if ('street' === $attribute->getAttributeCode()) {
                $value = $address->getStreetLine(1);
            }

            $isValid = $this->notEmpty->isValid($value);
            if ($attribute->getFrontendInput() === 'boolean') {
                if (is_array($value)) {
                    $value = $value['value'] ?? 0;
                }
                $isValid = $value !== null;
            }

            if (!$isValid) {
                $errors[] = __('%fieldName is a required field.', [
                    'fieldName' => $code
                ]);
            }
        }

        $havingOptionalZip = $this->directoryData->getCountriesWithOptionalZip();
        if (!in_array($address->getCountryId(), $havingOptionalZip)
            && !$this->notEmpty->isValid($address->getPostcode())
        ) {
            $errors[] = __('%fieldName is a required field.', [
                'fieldName' => 'postcode'
            ]);
        }

        return $errors;
    }
}
