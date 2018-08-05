API リファレンス
================

## 目次

- [Check API](#check-api)
- [List API](#list-api)
- [Badge API](#badge-api)

## Check API

```
/check/[{username}/][?format=sse|json]
```

指定したユーザー名のユーザーが登録した URL のリダイレクト状況を取得します。  
ユーザー名を省略した場合はすべてのユーザーの情報を返します。  
ユーザー名が存在しない場合は 404 が返ります。

レスポンスは指定された形式で返され、省略した場合は `sse` が指定されます。

### sse

[Server-Sent Events](https://www.w3.org/TR/eventsource/) によってイベントストリームとして返されます。
またブラウザーのバッファリングを無効にするために、コネクションの先頭に `:` に続く空白バイトが送信されます。

以下にストリームの例を示します。

```
event: initialize
data: {"count":30}

event: response
data: {"homo":{"screen_name":"java_shit","url":"https:\/\/homo.chitoku.jp","display_url":"homo.chitoku.jp","secure":true},"status":"OK","duration":0.45}
```

`event` が `initialize` の場合は `data` は `count` を持つ JSON データです。  
`event` が `response` の場合は `data` は以下に示す JSON データです。

### json

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

        // (string) 直前の接続先の IPv4 または IPv6（サポートしている場合）アドレス
        "ip": "2001:db8::4545:4545",

        // (number) リダイレクトにかかった時間 (s)
        "duration": 0.0
    }
]
```

## List API

```
/list/[{username}/][?format=json|sql]
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

## Badge API

```
/badge/[{status}/]
```

指定したステータスを持つホストの数を示すバッジを取得します。  
バッジは [Shields.io](https://shields.io/) によって生成される画像を返します。  
ステータスを省略した場合は登録されているホストの数を返します。
