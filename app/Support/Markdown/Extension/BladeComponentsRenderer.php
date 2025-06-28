<?php

namespace App\Support\Markdown\Extension;

use Illuminate\Support\Facades\Blade;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

/**
 * BladeComponentsRenderer は、Markdown内のbladeコードブロックを
 * Bladeコンポーネントとしてレンダリングするためのレンダラーです。
 */
class BladeComponentsRenderer implements ConfigurationAwareInterface, NodeRendererInterface
{
    /**
     * 許可されたコンポーネントのキャッシュ
     */
    private ?array $allowedComponents = null;

    /**
     * 設定オブジェクト
     */
    private ?ConfigurationInterface $config = null;

    /**
     * 設定を設定する
     */
    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    /**
     * NodeRendererInterfaceを実装したrender()メソッド
     *
     * @return \Stringable|string|null
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        // CommonMarkバージョン2.x以上では処理できない場合はデフォルトのレンダラーに任せる
        if (! ($node instanceof FencedCode)) {
            return $childRenderer->renderNodes([$node]);
        }

        $info = $node->getInfo();
        $content = $node->getLiteral();

        // bladeという言語タグが指定されているか確認
        if ($info !== 'blade') {
            // blade以外の言語指定は処理しない（デフォルトのレンダラーに任せる）
            return $childRenderer->renderNodes([$node]);
        }

        // Bladeコンポーネントをレンダリングして結果を直接返す
        return $this->processBlade($content);
    }

    /**
     * Bladeコンポーネントとしてコンテンツを処理
     *
     * @param  string  $content  bladeタグを含むコンテンツ
     * @return string|null レンダリング結果のHTML
     */
    private function processBlade(string $content): ?string
    {
        // コンテンツにコンポーネントタグが含まれているか確認
        if (! preg_match('/<x-([a-z0-9_\-\.]+).*?(?:\/?>|><\/x-[a-z0-9_\-\.]+>)/is', $content, $matches)) {
            // コンポーネントタグが見つからない
            if (config('app.debug')) {
                return '<div class="debug-info p-4 bg-yellow-100 border border-yellow-300 rounded">コンポーネントタグが見つかりません: '.e($content).'</div>';
            }

            return null;
        }

        // 抽出されたコンポーネント名
        $componentName = $matches[1];

        // 許可されたコンポーネントを取得
        $this->loadAllowedComponents();

        // 許可されたコンポーネントかチェック
        $isAllowed = in_array($componentName, $this->allowedComponents, true);

        if (! $isAllowed) {
            // 許可されていないコンポーネントの場合は、テスト期待値と一致するようにコードブロックとして表示
            return '<pre><code class="language-blade">'.e($content).'</code></pre>';
        }

        try {
            // レンダリングを高速化するためにBladeファサードを直接使用
            return Blade::render($content);
        } catch (\Throwable $e) {
            // エラーが発生した場合はエラーメッセージを表示
            if (config('app.debug')) {
                return '<div class="error p-4 bg-red-100 border border-red-300 rounded">レンダリングエラー: '.e($e->getMessage()).'</div>';
            }

            // 本番環境ではシンプルなエラーメッセージを表示
            return '<div class="error p-4 bg-red-100 border border-red-300 rounded">グラフの表示に失敗しました</div>';
        }
    }

    /**
     * 許可されたコンポーネント一覧を読み込む
     */
    private function loadAllowedComponents(): void
    {
        // 設定オブジェクトからデータを取得
        if ($this->config !== null) {
            try {
                $allowedComponents = $this->config->get('blade_components/allowed_components');
                // 設定から値が取得できた場合はそれを使用
                $this->allowedComponents = $allowedComponents;
            } catch (\Exception $e) {
                // 設定から値が取得できない場合は空の配列を使用
                $this->allowedComponents = [];
            }
        } else {
            // 設定が提供されていない場合は空の配列を使用
            $this->allowedComponents = [];
        }

        // nullの場合は空の配列を使用
        if ($this->allowedComponents === null) {
            $this->allowedComponents = [];
        }
    }
}
