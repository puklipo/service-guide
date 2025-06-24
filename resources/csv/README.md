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

## hash.txt
自動更新用にファイルのハッシュを記録。
