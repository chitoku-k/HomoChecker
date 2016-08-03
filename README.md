HomoChecker
===========

[![Build Status](https://travis-ci.org/chitoku-k/HomoChecker.svg?branch=master)](https://travis-ci.org/chitoku-k/HomoChecker)
[![Dependency Status](https://gemnasium.com/badges/github.com/chitoku-k/HomoChecker.svg)](https://gemnasium.com/github.com/chitoku-k/HomoChecker)
[![Code Climate](https://codeclimate.com/github/chitoku-k/HomoChecker/badges/gpa.svg)](https://codeclimate.com/github/chitoku-k/HomoChecker)

HomoChecker はホモ（[@mpyw](https://twitter.com/mpyw)）にリダイレクトするホモのためのホモの輪です。

## ホモへの手引き

[@java_shit](https://twitter.com/java_shit) にカミングアウトするか、このリポジトリにプルリクエストをお送りください。[@mpyw](https://twitter.com/mpyw) と関係があってもなくても構いません。

### 設定方法

DNS を適切に設定したあと、お使いの Web サーバーに合わせて設定を行います。Apache なら次のファイルを `.htaccess` として作成することでホモに成りかわります。

```
Redirect permanent / https://twitter.com/mpyw
```

## 動作環境

### フロントエンド

Internet Explorer 10 以上で動くのでたいていのホモは救われます。

### バックエンド

- PHP 7.0.7 以上
- cURL 7.49.0 以上
- Node.js 6 以上

## ビルド環境

動作の確認には ~~ディルド~~ ビルド を行う必要があります。  
前述のバックエンドの環境に加え Composer と npm がインストールされている必要があります。

初回の環境構築は次のコマンドで行います:

```
$ composer install
$ npm install
```

## 動作確認

### 実行するには

下記のフロントエンド・バックエンドの操作を実行して、ブラウザーで次の URL にアクセスします:

```
http://localhost:4545
```

### フロントエンド

npm から gulp タスクを実行します。

```
$ cd HomoChecker
$ npm start
```

ソースコードを変更した際に即座にコンパイルを行う場合は次のコマンドを実行します:

```
$ npm run watch
```

その他のタスクに関しては `npm run` を実行して確認してください。

### バックエンド

PHP のビルトインサーバーを使用します。ポート番号は任意です。
```
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
