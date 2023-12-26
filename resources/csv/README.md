# データの更新方法

1. https://www.wam.go.jp/content/wamnet/pcpub/top/sfkopendata/ から最新のCSVファイルをダウンロード。全サービス分。
2. ファイル名は変更せずそのまま入れ替える。
3. ファイル名が`csvdownload0**.csv`から変わっていたり、数字が変わっている場合のみ [config/service.php](../../config/service.php) を参考にしてサービスと数字を合わせる。
