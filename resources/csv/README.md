# データの更新方法

## 自動更新コマンド

ローカルでは
```bash
php artisan csv:update
```

GitHub Actionsを手動実行するとプルリクが作られる。

## 手動更新
1. https://www.wam.go.jp/content/wamnet/pcpub/top/sfkopendata/ から最新のCSVファイルをダウンロード。全サービス分。
2. ファイル名は変更せずそのまま入れ替える。
3. ファイル名が`csvdownload0**.csv`から変わっていたり、数字が変わっている場合のみ [config/service.php](../../config/service.php) を参考にしてサービスと数字を合わせる。

## 更新後はCSVをインポート
開発環境
```bash
php artisan wam:import
```

本番環境ではVaporのプロジェクトページのCommandsから実行。

## CSVのデータが間違ってる場合
`config/patch.php`と`app/Casts/Telephone.php`などを使って表示時に正しいデータに置き換える。

## 古いCSVファイル
`resources/csv/202503`などに`.gitignore`を追加してgit履歴からも削除する。
最新のファイルだけは本番環境でのインポートに必要なので`.gitignore`は追加しない。

## CSVのヘッダー
```csv
"都道府県コード又は市区町村コード","NO（※システム内の固有の番号、連番）","指定機関名","法人の名称","法人の名称_かな","法人番号","法人住所（市区町村）","法人住所（番地以降）","法人電話番号","法人FAX番号","法人URL","サービス種別","事業所の名称","事業所の名称_かな","事業所番号","事業所住所（市区町村）","事業所住所（番地以降）","事業所電話番号","事業所FAX番号","事業所URL","事業所緯度","事業所経度","利用可能な時間帯（平日）","利用可能な時間帯（土曜）","利用可能な時間帯（日曜）","利用可能な時間帯（祝日）","定休日","利用可能曜日特記事項（留意事項）","定員"
```

`事業所番号`は35%も重複しているのでユニークなIDとしては使えない。

## 閉鎖済み事業所の本番用データベースからの削除方法
- GitHubで`.github/workflows/detect-closed.yml`のワークフローを手動実行(Run workflow)。
- `config/deleted.php`を更新するPRが作られるのでマージ。
- Vaporで`php artisan wam:delete`を実行して削除。
