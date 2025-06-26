<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Markdown
{
    /**
     * Parse the given Markdown text into HTML.
     * Only use for trusted input, so HTML is allowed.
     */
    public static function parse(string $text, array $options = [], array $extensions = []): HtmlString
    {
        $config = array_merge([
            'html_input' => 'allow',
            'renderer' => [
                'soft_break' => "<br>\n",
            ],
            'allow_unsafe_links' => false,
            'disallowed_raw_html' => [
                'disallowed_tags' => ['title', 'textarea', 'style', 'xmp', 'noembed', 'noframes', 'plaintext'],
            ],
        ], $options);

        return new HtmlString(Str::markdown($text, $config, $extensions));
    }

    /**
     * Parse the given Markdown text into HTML.
     * Escape all input as it is used for user-provided content.
     */
    public static function escape(string $text, array $options = [], array $extensions = []): HtmlString
    {
        $config = array_merge([
            'html_input' => 'escape',
            'renderer' => [
                'soft_break' => "<br>\n",
            ],
            'allow_unsafe_links' => false,
        ], $options);

        return new HtmlString(Str::markdown($text, $config, $extensions));
    }
}
