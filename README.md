HomoChecker
===========

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
