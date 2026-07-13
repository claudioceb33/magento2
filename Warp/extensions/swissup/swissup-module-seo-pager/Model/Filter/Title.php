<?php

namespace Swissup\SeoPager\Model\Filter;

use Magento\Framework\DataObject;
use Swissup\SeoPager\Model\ToolbarResolver;

class Title
{
    /**
     * Cunstruction regular expression
     */
    protected $constructionPattern = '/{{([a-z,A-Z]{0,30})(.*?)}}/si';

    /**
     * @var DataObject
     */
    protected $scope;

    /**
     * @var ToolbarResolver
     */
    protected $toolbarResolver;

    /**
     * @param ToolbarResolver $toolbarResolver
     */
    public function __construct(
        ToolbarResolver $toolbarResolver
    ) {
        $this->toolbarResolver = $toolbarResolver;
    }

    /**
     * Set scopre for filter
     *
     * @param DataObject $scope
     * @return $this
     */
    public function setScope(DataObject $scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Get scope
     *
     * @return DataObject
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
     * @return string|null
     */
    public function titleDirective()
    {
        return $this->scope->getTitle();
    }

    public function currentPageDirective() :?int
    {
        try {
            return $this->toolbarResolver->getCurrPageNumber();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function lastPageDirective() :?int
    {
        try {
            return $this->toolbarResolver->getLastPageNumber();
        } catch (\Exception $e) {
            return null;
        }
    }
}
