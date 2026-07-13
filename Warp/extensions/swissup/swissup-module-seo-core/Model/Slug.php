<?php

namespace Swissup\SeoCore\Model;

use Cocur\Slugify\Slugify;
use Magento\Framework\Locale\Resolver;

class Slug
{
    /**
     * Slugify
     * @var null
     */
    protected $instance = null;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var array
     */
    protected $rulesets = [
        'zh_Hans_CN' => ['default', 'chinese'],
        'zh_Hans_HK' => ['default', 'chinese'],
        'zh_Hans_TW' => ['default', 'chinese'],
    ];

    /**
     * @param Resolver $resolver
     */
    public function __construct(
        Resolver $resolver
    ) {
        $this->resolver = $resolver;
    }

    /**
     * @param  string $string
     * @return string
     */
    public function slugify($string): string
    {
        if (!$string) {
            return '';
        }

        $instance = $this->getInstance();
        $string = (string)$string;

        return $instance ? $instance->slugify($string) : $this->fallback($string);
    }

    /**
     * Get slugify instance.
     *
     * @return Slugify|null
     */
    protected function getInstance()
    {
        if (class_exists(Slugify::class) && !$this->instance) {
            $options = [];
            $locale = $this->resolver->getLocale();
            if (isset($this->rulesets[$locale])) {
                $options['rulesets'] = $this->rulesets[$locale];
            }

            $this->instance = new Slugify($options);
        }

        return $this->instance;
    }

    /**
     * Fallback when slugify is not installed.
     *
     * @param  string $string
     * @return string
     */
    protected function fallback(string $string): string
    {
        // remove leading and trailing spaces
        $string = trim($string);
        // source - https://stackoverflow.com/questions/11330480/strip-php-variable-replace-white-spaces-with-dashes
        // Lower case everything
        $string = strtolower($string);
        // decode html entities to utf8
        $string = html_entity_decode($string);
        // Replace non-breakable space (nbsp) with regular space
        $string = str_replace("\xc2\xa0", ' ', $string);
        // Remove & and .
        $string = preg_replace("/[&.%'\"+?#]/", " ", $string);
        // Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        // Convert whitespaces, underscore and slash to dash
        $string = preg_replace("/[\s_\/]/", "-", $string);

        return $string;
    }
}
