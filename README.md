# Swarm to cURL
Swarm から cURL 投げて各種SNSへ自動投稿するための PHP スクリプト

# 使い方
1. https://location.foursquare.com/log-in/ より、アプリケーションキーとアプリケーションシークレットを入手します。
1. https://syncer.jp/foursquare-api-matome あたりを参考にして、アクセストークンを取得します。
1. run.php にアクセストークンとその他諸々を設定します。
1. run.php を一定間隔毎に実行します。
1. Enjoy!

# 注意点
1. Foursquare の 1 時間あたりのレート制限が 500 のため、早くても 20 秒毎に実行するのが良い?
1. 画像は扱いません。テキストのみです。
1. 非公開は投稿対象から除外されます。
1. 安定動作は保証しません。自己責任でご利用下さい。
1. サンプルプログラムのため、改造等はご自由にどうぞ。
1. 旧Twitter のサポートを追加しましたが、24 時間当たり 50 投稿 (1 ヶ月で 1,500 投稿) しかできないのでご注意下さい。(各自で API Key の準備が必要です)

# 使用しているライブラリについて
𝕏への投稿には、https://github.com/abraham/twitteroauth を利用しております。

# License
MIT
