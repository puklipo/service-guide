<?php

namespace App\Support\Markdown\Extension;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

/**
 * BladeComponentsExtension は、Markdownの中で特定のBladeコンポーネントを
 * 使用できるようにするためのExtensionです。
 */
class BladeComponentsExtension implements ConfigurableExtensionInterface
{
    /**
     * Configure the extension.
     */
    public function configureSchema(ConfigurationBuilderInterface $builder): void
    {
        $builder->addSchema('blade_components',
            Expect::structure([
                'allowed_components' => Expect::arrayOf('string')->default([
                    'chart.bar',
                    'chart.line',
                    'chart.pie',
                ]),
            ]),
        );
    }

    /**
     * Register the extension with the environment.
     */
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // デバッグログ
        info('BladeComponentsExtension: register method called');

        // FencedCodeノードのレンダラーとして専用のレンダラークラスを登録
        $environment->addRenderer(
            FencedCode::class,
            new BladeComponentsRenderer(),
            10
        );
    }
}
