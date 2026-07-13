<?php

namespace Swissup\SeoCore\Model;

class Url
{
    /**
     * Rebuild URL; append new parts
     *
     * @param  string $url
     * @param  array  $newParts
     * @return string
     */
    public function rebuild($url, array $newParts)
    {
        $query = $this->getQuery($url);
        $ext = $this->getExtension($url);
        $body = rtrim($this->getBody($url, true), '/'); // remove trailing slash
        if (empty($ext)) {
            // remove 'index'
            $body = preg_replace('/(|\/index)$/', '', $body);
        }

        return $body
            . ( empty($newParts) ? ''  : ('/' . implode('/', $newParts)) )
            . ( empty($ext)      ? '/' : ('.' . $ext) )
            . ( empty($query)    ? ''  : ('?' . $query) );
    }

    /**
     * Get URL extension
     *
     * @param  string &$url
     * @return string
     */
    public function getExtension($url)
    {
        $parts = parse_url($url);
        if ($parts === false) {
            $parts = explode('?', $url);
            $parts = [
                'path' => reset($parts)
            ];
        }
        $parts = explode('.', $parts['path']);
        $extansion = array_pop($parts);
        if (in_array($extansion, ['html', 'htm'])) {
            return $extansion;
        }

        return '';
    }

    /**
     * Get URL query
     *
     * @param  string &$url
     * @return array
     */
    public function getQuery($url)
    {
        $parts = parse_url($url);
        if ($parts === false) {
            $parts = explode('?', $url);
            $queryStr = count($parts) > 1 ? array_pop($parts) : '';
            $parts['query'] = [];
            parse_str($queryStr, $parts['query']);
        }

        return $parts['query'] ?? [];
    }

    /**
     * Get URL body (optionaly without extension)
     *
     * @param  string $url
     * @param  boolean $withoutExt [description]
     * @return string
     */
    public function getBody($url, $withoutExt = false)
    {
        $parts = parse_url($url);
        if ($parts === false) {
            $parts = explode('?', $url);
            $parts = [
                'path' => reset($parts)
            ];
        }
        if ($withoutExt && isset($parts['path'])) {
            if ($ext = $this->getExtension($parts['path'])) {
                $ext = '.' . $ext;
                $parts['path'] = $this->removeLatsOccurence($ext, $parts['path']);
            }
        }

        return ( isset($parts['scheme']) ? $parts['scheme'] . '://' : '' )
            . ( isset($parts['host']) ? $parts['host'] : '' )
            . ( isset($parts['path']) ? $parts['path'] : '' );
    }

    /**
     * Remove last occurance of $search in $subject
     * ( insipired by https://stackoverflow.com/a/3835653 )
     *
     * @param  string $search
     * @param  string $subject
     * @return string
     */
    private function removeLatsOccurence($search, $subject)
    {
        $pos = strrpos($subject, $search);
        if($pos !== false)
        {
            $subject = substr_replace($subject, '', $pos, strlen($search));
        }

        return $subject;
    }
}
