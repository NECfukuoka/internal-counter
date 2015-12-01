# カウンタ機能

----
## 概要
カウンタ機能は，ページの累計アクセス数を加算し，その累計情報を配信するためのWeb ReST APIです．
PHPで実装され，Apache HTTPDサーバ経由で配信されます．このアプリケーションはDockerで起動します．
検索パラメータをURLに含めてGETメソッドでリクエストを送信すると，検索結果がJSON形式で戻ります．


----
### Dockerへの配備方法

1. GitHubよりZIPファイルとして本API一式をダウンロードし，Dockerの環境にコピーし，ZIPを展開します．

2. 展開したフォルダ内直下(counter)に移動し，
以下のdockerコマンドを実行しDocker Imageを作成します．
```
$ docker build -t counter .
```

3. Docker Containerを配備(起動)します．
```
$ docker run -itd -p 8083:80 --name counter counter
```
**ポート番号の部分は自身の環境にあわせて変更してください．**

4. 以下のURLにアクセスし，JSONが戻ることを確認します．
```
http://localhost:8083/CounterJson.php
```
**localhostではなくIPアドレスを指定してもかまいません**

**リクエストしたクライアントのRefererによりカウントを制限しています。自身のサイトで使用する場合は、CounterJson.phpで定義しているACCEPTABLE_REFERERを自身のサイトのURLに変更します。**



----
## API仕様

APIへのリクエスト(URL)は以下となります：
```
http://[server]/CounterJson.php
```


レスポンスとしてJSON形式の値が戻ります．フォーマットは以下のようになります：
```
{"results":{"total":[全体の総計],"today":[今日(現時点)の総計],"yesterday":[昨日の総計]}}
```

例：リクエスト

```
http://localhost:8083/CounterJson.php
```

例：レスポンス(JSON)

```
{"results":{"total":7,"today":3,"yesterday":4}}
```
