HomoChecker
===========

[![][travis-badge]][travis-link]
[![][coveralls-badge]][coveralls-link]
[![][climate-badge]][climate-link]
[![][homo-badge]][homo-link]

HomoChecker はホモ（[@mpyw](https://twitter.com/mpyw)）にリダイレクトするホモのためのホモの輪です。

## 目次

- [ホモへの手引き](#ホモへの手引き)
- [動作環境](#動作環境)
- [実行するには](#実行するには)
- [テストするには](#テストするには)
- [API](/api/README.md)

## ホモへの手引き

[@java_shit](https://twitter.com/java_shit) にカミングアウトしてください。[@mpyw](https://twitter.com/mpyw) と関係があってもなくても構いません。

### 設定方法

DNS を適切に設定したあと、お使いの Web サーバーに合わせて設定を行います。

#### Apache

```apache
<VirtualHost *:80>
    ServerName homo.example.com
    Redirect permanent / https://twitter.com/mpyw
</VirtualHost>
```

#### nginx

```nginx
server {
    listen 80;
    server_name homo.example.com;
    return 301 https://twitter.com/mpyw;
}
```

#### H2O

```yaml
hosts:
    "homo.example.com:80":
        listen:
            port: 80
        paths:
            "/":
                redirect:
                    url: https://twitter.com/mpyw
                    status: 301
```

Web サーバーが静的コンテンツ配信のみの場合は HTML によるリダイレクトを行います。

```html
<!doctype html>
<title>homo</title>
<meta http-equiv="refresh" content="1; url=https://twitter.com/mpyw">
```

## 動作環境

### フロントエンド

Internet Explorer 10 以上で動くのでたいていのホモは救われます。

### バックエンド

Docker Compose のインストールが必要です。  
nginx + PHP-FPM + MySQL で構成されています。

## 実行するには

初回実行時のみコンテナーのビルド作業が必要です。

```sh
$ bin/init
```

webpack のモード、ポート番号、サブネット/ゲートウェイを指定する場合は環境変数を変更します（任意）。
変更しない場合は適宜 NAT を構成してください。以下はデフォルト値です。

```sh
$ export HOMOCHECKER_ENV=production
$ export HOMOCHECKER_PORT=4545

$ export HOMOCHECKER_SUBNET4=10.45.45.0/24
$ export HOMOCHECKER_GATEWAY4=10.45.45.1

$ export HOMOCHECKER_SUBNET6=fd00:4545::/48
$ export HOMOCHECKER_GATEWAY6=fd00:4545::1
```

次のコマンドでコンテナーを起動します。

```sh
$ docker-compose up -d
```

ブラウザーで次の URL にアクセスします。

```
http://localhost:4545
```

コンテナーを終了するには次のコマンドを使用します。

```sh
$ docker-compose stop
```

現在の最新データは SQL 形式で[ダウンロード](https://homo.chitoku.jp:4545/list/?format=sql)できます。  
次のコマンドで MySQL にログインできます。

```sh
$ docker-compose exec database mysql -uhomo -phomo -Dhomo
```

たとえば最新のデータを入れるには次のようにします。

```sh
$ curl -s 'https://homo.chitoku.jp:4545/list/?format=sql' | docker exec -i $(docker-compose ps -q database) mysql -uhomo -phomo -Dhomo
```

## テストするには

次のコマンドでテストを実行します。

```sh
$ bin/test
```


[travis-link]:          https://travis-ci.org/chitoku-k/HomoChecker
[travis-badge]:         https://img.shields.io/travis/chitoku-k/HomoChecker.svg?style=flat-square
[coveralls-link]:       https://coveralls.io/github/chitoku-k/HomoChecker
[coveralls-badge]:      https://img.shields.io/coveralls/chitoku-k/HomoChecker.svg?style=flat-square
[climate-link]:         https://codeclimate.com/github/chitoku-k/HomoChecker/maintainability
[climate-badge]:        https://img.shields.io/codeclimate/maintainability/chitoku-k/HomoChecker.svg?style=flat-square
[homo-link]:            https://homo.chitoku.jp:4545
[homo-badge]:           https://homo.chitoku.jp:4545/badge/?style=flat-square
