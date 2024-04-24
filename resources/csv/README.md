# データの更新方法

1. https://www.wam.go.jp/content/wamnet/pcpub/top/sfkopendata/ から最新のCSVファイルをダウンロード。全サービス分。
2. ファイル名は変更せずそのまま入れ替える。
3. ファイル名が`csvdownload0**.csv`から変わっていたり、数字が変わっている場合のみ [config/service.php](../../config/service.php) を参考にしてサービスと数字を合わせる。

開発環境
```bash
sail art wam:import
```

本番環境ではVaporのプロジェクトページのCommandsから実行。
```bash
php artisan wam:import
```

## CSVのデータが間違ってる場合
`config/patch.php`と`app/Casts/Telephone.php`などを使って表示時に正しいデータに置き換える。
