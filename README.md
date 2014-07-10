Simple Custom Vote
=============

独自の投票機能の実装を実現するためのプラグインです。
項目の追加・編集を自由に行なえ、柔軟性をもたせた投票機能の作成を可能とします。

# 使い方
## 投票項目の作成
管理画面から投票項目を作成できます。
設定内容はシンプルに名前のみです。

## 投票ボタンの作成
投票項目を作成したら生成されるショートコードを、
投稿もしくはテンプレートに埋め込みして、項目に対する投票ボタンを設置できます。

### ショートコードの設定値
| プロパティ | 必須 | 設定値  | 未設定時の初期値 |
| ------------- | ------------- |:----- |:----- |
|type_id | 必須 | 管理画面で生成される値をそのまま使用し、変更しないでください | |
|post_id | 必須 | 投票対象の投稿のpost_idを設定してください | |
|html | 必須 | 投票リンク内のHTMLを設定してください　所定の書式により投票数を表示できます | |
|id |  | 投票リンクのaタグに付与するid | |
|class |  | 投票リンクのaタグに付与するclass | |
|unique_id |  | 投票データの一意性を識別するための情報　文字列を指定可能 | |
|allow_duplicate_count |  | unique_idを指定している時、投票を重複カウントするかboolean値で指定 |'false'|
|callback |  | コールバック実行したい関数 指定の文字列はevalにより評価されます |　|

### 投票数の表示
htmlプロパティの設定値に{{count}}という文字を含めることで画面表示時の当該項目の得票数を表示できます

ショートコード例：
```
	[scvote type_id="1" post_id="1"　html="得票数：{{count}}"]
```

### 投票後に得票数を反映させる
htmlプロパティ内の要素で、classにscvote_countが指定されてる要素のtextを得票数に書き換えます

ショートコード例：
```
	[scvote type_id="1" post_id="1" html="只今の得票数は<span class=\'scvote_count\'>{{count}}</span>です"]
```

### コールバックの設定
指定したコールバックは投票処理が行われたのちに実行されます。
また、指定の値をevalにて評価するため、即時実行可能な値での設定をしてください。
ショートコード例：
```
	// 実行する関数の指定
	[scvote type_id="13" post_id="1" html="サンプル項目" callback="someFunction()"]

	// 無名関数の場合
	[scvote type_id="13" post_id="1" html="サンプル項目" callback="(function(){/* something to do */})()"]
```

## 関数

#### SimpleCustomvoteControler::isExistVoteByUniqueId($post_id, $unique_id, (Optional)$type_id)

post_idに対して特定のunique_idで投票済みであるかどうか以下の関数で論理値を返却します。
投票項目ごとに投票済みかどうかを知りたい場合は$type_idを指定してください。

- 引数
 * $post_id: 投稿のID
 * $unique_id: 一意識別用のID
 * $type_id:(オプション) 投票項目のID

- 戻り値
 * true : 投票済み
 * false : 未投票

#### SimpleCustomvoteControler::getVoteCount($post_id, (Optional)$type_id)

post_idに対しての得票数合計を返却します。
投票項目ごとの投票数を取得したい場合は$type_idを指定してください。
データが取得できない場合は例外をスローします。

- 引数
 * $post_id: 投稿のID
 * $type_id:(オプション) 投票項目のID

- 戻り値
 * int 得票数合計
