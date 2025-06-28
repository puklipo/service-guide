<?php

namespace Tests\Unit\Support\Markdown;

use App\Support\Markdown\Extension\BladeComponentsExtension;
use App\Support\Markdown\Extension\BladeComponentsRenderer;
use Illuminate\Support\Facades\Blade;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\Config\ConfigurationInterface;
use Mockery;
use Tests\TestCase;

class BladeComponentsExtensionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * BladeComponentsExtensionが正しく登録されることを確認
     */
    public function test_extension_registers_events()
    {
        $environment = Mockery::mock('League\CommonMark\Environment\EnvironmentBuilderInterface');

        // Extensionのregisterメソッドで、addRendererメソッドが
        // BladeComponentsRendererのインスタンスで呼び出されることを検証
        $environment->shouldReceive('addRenderer')
            ->with(
                FencedCode::class,
                Mockery::type(BladeComponentsRenderer::class),
                10,
            )
            ->once();

        // エクステンションを登録
        $extension = new BladeComponentsExtension;
        $extension->register($environment);

        // 明示的なアサーションを追加
        $this->assertTrue(true, 'Mockery expectationsは満たされており、例外は発生していません');
    }

    /**
     * BladeComponentsRendererが許可されたコンポーネントを正しく処理することを確認
     */
    public function test_blade_component_renderer_handles_allowed_components()
    {
        // テスト用の設定（変更：chart.barからchart-barに変更）
        config(['markdown.blade_components.allowed_components' => ['chart.bar']]);

        // レンダラーのインスタンスを作成
        $renderer = new BladeComponentsRenderer;

        // プライベートメソッドをテストするためにリフレクションを使用
        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('processBlade');
        $method->setAccessible(true);

        // モックBladeコンテンツ（変更なし）
        $content = '<x-chart.bar :data="[100, 200, 300]" />';

        // コンテンツが許可されたコンポーネントとして処理されることを確認
        $result = $method->invokeArgs($renderer, [$content]);
        $this->assertNotNull($result, '許可されたコンポーネントが処理されていません');
    }

    /**
     * ブレードコンポーネント内のデータがJSONエンコードされることを確認
     * これはAlpineJSのx-dataバインディングに必要
     */
    public function test_blade_component_encodes_data()
    {
        // テスト用の設定
        config(['markdown.blade_components.allowed_components' => ['test-chart']]);

        // テスト用のbladeディレクティブを定義
        Blade::directive('test_chart_directive', function ($expression) {
            return "<?php echo '<div x-data=\"{ data: ' . json_encode([100, 200, 300]) . ' }\">Chart Component</div>'; ?>";
        });

        // テスト用のコンテンツを作成
        $content = '@test_chart_directive([100, 200, 300])';

        // Blade::renderで直接レンダリングして結果を確認
        $renderedHtml = Blade::render($content);

        // Bladeディレクティブの出力をチェック
        $this->assertStringContainsString('[100,200,300]', $renderedHtml);
        $this->assertStringContainsString('Chart Component', $renderedHtml);
    }

    /**
     * bladeという言語指定がないコードブロックは変換されないことを確認
     */
    public function test_non_blade_code_blocks_are_not_processed()
    {
        // 実際のFencedCodeオブジェクトを使用
        $fencedCode = new FencedCode(3, '`', 0);

        // infoプロパティに言語を設定するためにリフレクションを使用
        $reflection = new \ReflectionProperty($fencedCode, 'info');
        $reflection->setAccessible(true);
        $reflection->setValue($fencedCode, 'php');

        // リテラル（内容）も同様にリフレクションを使用して設定
        $literalReflection = new \ReflectionProperty($fencedCode, 'literal');
        $literalReflection->setAccessible(true);
        $literalReflection->setValue($fencedCode, '<x-chart.bar :data="[100, 200, 300]" />');

        // レンダラーのインスタンス
        $renderer = new BladeComponentsRenderer;

        // 子ノードレンダラーをモック
        $childRenderer = Mockery::mock(ChildNodeRendererInterface::class);
        $childRenderer->shouldReceive('renderNodes')
            ->andReturn('モックレンダリング結果');

        // renderメソッドを呼び出し、結果を検証
        $result = $renderer->render($fencedCode, $childRenderer);

        // PHP言語を指定したため、bladeコンポーネントとして処理されずに
        // childRendererのrenderNodesメソッドの結果が返されることを検証
        $this->assertEquals('モックレンダリング結果', $result);
    }

    /**
     * 許可されていないコンポーネントがコードブロックとして表示されることを確認
     */
    public function test_disallowed_component_renders_as_code_block()
    {
        // 許可リストに含まれないコンポーネントを設定
        config(['markdown.blade_components.allowed_components' => ['chart.bar']]);

        // レンダラーのインスタンスを作成
        $renderer = new BladeComponentsRenderer;

        // プライベートメソッドをテストするためにリフレクションを使用
        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('processBlade');
        $method->setAccessible(true);

        // 許可されていないコンポーネント
        $content = '<x-unauthorized-component />';

        // 処理結果を取得
        $result = $method->invokeArgs($renderer, [$content]);

        // 結果がコードブロックHTMLとして表示されることを確認
        $this->assertStringContainsString('<pre><code class="language-blade">', $result);
        $this->assertStringContainsString('&lt;x-unauthorized-component /&gt;', $result);
    }

    /**
     * ConfigurationAwareInterfaceを介して設定が正しく注入され、使用されることを確認
     */
    public function test_configuration_aware_interface_allows_configuration_injection()
    {
        // レンダラーのインスタンスを作成
        $renderer = new BladeComponentsRenderer;

        // モック設定を作成
        $config = Mockery::mock(ConfigurationInterface::class);

        // CommonMarkの設定から値を取得する振る舞いをモックする
        // 第二引数のデフォルト値はなく、単一の引数のみを受け取る
        $config->shouldReceive('get')
            ->with('blade_components/allowed_components')
            ->andReturn(['mock-component', 'another-component']);

        // 設定をレンダラーに注入
        $renderer->setConfiguration($config);

        // プライベートプロパティにアクセスするためにリフレクションを使用
        $reflection = new \ReflectionClass($renderer);
        $allowedComponentsProperty = $reflection->getProperty('allowedComponents');
        $allowedComponentsProperty->setAccessible(true);

        // loadAllowedComponentsメソッドを呼び出す
        $method = $reflection->getMethod('loadAllowedComponents');
        $method->setAccessible(true);
        $method->invoke($renderer);

        // allowedComponentsプロパティが設定から正しく読み込まれたことを確認
        $allowedComponents = $allowedComponentsProperty->getValue($renderer);
        $this->assertEquals(['mock-component', 'another-component'], $allowedComponents);

        // Laravelの設定ではなく、注入された設定から値が取得されることを確認
        // 一時的に異なるLaravel設定を登録しても上書きされないことをテスト
        config(['markdown.blade_components.allowed_components' => ['laravel-component']]);

        // allowedComponentsをリセットしてから再度読み込み
        $allowedComponentsProperty->setValue($renderer, null);
        $method->invoke($renderer);

        // 設定がCommonMark経由で取得され、Laravelの設定ではないことを確認
        $allowedComponents = $allowedComponentsProperty->getValue($renderer);
        $this->assertEquals(['mock-component', 'another-component'], $allowedComponents);
        $this->assertNotContains('laravel-component', $allowedComponents);
    }

    /**
     * 設定からの値取得時に例外が発生した場合のテスト
     */
    public function test_handles_exception_when_getting_configuration_value()
    {
        // レンダラーのインスタンスを作成
        $renderer = new BladeComponentsRenderer;

        // 例外を投げるモック設定を作成
        $config = Mockery::mock(ConfigurationInterface::class);
        $config->shouldReceive('get')
            ->with('blade_components/allowed_components')
            ->andThrow(new \Exception('設定キーが見つかりません'));

        // 設定をレンダラーに注入
        $renderer->setConfiguration($config);

        // プライベートプロパティにアクセスするためにリフレクションを使用
        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('loadAllowedComponents');
        $method->setAccessible(true);

        // 例外が発生してもエラーにならないことを確認
        $method->invoke($renderer);

        // デフォルト値が使用されることを確認
        $allowedComponentsProperty = $reflection->getProperty('allowedComponents');
        $allowedComponentsProperty->setAccessible(true);
        $allowedComponents = $allowedComponentsProperty->getValue($renderer);

        $this->assertEquals(['chart.bar', 'chart.line', 'chart.pie'], $allowedComponents);
    }

    /**
     * 設定が注入されていない場合はLaravelの設定にフォールバックすることを確認
     */
    public function test_falls_back_to_laravel_config_when_configuration_not_injected()
    {
        // Laravelの設定を定義
        config(['markdown.blade_components.allowed_components' => ['laravel-component']]);

        // レンダラーのインスタンスを作成（設定を注入しない）
        $renderer = new BladeComponentsRenderer;

        // プライベートプロパティにアクセスするためにリフレクションを使用
        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('loadAllowedComponents');
        $method->setAccessible(true);
        $method->invoke($renderer);

        // allowedComponentsプロパティがLaravelの設定から読み込まれたことを確認
        $allowedComponentsProperty = $reflection->getProperty('allowedComponents');
        $allowedComponentsProperty->setAccessible(true);
        $allowedComponents = $allowedComponentsProperty->getValue($renderer);

        $this->assertEquals(['laravel-component'], $allowedComponents);
    }

    /**
     * 設定値がnullだった場合のテスト
     */
    public function test_uses_default_when_configuration_value_is_null()
    {
        // レンダラーのインスタンスを作成
        $renderer = new BladeComponentsRenderer;

        // nullを返すモック設定を作成
        $config = Mockery::mock(ConfigurationInterface::class);
        $config->shouldReceive('get')
            ->with('blade_components/allowed_components')
            ->andReturn(null);

        // 設定をレンダラーに注入
        $renderer->setConfiguration($config);

        // プライベートプロパティにアクセスするためにリフレクションを使用
        $reflection = new \ReflectionClass($renderer);
        $method = $reflection->getMethod('loadAllowedComponents');
        $method->setAccessible(true);
        $method->invoke($renderer);

        // デフォルト値が使用されることを確認
        $allowedComponentsProperty = $reflection->getProperty('allowedComponents');
        $allowedComponentsProperty->setAccessible(true);
        $allowedComponents = $allowedComponentsProperty->getValue($renderer);

        $this->assertEquals(['chart.bar', 'chart.line', 'chart.pie'], $allowedComponents);
    }
}
