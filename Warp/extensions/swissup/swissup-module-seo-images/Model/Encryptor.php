<?php

namespace Swissup\SeoImages\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Math\Random;
use Swissup\SeoImages\Model\ParamsFactory;

class Encryptor extends \Magento\Framework\Encryption\Encryptor
{
    /**
     * Default values for misc parameters
     *
     * @var array
     */
    private $defaults = [
        'rgb255,255,255',
        'r:empty',
        'q:80',
        'proportional',
        'frame',
        'transparency',
        'doconstrainonly'
    ];

    /**
     * @var Swissup\SeoImages\Helper\Data
     */
    private $helper;

    /**
     * @var ParamsFactory
     */
    private $paramsFactory;

    /**
     * @var array
     */
    private $params_id = [];

    /**
     * @param \Swissup\SeoImages\Helper\Data                  $helper
     * @param ParamsFactory                                   $paramsFactory
     * @param Random                                          $random
     * @param DeploymentConfig                                $deploymentConfig
     */
    public function __construct(
        \Swissup\SeoImages\Helper\Data $helper,
        ParamsFactory $paramsFactory,
        Random $random,
        DeploymentConfig $deploymentConfig
    ) {
        $this->helper = $helper;
        $this->paramsFactory = $paramsFactory;
        parent::__construct($random, $deploymentConfig);
    }

    /**
     * Convert image misc parameters into string.
     * Result is subdir name where resized images are placed.
     *
     * @param  array $data
     * @param  int   $version
     * @return string
     */
    public function hash(
        $data,
        $version = \Magento\Framework\Encryption\Encryptor::HASH_VERSION_SHA256
    ) {
        if (!$this->helper->isEnabled() || !$this->helper->isClearParams()) {
            return parent::hash($data, $version);
        }

        $options = [
            'additional_params' => '',
            'size' => 'x'
        ];

        foreach (explode('_', $data) as $item) {
            if (strpos($item, 'w:') === 0) {
                // width
                $options['size'] = substr($item, 2) . $options['size'];
            } elseif (strpos($item, 'h:') === 0) {
                // height
                $options['size'] .= substr($item, 2);
            } elseif (!in_array($item, $this->defaults)) {
                $options['additional_params'] .= '_' . $item;
            }
        }

        if ($options['size'] === 'emptyxempty') {
            $options['size'] = 'original';
        }

        $options['additional_params'] = $this->getParamsId($options['additional_params']);
        if (empty($options['additional_params'])) {
            unset($options['additional_params']);
        }

        return implode(DIRECTORY_SEPARATOR, $options);
    }

    /**
     * Get id for params set
     *
     * @param  string $paramsString
     * @return string
     */
    private function getParamsId($paramsString)
    {
        if (!isset($this->params_id[$paramsString])) {
            // Params field in DB table has max length 255
            $shortenedParams = ltrim($paramsString, '_');
            $shortenedParams = substr($shortenedParams, 0, 255);
            $id = '';
            if ($shortenedParams) {
                $params = $this->paramsFactory->create()->load(
                    $shortenedParams,
                    'params'
                );
                if (!$params->getId()) {
                    $params->setParams($shortenedParams)->save();
                }

                $id = base_convert($params->getId(), 10, 36);
            }

            $this->params_id[$paramsString] = $id;
        }

        return $this->params_id[$paramsString];
    }
}
