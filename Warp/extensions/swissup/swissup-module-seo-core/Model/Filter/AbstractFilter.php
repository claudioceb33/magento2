<?php

namespace Swissup\SeoCore\Model\Filter;

abstract class AbstractFilter
{
    /**
     * Cunstruction regular expression
     */
    protected $constructionPattern = '/{{([a-z,A-Z,0-9]{0,30})(.*?)}}/si';

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $scope;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->filterManager = $filterManager;
    }

    /**
     * Set scopre for filter
     *
     * @param \Magento\Framework\Model\AbstractModel $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Get scope
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (preg_match_all($this->constructionPattern, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $replacedValue = '';
                $callback = [$this, $construction[1].'Directive'];
                if (is_callable($callback)) {
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }

                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }

        return $value;
    }

    /**
     * Retrieve directive parameters
     *
     * @param mixed $value
     * @return array
     */
    protected function _getIncludeParameters($value)
    {
        $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Parameter();
        $tokenizer->setString($value);
        return $tokenizer->tokenize();
    }

    /**
     * Get attribute frontend value from $this->scope
     *
     * @param  string $attributeCode
     * @return string
     */
    protected function _getAttributeValue($attributeCode)
    {
        $scope = $this->getScope();
        $typeInstance = $scope->getTypeInstance();
        if ($typeInstance && method_exists($typeInstance, 'getConfigurableAttributes')) {
            // search for attribute values in simple products of configurable product
            foreach ($typeInstance->getConfigurableAttributes($scope) as $attribute) {
                if ($attributeCode == $attribute->getProductAttribute()->getAttributeCode()) {
                    $values = $attribute->getOptions() ?: [];
                    $result = [];
                    foreach ($values as $value) {
                        $result[] = $value['store_label'];
                    }

                    return $result;
                }
            }
        }

        $attribute = $scope->getResource()->getAttribute($attributeCode);
        if ($attribute && $scope->getData($attributeCode)) {
            $attribute->setStoreId($scope->getStoreId());
            $value = $attribute->getFrontend()->getValue($scope);
            if ($attribute->getBackendType() == 'decimal') {
                $value = (float)$value;
            } elseif ($attribute->getIsHtmlAllowedOnFront()) {
                $value = $this->filterManager->removeTags($value);
                // make string into one line
                $value = str_replace(["\r\n", "\r","\n"], ' ', $value);
            }

            return $value;
        }

        return '';
    }

    /**
     * Process directive result before output
     *
     * @param  array|string $result [description]
     * @param  array $params [description]
     * @return string
     */
    protected function _postprocessResult($result, $paramsArray)
    {
        $output = '';
        $params = new \Magento\Framework\DataObject($paramsArray);
        if ($result) {
            if (is_array($result)) {
                if ($params->hasExclude()) {
                    // exclude values
                    $exclude = explode(",", $params->getExclude());
                    $exclude = array_map('trim', $exclude);
                    $result = array_diff($result, $exclude);
                }

                if ($limit = (int)$params->getLimit()) {
                    $result = array_slice($result, 0, $limit, true);
                }

                $separator = $params->hasSeparator() ? $params->getSeparator() : ', ';
                $output = implode($separator, $result);
            } else {
                $output = $result;
            }

            if ($maxLength = (int)$params->getMaxLength()) {
                // cut the rest of string when max_length is set
                $output = $this->filterManager->truncate(
                    $output,
                    [
                        'length' => $maxLength,
                        'breakWords' => (bool)$params->getBreakWords(),
                        'etc' => $params->getEtc()
                    ]
                );
            }

            $output = $params->getPrefix() . $output . $params->getSufix();
        }

        return $output;
    }

    /**
     * Attribute directive
     *
     * @param  array $construction
     * @return string
     */
    public function attributeDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $attributeCode = isset($params['code']) ? $params['code'] : '';
        // attribute code can containe multiple attributes separated by comma
        $attributeCodes = array_map('trim', explode(",",$attributeCode));
        foreach ($attributeCodes as $code) {
            $attributeValue = $this->_getAttributeValue($code);
            if ($attributeValue) {
                break;
            }
        }

        return $this->_postprocessResult($attributeValue, $params);
    }

    /**
     * If exist directive
     *
     * @param  array $construction
     * @return string
     */
    public function ifexistDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $attributeCode = isset($params['attribute']) ? $params['attribute'] : '';
        $then = isset($params['then']) ? $params['then'] : '';
        $else = isset($params['else']) ? $params['else'] : '';
        // attribute code can containe multiple attributes separated by comma
        $attributeCodes = array_map('trim', explode(",",$attributeCode));
        foreach ($attributeCodes as $code) {
            $attributeValue = $this->_getAttributeValue($code);
            if ($attributeValue) {
                break;
            }
        }

        if ($attributeValue) {
            return $this->filter($then);
        }

        return $this->filter($else);
    }
}
