HomoChecker
===========

[![][php-badge]][php-link]
[![][travis-badge]][travis-link]
[![][dependencies-badge]][dependencies-link]
[![][coveralls-badge]][coveralls-link]
[![][climate-badge]][climate-link]
[![][homo-badge]][homo-link]

HomoChecker はホモ（[@mpyw](https://twitter.com/mpyw)）にリダイレクトするホモのためのホモの輪です。

## 目次

- [ホモへの手引き](#ホモへの手引き)
- [動作環境](#動作環境)
- [ビルド環境](#ビルド環境)
- [動作確認](#動作確認)
- [API](#api)
  - [Check API](#check-api)
  - [List API](#list-api)
  - [Badge API](#badge-api)

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

- PHP 7.1 以上
- cURL 7.49.0 以上
- Node.js 6 以上
- MySQL

## ビルド環境

動作の確認には ~~ディルド~~ ビルド を行う必要があります。  
前述のバックエンドの環境に加え Composer と npm がインストールされている必要があります。

初回の環境構築は次のコマンドで行います:

```sh
$ composer install
$ npm install
```

続いてデータベースとテーブルを作成します:

```sql
-- 開発用
CREATE DATABASE `homo`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `screen_name` varchar(20) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `screen_name` (`screen_name`)
) DEFAULT CHARSET=utf8;

-- テスト用
CREATE DATABASE `homo_test`;
```

作成したデータベースを開発用とテスト用のファイルに設定します:

```sh
# 開発用
$ cp app/src/config.sample.php app/src/config.php
$ vim app/src/config.php

# テスト用
$ cp phpunit.xml.dist phpunit.xml
$ vim phpunit.xml
```


## 動作確認

### 実行するには

下記のフロントエンド・バックエンドの操作を実行して、ブラウザーで次の URL にアクセスします:

```
http://localhost:4545
```

### フロントエンド

npm から webpack を実行します。

```sh
$ cd HomoChecker
$ npm run build
```

ソースコードを変更した際に即座にコンパイルを行う場合は次のコマンドを実行します:

```sh
$ npm run watch
```

### バックエンド

PHP のビルトインサーバーを使用します。ポート番号は任意です。
```sh
$ cd HomoChecker
$ php -S 0.0.0.0:4545 router.php
```

## API

### Check API

```
/check/[{username}/][?format=sse|json]
```

指定したユーザー名のユーザーが登録した URL のリダイレクト状況を取得します。  
ユーザー名を省略した場合はすべてのユーザーの情報を返します。  
ユーザー名が存在しない場合は 404 が返ります。

レスポンスは指定された形式で返され、省略した場合は `sse` が指定されます。

#### sse

[Server-Sent Events](https://www.w3.org/TR/eventsource/) によってイベントストリームとして返されます。
またブラウザーのバッファリングを無効にするために、コネクションの先頭に `:` に続く空白バイトが送信されます。

以下にストリームの例を示します。

```
event: response
data: {"homo":{"screen_name":"java_shit","url":"https:\/\/homo.chitoku.jp","display_url":"homo.chitoku.jp","secure":true},"status":"OK","duration":0.45}

event: close
data: end
```

`event` が `response` の場合は `data` は以下に示す JSON データです。
`event` が `close` の場合は `data` は常に `end` です。

#### json

[JSON](http://www.json.org/) によって返されます。

```javascript
[
    {
        // (object) ホモ
        "homo": {
            // (string) スクリーンネーム
            "screen_name": "",

            // (string) URL
            "url": "",

            // (string) 表示用の URL
            "display_url": "",

            // (string) アイコンの URL
            "icon": "",

            // (bool) HTTPS 接続かどうかを示す値
            "secure": true
        },

        // (string) リダイレクト状況を示す値
        // OK: リダイレクト設定済
        // WRONG: リダイレクト未設定
        // CONTAINS: ページ内に URL を含む
        // ERROR: 接続失敗/タイムアウト
        "status": "",

        // (number) リダイレクトにかかった時間 (s)
        "duration": 0.0
    }
]
```

### List API

```
/list/[{username}/]
```

指定したユーザー名のユーザーが登録した URL の一覧を取得します。  
ユーザー名を省略した場合はすべてのユーザーの情報を返します。  
ユーザー名が存在しない場合は 404 が返ります。

レスポンスは JSON によって返されます。

```javascript
[
    {
        // (string) スクリーンネーム
        "screen_name": "",

        // (string) URL
        "url": "",

        // (string) 表示用の URL
        "display_url": "",

        // (bool) HTTPS 接続かどうかを示す値
        "secure": true
    }
]
```

### Badge API

```
/badge/[{status}/]
```

指定したステータスを持つホストの数を示すバッジを取得します。  
バッジは [Shields.io](https://shields.io/) によって生成される画像を返します。  
ステータスを省略した場合は登録されているホストの数を返します。


[php-link]:             https://secure.php.net
[php-badge]:            https://img.shields.io/badge/php-%3e%3d%207.1-8892bf.svg?style=flat-square
[travis-link]:          https://travis-ci.org/chitoku-k/HomoChecker
[travis-badge]:         https://img.shields.io/travis/chitoku-k/HomoChecker.svg?style=flat-square
[dependencies-link]:    https://gemnasium.com/github.com/chitoku-k/HomoChecker
[dependencies-badge]:   https://img.shields.io/gemnasium/chitoku-k/HomoChecker.svg?style=flat-square
[coveralls-link]:       https://coveralls.io/github/chitoku-k/HomoChecker
[coveralls-badge]:      https://img.shields.io/coveralls/chitoku-k/HomoChecker.svg?style=flat-square
[climate-link]:         https://codeclimate.com/github/chitoku-k/HomoChecker
[climate-badge]:        https://img.shields.io/codeclimate/github/chitoku-k/HomoChecker.svg?style=flat-square
[homo-link]:            https://homo.chitoku.jp:4545
[homo-badge]:           https://homo.chitoku.jp:4545/badge/?style=flat-square
