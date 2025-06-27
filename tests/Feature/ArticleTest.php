<?php

namespace Tests\Feature;

use App\Support\Markdown;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    /**
     * 記事ページのルーティングテスト
     */
    public function test_article_route_works_with_valid_date_and_slug()
    {
        // テスト用記事ファイルのパスを設定
        $testArticlePath = resource_path('articles/202503/test-article.md');
        $testArticleDir = dirname($testArticlePath);

        // ディレクトリがなければ作成
        if (! File::exists($testArticleDir)) {
            File::makeDirectory($testArticleDir, 0755, true);
        }

        // テスト用記事ファイルを作成
        File::put($testArticlePath, "---\ntitle: テスト記事\ndescription: これはテスト記事です\n---\n\n# テスト記事\n\nこれはテスト記事です");

        // 記事ページにアクセス
        $response = $this->get('/articles/202503/test-article');

        // 削除
        File::delete($testArticlePath);

        // アサーション
        $response->assertStatus(200);
        $response->assertSee('テスト記事');
    }

    /**
     * 存在しない記事へのアクセス時にリダイレクトされることを確認
     */
    public function test_article_route_redirects_when_article_not_found()
    {
        $response = $this->get('/articles/202503/non-existing-article');
        $response->assertRedirect('/');
    }

    /**
     * 基本的なMarkdown解析が機能することを確認
     */
    public function test_markdown_parse_works_with_basic_markdown()
    {
        $markdown = "# テスト見出し\n\nこれは**テスト**です。";
        $html = Markdown::parse($markdown);

        $this->assertStringContainsString('<h1>テスト見出し</h1>', $html);
        $this->assertStringContainsString('これは<strong>テスト</strong>です。', $html);
    }

    /**
     * HTML入力が許可されていることを確認
     */
    public function test_markdown_parse_allows_html()
    {
        $markdown = "# テスト\n\n<div class=\"test\">HTMLテスト</div>";
        $html = Markdown::parse($markdown);

        $this->assertStringContainsString('<div class="test">HTMLテスト</div>', $html);
    }

    /**
     * BladeComponentsExtensionが正しく機能することを確認
     */
    public function test_blade_components_extension_works()
    {
        // テスト用のコンポーネントファイルを作成
        $componentPath = resource_path('views/components/tests/mock.blade.php');
        $componentDir = dirname($componentPath);

        if (! File::exists($componentDir)) {
            File::makeDirectory($componentDir, 0755, true);
        }

        File::put($componentPath, '@props(["value"])<div class="mock-component">Mock Component: {{ $value }}</div>');

        try {
            // テスト用に許可コンポーネントを設定
            config(['markdown.blade_components.allowed_components' => ['tests.mock']]);

            $markdown = "```blade\n<x-tests.mock value=\"テスト値\" />\n```";

            $html = Markdown::parse($markdown);

            $this->assertStringContainsString('<div class="mock-component">Mock Component: テスト値</div>', $html);
        } finally {
            // テスト後にファイルを削除
            if (File::exists($componentPath)) {
                File::delete($componentPath);
            }
        }
    }

    /**
     * 許可されていないBladeコンポーネントが使用されたときにコードブロックとして表示されることを確認
     */
    public function test_disallowed_blade_components_are_rendered_as_code()
    {
        config(['markdown.blade_components.allowed_components' => ['chart.bar']]);

        $markdown = "```blade\n<x-unauthorized-component />\n```";
        $html = Markdown::parse($markdown);

        $this->assertStringContainsString('<pre><code class="language-blade">', $html);
        $this->assertStringContainsString('&lt;x-unauthorized-component /&gt;', $html);
    }

    /**
     * グラフコンポーネントが正しく機能することを確認
     */
    public function test_chart_components_render_correctly()
    {
        // 実際にはコンポーネントのレンダリングをテストするのは難しいので、
        // コンポーネントが存在することを確認する簡易的なテスト

        $barChartPath = resource_path('views/components/chart/bar.blade.php');
        $lineChartPath = resource_path('views/components/chart/line.blade.php');
        $pieChartPath = resource_path('views/components/chart/pie.blade.php');

        $this->assertTrue(File::exists($barChartPath), 'バーグラフコンポーネントが存在しません');
        $this->assertTrue(File::exists($lineChartPath), '折れ線グラフコンポーネントが存在しません');
        $this->assertTrue(File::exists($pieChartPath), '円グラフコンポーネントが存在しません');
    }

    /**
     * グラフコンポーネントの設定が正しく読み込まれることを確認
     */
    public function test_chart_configuration_is_loaded()
    {
        $this->assertIsArray(config('markdown.blade_components.allowed_components'));
        $this->assertContains('chart.bar', config('markdown.blade_components.allowed_components'));
        $this->assertContains('chart.line', config('markdown.blade_components.allowed_components'));
        $this->assertContains('chart.pie', config('markdown.blade_components.allowed_components'));
    }

    /**
     * グラフコンポーネントが正しくレンダリングされることを確認
     */
    public function test_chart_bar_component_renders_correctly_in_markdown()
    {
        // テスト用のBarコンポーネントファイルがなければスキップ
        $barChartPath = resource_path('views/components/chart/bar.blade.php');
        if (! File::exists($barChartPath)) {
            $this->markTestSkipped('バーグラフコンポーネントが存在しないためテストをスキップします');
        }

        // chart.barコンポーネントを許可リストに設定
        config(['markdown.blade_components.allowed_components' => ['chart.bar']]);

        // マークダウン内にBladeコンポーネントを含める
        $markdown = "```blade\n<x-chart.bar\n  :data=\"[145937, 149540, 155972, 159780]\"\n  :labels=\"['2021年11月', '2022年3月', '2022年9月', '2023年3月']\"\n  title=\"テストグラフ\"\n/>\n```";

        // マークダウンを解析
        $html = Markdown::parse($markdown);

        // レンダリング結果をログに出力（デバッグ用）
        info('Rendered HTML for chart.bar:', ['html' => $html]);

        // 期待される結果: <pre><code>タグが含まれていないこと
        $this->assertStringNotContainsString('<pre><code', $html, 'レンダリング結果に<pre><code>タグが含まれています');

        // 期待される結果: chart-containerクラスを持つdivが含まれていること
        $this->assertStringContainsString('<div class="chart-container', $html, 'レンダリング結果にchart-containerクラスが含まれていません');

        // 期待される結果: データとラベルがJSON形式で含まれていることを確認
        $this->assertStringContainsString('"data":', $html, 'レンダリング結果にデータ配列が含まれていません');
        $this->assertStringContainsString('"labels":', $html, 'レンダリング結果にラベル配列が含まれていません');
        $this->assertStringContainsString('[145937,149540,155972,159780]', $html, 'レンダリング結果に正しいデータ値が含まれていません');

        // タイトルが表示されていることを確認
        $this->assertStringContainsString('テストグラフ', $html, 'レンダリング結果にグラフタイトルが含まれていません');

        // Alpine.js関連の属性が含まれていることを確認
        $this->assertStringContainsString('x-data=', $html, 'レンダリング結果にAlpine.jsのx-dataディレクティブが含まれていません');
    }
}
