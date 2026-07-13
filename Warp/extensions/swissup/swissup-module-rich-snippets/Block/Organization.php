<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\RichSnippets\Block;

use Magento\Framework\View\Element\Template;
use Swissup\RichSnippets\Model\Config\Backend\OpeningHours as ConfigValueProcessor;

class Organization extends LdJson
{
    /**
     * @var ConfigValueProcessor
     */
    private $configValueProcessor;

    /**
     * @param ConfigValueProcessor $configValueProcessor
     * @param Template\Context     $context
     * @param array                $data
     */
    public function __construct(
        ConfigValueProcessor $configValueProcessor,
        Template\Context $context,
        array $data = []
    ) {
        $this->configValueProcessor = $configValueProcessor;
        parent::__construct($context, $data);
    }

    private function getConfigDataArray($key)
    {
        $configData = $this->getStoreConfig("richsnippets/{$key}");
        if (!is_array($configData)) {
            return [$key => $configData];
        }

        return $configData;
    }

    /**
     * {@inheritdoc}
     */
    public function getLdJson()
    {
        $configDataOrganization = $this->getConfigDataArray('organization');
        // prepare general organization info
        $keysMap = [
            'type' => '@type',
            'url' => 'url',
            'name' => 'name',
            'phone' => 'telephone',
            'email' => 'email'
        ];
        $ldArray = $this->remapArray($keysMap, $configDataOrganization);

        $ldArray['address'] = $this->getPostalAddress();
        $ldArray['openingHoursSpecification'] = $this->getOpeningHours();
        $ldArray['sameAs'] = $this->getSameAs();
        $ldArray = array_filter($ldArray);

        return empty($ldArray) ?
            '' :
            $this->prepareJsonString(['@context' => 'http://schema.org'] + $ldArray);
    }

    /**
     * Get opening hours data snippet
     *
     * @return array
     */
    public function getOpeningHours()
    {
        $configValue = $this->getStoreConfig('richsnippets/organization/opening_hours');

        $openingHours = array_map(
            function ($hours) {
                $days = explode('|', $hours['day_of_week']);
                return [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => (count($days) == 1 ? reset($days) : $days),
                    'opens' => $hours['opens'],
                    'closes' => $hours['closes']
                ];
            },
            $this->configValueProcessor->makeArrayFieldValue($configValue)
        );

        return array_values($openingHours);
    }

    /**
     * Get postal address data snippet for organization
     *
     * @return array
     */
    public function getPostalAddress(): array
    {
        $configDataOrganization = $this->getConfigDataArray('organization');
        $keysMap = [
            'street' => 'streetAddress',
            'locality' => 'addressLocality',
            'region' => 'addressRegion',
            'postal_code' => 'postalCode',
            'country' => 'addressCountry',
        ];

        $address = $this->remapArray($keysMap, $configDataOrganization);
        $address = array_filter($address);

        return $address ?
            (['@type' => 'PostalAddress'] + $address) :
            [];
    }

    /**
     * Get social data snippet of organization
     *
     * @return array
     */
    public function getSameAs(): array
    {
        $configDataSocial = $this->getConfigDataArray('social');
        $socialsMap = [
            'twitter' => 'https://twitter.com/',
            'facebook' => 'https://www.facebook.com/',
            'googleplus' => 'https://plus.google.com/',
            'linkedin' => 'https://www.linkedin.com/company/',
            'pinterest' => 'https://www.pinterest.com/',
            'instagram' => 'https://instagram.com/',
        ];

        $sameAs = array_map(
            function ($key, $socialBaseUrl) use ($configDataSocial) {
                return empty($configDataSocial[$key]) ?
                    '' :
                    $socialBaseUrl . $configDataSocial[$key];
            },
            array_keys($socialsMap),
            array_values($socialsMap)
        );

        return array_values(array_filter($sameAs));
    }
}
