<?php

namespace App\Support\Markdown\Extension;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\ConfigurableExtensionInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationBuilderInterface;
use Nette\Schema\Expect;

/**
 * BladeComponentsExtension は、Markdownの中で特定のBladeコンポーネントを
 * 使用できるようにするためのExtensionです。
 */
class BladeComponentsExtension implements ConfigurableExtensionInterface, NodeRendererInterface
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

        // NodeRendererInterfaceを実装したクラスとして自分自身をFencedCodeのレンダラーとして登録
        $environment->addRenderer(FencedCode::class, $this, 10);
    }

    /**
     * NodeRendererInterfaceを実装したrender()メソッド
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable
    {
        info('BladeComponentsExtension: render method called', [
            'node_type' => get_class($node),
        ]);

        // CommonMarkバージョン2.x以上では処理できない場合はデフォルトのレンダラーに任せる
        if (!($node instanceof FencedCode)) {
            // childRenderer->renderNodesメソッドの戻り値をStringableに変換することを保証
            $result = $childRenderer->renderNodes([$node]);
            // すでにStringableの場合はそのまま返す
            if ($result instanceof \Stringable) {
                return $result;
            }
            // それ以外の場合は文字列としてラップして返す
            return new class((string)$result) implements \Stringable {
                private string $content;

                public function __construct(string $content)
                {
                    $this->content = $content;
                }

                public function __toString(): string
                {
                    return $this->content;
                }
            };
        }

        $info = $node->getInfo();
        $content = $node->getLiteral();

        info('BladeComponentsExtension: processing fenced code block', [
            'info' => $info,
            'content_length' => strlen($content),
        ]);

        // bladeという言語タグが指定されているか確認
        if ($info !== 'blade') {
            // blade以外の言語指定は処理しない（デフォルトのレンダラーに任せる）
            $result = $childRenderer->renderNodes([$node]);
            // すでにStringableの場合はそのまま返す
            if ($result instanceof \Stringable) {
                return $result;
            }
            // それ以外の場合は文字列としてラップして返す
            return new class((string)$result) implements \Stringable {
                private string $content;

                public function __construct(string $content)
                {
                    $this->content = $content;
                }

                public function __toString(): string
                {
                    return $this->content;
                }
            };
        }

        // Bladeコンポーネントとして処理
        $result = $this->processBlade($content);

        // HTML出力を直接返すための特殊なStringableオブジェクト
        // <pre><code>タグでラップされないようにするため
        return new class($result) implements \Stringable {
            private $content;

            public function __construct($content)
            {
                $this->content = $content;
            }

            public function __toString(): string
            {
                // HtmlStringの場合はtoHtmlメソッドを呼び出す
                if ($this->content instanceof \Illuminate\Support\HtmlString) {
                    return $this->content->toHtml();
                }

                // content自体がnullの場合は空文字を返す
                if ($this->content === null) {
                    return '';
                }

                // それ以外の場合は文字列に変換して返す
                return (string) $this->content;
            }
        };
    }

    /**
     * Bladeコンポーネントとしてコンテンツを処理
     */
    private function processBlade(string $content): HtmlString|string|null
    {
        info('BladeComponentsExtension: processBlade called', [
            'content' => $content,
        ]);

        // コンポーネント名を抽出 (マルチラインモードでドットが改行にもマッチするように)
        // コンポーネント名は<x-で始まる必要があり、Alpine.jsのx-data属性などと区別する
        if (! preg_match('/(?:<x-)([a-z0-9_\-\.]+)(?:\s|\n|>).*?(?:\/?>|><\/x-[a-z0-9_\-\.]+>)/is', $content, $matches)) {
            // コンポーネントタグが見つからない
            info('BladeComponentsExtension: component tag not found');
            if (config('app.debug')) {
                return '<pre class="debug-info">コンポーネントタグが見つかりません: ' . e($content) . '</pre>';
            }
            return null;
        }

        // 抽出されたコンポーネント名
        $componentName = $matches[1];

        // コンポーネント名の正規化（常に.形式に変換）
        $normalizedName = str_replace('::', '.', $componentName);

        info('BladeComponentsExtension: component name extracted', [
            'componentName' => $componentName,
            'normalizedName' => $normalizedName,
        ]);

        // 許可されたコンポーネントかチェック
        $allowedComponents = config('markdown.blade_components.allowed_components', [
            'chart.bar',
            'chart.line',
            'chart.pie',
        ]);

        // 許可リストの正規化（常に.形式に変換）
        $normalizedAllowedComponents = array_map(function ($name) {
            return str_replace('::', '.', $name);
        }, $allowedComponents);

        // 正規化した名前で比較
        $isAllowed = in_array($normalizedName, $normalizedAllowedComponents, true);

        info('BladeComponentsExtension: component permission check', [
            'componentName' => $componentName,
            'normalizedName' => $normalizedName,
            'allowedComponents' => $allowedComponents,
            'isAllowed' => $isAllowed,
        ]);

        if (!$isAllowed) {
            // デバッグ情報
            info('BladeComponentsExtension: component not allowed', [
                'componentName' => $componentName,
            ]);

            // 許可されていないコンポーネントの場合は、テスト期待値と一致するようにコードブロックとして表示
            // デバッグ表示はテスト時に問題を引き起こすので、テストの期待値に合わせて常に同じフォーマットを使用
            return '<pre><code class="language-blade">' . e($content) . '</code></pre>';
        }

        try {
            // コンポーネント名を常に.形式に変換
            $modifiedContent = $content;
            if (strpos($componentName, '::') !== false) {
                // ::形式を.形式に変換
                $modifiedContent = str_replace('<x-' . $componentName, '<x-' . $normalizedName, $content);
            }

            info('BladeComponentsExtension: rendering blade component', [
                'modifiedContent' => $modifiedContent,
            ]);

            // Bladeコンポーネントをレンダリング（エスケープ防止のためにraw()を使用）
            $renderedHtml = Blade::render($modifiedContent);

            info('BladeComponentsExtension: blade rendering successful', [
                'renderedHtml_length' => strlen($renderedHtml),
            ]);

            // 重要: rawコンテンツとして明示的に返す
            return new \Illuminate\Support\HtmlString($renderedHtml);
        } catch (\Throwable $e) {
            // エラーが発生した場合はエラーメッセージを表示
            info('BladeComponentsExtension: blade rendering error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (config('app.debug')) {
                return '<div class="error p-4 bg-red-100 border border-red-300 rounded">レンダリングエラー: '.e($e->getMessage()).'</div>';
            }

            // 本番環境ではシンプルなエラーメッセージを表示
            return '<div class="error p-4 bg-red-100 border border-red-300 rounded">グラフの表示に失敗しました</div>';
        }
    }
}
