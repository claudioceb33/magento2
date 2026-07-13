<?php

namespace Swissup\RichSnippets\Model;

interface DataSnippetInterface
{
    /**
     * Return data snippet
     *
     * @return array|string
     */
    public function get();
}
