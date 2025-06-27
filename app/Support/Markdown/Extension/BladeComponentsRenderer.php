<?php

namespace App\Support\Markdown\Extension;

use Illuminate\Support\Facades\Blade;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * BladeComponentsRenderer は、Markdown内のbladeコードブロックを
 * Bladeコンポーネントとしてレンダリングするためのレンダラーです。
 */
class BladeComponentsRenderer implements NodeRendererInterface
{
    /**
     * 許可されたコンポーネントのキャッシュ
     *
     * @var array|null
     */
    private ?array $allowedComponents = null;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // 許可されたコンポーネントを設定から読み込む
        $this->allowedComponents = config('markdown.blade_components.allowed_components', [
            'chart.bar',
            'chart.line',
            'chart.pie',
        ]);
    }

    /**
     * NodeRendererInterfaceを実装したrender()メソッド
     *
     * @return \Stringable|string|null
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        // CommonMarkバージョン2.x以上では処理できない場合はデフォルトのレンダラーに任せる
        if (!($node instanceof FencedCode)) {
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
     * @param string $content bladeタグを含むコンテンツ
     * @return string|null レンダリング結果のHTML
     */
    private function processBlade(string $content): ?string
    {
        // コンテンツにコンポーネントタグが含まれているか確認
        if (! preg_match('/<x-([a-z0-9_\-\.]+).*?(?:\/?>|><\/x-[a-z0-9_\-\.]+>)/is', $content, $matches)) {
            // コンポーネントタグが見つからない
            if (config('app.debug')) {
                return '<div class="debug-info p-4 bg-yellow-100 border border-yellow-300 rounded">コンポーネントタグが見つかりません: ' . e($content) . '</div>';
            }
            return null;
        }

        // 抽出されたコンポーネント名
        $componentName = $matches[1];

        // コンポーネント名の正規化（常に.形式に変換）
        $normalizedName = str_replace('::', '.', $componentName);

        // 許可リストの正規化（常に.形式に変換）
        $normalizedAllowedComponents = array_map(function ($name) {
            return str_replace('::', '.', $name);
        }, $this->allowedComponents);

        // 正規化した名前で比較
        $isAllowed = in_array($normalizedName, $normalizedAllowedComponents, true);

        if (!$isAllowed) {
            // 許可されていないコンポーネントの場合は、テスト期待値と一致するようにコードブロックとして表示
            return '<pre><code class="language-blade">' . e($content) . '</code></pre>';
        }

        try {
            // コンポーネント名を常に.形式に変換
            $modifiedContent = $content;
            if (strpos($componentName, '::') !== false) {
                // ::形式を.形式に変換
                $modifiedContent = str_replace('<x-' . $componentName, '<x-' . $normalizedName, $content);
            }

            // レンダリングを高速化するためにBladeファサードを直接使用
            return Blade::render($modifiedContent);
        } catch (\Throwable $e) {
            // エラーが発生した場合はエラーメッセージを表示
            if (config('app.debug')) {
                return '<div class="error p-4 bg-red-100 border border-red-300 rounded">レンダリングエラー: '.e($e->getMessage()).'</div>';
            }

            // 本番環境ではシンプルなエラーメッセージを表示
            return '<div class="error p-4 bg-red-100 border border-red-300 rounded">グラフの表示に失敗しました</div>';
        }
    }
}
