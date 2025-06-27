<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use App\Support\Markdown\Extension\BladeComponentsExtension;

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
            'blade_components' => [
                'allowed_components' => config('markdown.blade_components.allowed_components', [
                    'chart.bar',
                    'chart.line',
                    'chart.pie',
                ]),
            ],
        ], $options);

        $extensions = array_merge($extensions, [
            new FrontMatterExtension,
            new BladeComponentsExtension,
        ]);

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

    /**
     * Parse the front matter from a Markdown file.
     *
     * @param  string  $file  The path to the Markdown file.
     * @return array|null The front matter data, or null if not found.
     */
    public static function matter(string $file): ?array
    {
        $frontMatterParser = new FrontMatterParser(new SymfonyYamlFrontMatterParser);
        $md = $frontMatterParser->parse(File::get($file));

        return $md->getFrontMatter();
    }
}
