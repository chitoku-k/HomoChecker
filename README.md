HomoChecker
===========

[![][workflow-badge]][workflow-link]
[![][coveralls-badge]][coveralls-link]
[![][climate-badge]][climate-link]
[![][homo-badge]][homo-link]

HomoChecker はホモ（[@mpyw](https://twitter.com/mpyw)）にリダイレクトするホモのためのホモの輪です。

## 目次

- [ホモへの手引き](#ホモへの手引き)
- [本番環境](#本番環境)
- [開発環境](#開発環境)
- [テスト](#テスト)
- [API](/api/README.md)

## ホモへの手引き

[@java\_shit](https://twitter.com/java_shit) にカミングアウトしてください。[@mpyw](https://twitter.com/mpyw) と関係があってもなくても構いません。

### 設定方法

DNS を適切に設定したあと、お使いの Web サーバーに合わせて設定を行います。  
HomoChecker は HTTP/1.1、HTTP/2、HTTP/3（Alt-Svc のみ）に対応しています。

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
    listen [::]:80;
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

#### Cloudflare

1. DNS で `homo.example.com` を Cloudflare を通すように設定します。
2. Page Rule を作成します。
   - `If the URL matches:` に `*homo.example.com/*` を指定します。
   - `Then the settings are:` に `Forwarding URL`、`301 - Permanent Redirect`、`https://twitter.com/mpyw` を指定します。

#### 静的配信

下記の内容を HTML 形式で配信します。

```html
<!doctype html>
<title>homo</title>
<meta http-equiv="refresh" content="1; url=https://twitter.com/mpyw">
```

## 本番環境

BuildKit（または Docker の対応するバージョン）あるいは Buildah のインストールが必要です。

- `docker build` を利用する場合: Docker 18.09 以上
- `docker buildx` を利用する場合: Docker 19.03 以上

nginx + PHP-FPM + PostgreSQL で構成されています。

### 設定

#### nginx

```sh
$ export HOMOCHECKER_API_HOST=api
```

#### PHP-FPM

```sh
$ export HOMOCHECKER_DB_HOST=database
$ export HOMOCHECKER_DB_PORT=5432
$ export HOMOCHECKER_DB_USERNAME=homo
$ export HOMOCHECKER_DB_PASSWORD=homo
$ export HOMOCHECKER_DB_SSLMODE=prefer
$ export HOMOCHECKER_DB_SSLCERT=/path/to/sslcert
$ export HOMOCHECKER_DB_SSLKEY=/path/to/sslkey
$ export HOMOCHECKER_DB_SSLROOTCERT=/path/to/sslrootcert
$ export HOMOCHECKER_LOG_LEVEL=debug
$ export HOMOCHECKER_TWITTER_CONSUMER_KEY=
$ export HOMOCHECKER_TWITTER_CONSUMER_SECRET=
$ export HOMOCHECKER_TWITTER_TOKEN=
$ export HOMOCHECKER_TWITTER_TOKEN_SECRET=
```

### ビルド

```sh
$ docker buildx bake
```

## 開発環境

### 設定

webpack のモード、ポート番号を指定する場合は環境変数を変更します（任意）。

```sh
$ export HOMOCHECKER_ENV=production
$ export HOMOCHECKER_PORT=4545
```

IPv6 接続を有効にするためには、あらかじめサブネットを指定してネットワークを作成しておく必要があります。

```sh
$ docker network create --attachable --ipv6 --subnet=fd00:4545::/48 homochecker_ipv6
```

### 実行

```sh
$ bin/init
$ docker compose up -d --build
```

ブラウザーで次の URL にアクセスします。

```
http://localhost:4545
```

コンテナーを終了するには次のコマンドを使用します。

```sh
$ docker compose stop
```

現在の最新データは SQL 形式で[ダウンロード](https://homo.chitoku.jp:4545/list/?format=sql)できます。  
次のコマンドで PostgreSQL にログインできます。

```sh
$ docker compose exec database psql -dhomo -Uhomo
```

たとえば最新のデータを入れるには次のようにします。

```sh
$ curl -s 'https://homo.chitoku.jp:4545/list/?format=sql' |
    docker compose exec --no-TTY database psql -dhomo -Uhomo
```

### テスト

```sh
$ bin/test
```

[workflow-link]:    https://github.com/chitoku-k/HomoChecker/actions?query=branch:master
[workflow-badge]:   https://img.shields.io/github/actions/workflow/status/chitoku-k/HomoChecker/ci.yml?branch=master&style=flat-square&logo=github
[coveralls-link]:   https://coveralls.io/github/chitoku-k/HomoChecker?branch=master
[coveralls-badge]:  https://img.shields.io/coveralls/github/chitoku-k/HomoChecker/master?style=flat-square&logo=coveralls
[climate-link]:     https://codeclimate.com/github/chitoku-k/HomoChecker/maintainability
[climate-badge]:    https://img.shields.io/codeclimate/maintainability/chitoku-k/HomoChecker.svg?style=flat-square&logo=code-climate
[homo-link]:        https://homo.chitoku.jp:4545
[homo-badge]:       https://homo.chitoku.jp:4545/badge/?style=flat-square
