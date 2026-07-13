<?php

namespace Swissup\RichSnippets\Model\Config\Source;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\HTTP\Client\Curl;

class OrganizationType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @param CacheInterface $cache
     * @param Curl           $curl
     */
    public function __construct(
        CacheInterface $cache,
        Curl $curl
    ) {
        $this->cache = $cache;
        $this->curl = $curl;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $items = [
            ['value' => '', 'label' => __('None')],
            ['value' => 'Store', 'label' => __('Store (general)')]
        ];
        $types = $this->getStoreTypes();

        foreach ($types as $type) {
            $items[] = [
                'value' => $type['rdfs:label'],
                // 'label' => __($type['rdfs:comment'])
                'label' => __(
                    preg_replace(
                        '/(?<=[a-z])[A-Z]|[A-Z](?=[a-z])/',
                        ' $0',
                        $type['rdfs:label']
                    )
                )
            ];
        }

        return $items;
    }

    /**
     * Read store types from Magento cache. When not found send curl request.
     *
     * @return array
     */
    private function getStoreTypes()
    {
        $cacheFile = 'schemaOrgStoreTypes';
        $json = $this->cache->load($cacheFile);
        if ($json === false) {
            $curlData = $this->curlStoreTypes();
            $json = json_encode($curlData);
            $this->cache->save($json, $cacheFile);
        }

        return json_decode($json, true);
    }

    /**
     * Send curl to schema.org
     *
     * @return array
     */
    private function curlStoreTypes()
    {
        // url taken from https://schema.org/docs/developers.html
        $this->curl->get('https://schema.org/version/latest/schemaorg-current-https.jsonld');
        $json = $this->curl->getBody();
        $decoded = json_decode($json, true);

        return array_filter(($decoded['@graph'] ?? []), function ($item) {
            $subClasOf = $item['rdfs:subClassOf']['@id'] ?? '';

            return $subClasOf == 'schema:Store' || $subClasOf == 'schema:OnlineBusiness';
        });
    }
}
