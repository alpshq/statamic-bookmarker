<?php

namespace Alps\Bookmarker\Tags;

use Statamic\Tags\Tags;

class BookmarkerScripts extends Tags
{
    /**
     * The {{ bookmarker_scripts }} tag.
     */
    public function index()
    {
        $scriptSource = '/vendor/statamic-bookmarker/js/bookmarker.js?' . time();

        return "<script src=\"${scriptSource}\" defer async></script>";
    }
}
