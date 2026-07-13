<?php
namespace Swissup\RichSnippets\Plugin;

use Magento\Theme\Block\Html\Breadcrumbs as Subject;

class Breadcrumbs
{
    /**
     * List of breadcrumbs
     *
     * @var array
     */
    protected $crumbs = [];

    /**
     * Before addCrumbs method to save crumbs
     *
     * @param  Subject $subject
     * @param  string $crumbName
     * @param  array $crumbInfo
     * @return null
     */
    public function beforeAddCrumb(Subject $subject, $crumbName, $crumbInfo)
    {
        if (!isset($this->crumbs[$crumbName])) {
            $this->crumbs[$crumbName] = $crumbInfo;
        }

        return null;
    }

    /**
     * Get breadcrumbs
     *
     * @return array
     */
    public function getCrumbs()
    {
        return $this->crumbs;
    }
}
