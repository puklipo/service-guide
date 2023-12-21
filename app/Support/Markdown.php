<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Markdown
{
    /**
     * Parse the given Markdown text into HTML.
     */
    public static function parse(string $text, array $options = []): HtmlString
    {
        $config = array_merge([
            'html_input' => 'allow',
            'renderer' => [
                'soft_break'      => "<br>\n",
            ],
            'allow_unsafe_links' => false,
            'disallowed_raw_html' => [
                'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'noembed', 'noframes', 'plaintext'],
            ],
        ], $options);

        return new HtmlString(Str::markdown($text, $config));
    }

    /**
     * Parse the given Markdown text into HTML.
     */
    public static function escape(string $text, array $options = []): HtmlString
    {
        $config = array_merge([
            'html_input' => 'escape',
            'renderer' => [
                'soft_break'      => "<br>\n",
            ],
            'allow_unsafe_links' => false,
        ], $options);

        return new HtmlString(Str::markdown($text, $config));
    }
}
