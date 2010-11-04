Gnix_Db
======

Gnix_Db（ニックス・ディービー）はPHP5.3/MySQL専用のORマッピングツール（のプロトタイプ）です。データ規模、アクセス規模が大きく、複雑なJOINやサブクエリーを利用していないシステムに向いています。

※プロトタイプとはいえ、このORMは月間数億PV程度のWebサイトで実際に運用されており、Apache処理時間も100msec/req程度を維持しています。


## 経緯

あるORMを利用しようと思ったところ、そのマニュアルが数百ページにも及んでいました。私はともかくチーム全員にそのORMをマスターしてもらうには気が引けました。我々はただ簡単なSELECTやUPDATEがしたかっただけなのです。

ORMは元々リレーショナル技術とオブジェクト技術の概念の差異（[インピーダンス・ミスマッチ](http://en.wikipedia.org/wiki/Object-relational_impedance_mismatch)）を埋めるべく考案されたものです。しかし、そのORM技術自体が成熟し、肥大化し、今度はORM技術と技術者の間に新たなミスマッチが生まれている気がします。

のような経緯から自社システムのために作成したのですが、自社システム特有のコードがなかったので（絶対に誰も使わないであろうことを承知で）公開しました。


## 必要なもの

  - PHP >= 5.3
  - MySQL

PHP5.3の機能、[遅延静的束縛](http://php.net/manual/ja/language.oop5.late-static-bindings.php)を利用しているため、PHP5.2以前のバージョンでは動作しません。また、[ZendFrameworkコーディング規約](http://framework.zend.com/manual/ja/coding-standard.overview.html)に則っていますので、Zend_Loaderを利用すればクラスの自動ロードが可能になり非常に楽です。


## 特徴

[Propel](http://www.propelorm.org/)と[Zend_Db_Table](http://framework.zend.com/manual/ja/zend.db.table.html)から影響を受けています。クライアント側のコードはPropelに非常に近いです。

以下のものは不要です：

  - XML、yaml、json等の定義ファイル
  - スキーマやテーブル定義の変更
  - コマンド （自動生成しなくてはいけないコード自体不要です）
  - プロパティや連想配列の操作 （結果はすべてオブジェクトで全てメソッドでの操作になります）
  - DESCRIBE TABLE等の開発者が意図しないクエリー
  - SQL

出来ないこと：

  - JOINやサブクエリー （できない事もないですが、PHP側で処理する方がよいです）
  - 連鎖更新、連鎖削除のエミュレート （知ってました？ もしやりたいのであればInnoDBでできます！）

出来ること：

  - 更新＝マスター/参照＝スレーブの自動切換え （また特定の参照クエリーをマスターに向けることも可能です）
  - DBへの遅延接続 （初めてクエリーが発行される際に初めて接続します）
  - 変則的なクエリー （Sennaや自作プラグイン、ストアド関数、MySQL特有の日時関数等を自由に記述できます）
  - 取得するカラムの指定 （デフォルトは「*」による全カラム取得ですが、必要なカラムをクエリー単位に指定できます）
  - トランザクション


## 利用方法

### 1. インストール

1. このページ上部のDownloadsボタンよりデータを取得・展開します。
2. もしZendFrameworkを利用している場合は以下のコードで、自動ロードが可能です。そうでない場合は、エラーメッセージの通り、クラス（PHPファイル）をrequire_onceしてください。

-
    set_include_path(get_include_path() . PATH_SEPARATOR . '/path/to/gnix-db/library'); 
    
    require_once 'Zend/Loader/Autoloader.php'; 
    $autoloader = Zend_Loader_Autoloader::getInstance(); 
    $autoloader->setFallbackAutoloader(true); 


### 2. DB接続設定

以下は、twitterスキーマのマスターDBへの設定例です。第一引数は接続名で、なんでも構いませんが、スキーマ名と合わせると便利です。attributesは[PDOの属性](http://php.net/manual/ja/pdo.setattribute.php)です。

    Gnix_Db_Connection_Master::setInfo(
        'twitter',
        array (
            'host'   => '192.168.0.1',
            'port'   => '3306',
            'dbname' => 'twitter',
            'user'   => 'username',
            'pass'   => 'password',
            'attributes' => array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            )
        )
    );

続いて、twitterスキーマのスレーブDBへの設定例です。

    Gnix_Db_Connection_Slave::setInfo(
        'twitter',
        array (
            'host'   => '192.168.0.2',
            'port'   => '3306',
            'dbname' => 'twitter',
            'user'   => 'username',
            'pass'   => 'password',
            'attributes' => array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            )
        )
    );

上記の情報を確認するには getInfo() メソッドを利用します。

    var_dump(Gnix_Db_Connection_Master::getInfo('twitter'));
    var_dump(Gnix_Db_Connection_Slave::getInfo('twitter'));

単一サーバーで、マスター/スレーブ構成でない場合は、同じ設定を2度行うか、Gnix_Db_Connectionクラスを利用します。

    Gnix_Db_Connection::setInfo(
        'twitter',
        array (
            'host'   => '192.168.0.1',
            'port'   => '3306',
            'dbname' => 'twitter',
            'user'   => 'username',
            'pass'   => 'password',
            'attributes' => array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            )
        )
    );

ほとんどの場合、PDO属性は共通のものを利用するはずです。その場合、上記の設定よりも先にデフォルト値を設定します。

    Gnix_Db_Connection::setDefaultAttributes(array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));

これらの設定は変数に保存されるだけで実際にはMySQLに接続しません。実際に接続されるのは、初めてのクエリーが発行される時です（遅延接続）。よって、もし百台のMySQLサーバーをお持ちであれば、百台の設定コードを書いてしまいましょう。それによるオーバーヘッドはほとんどありません。

一方、遅延接続は設定を間違えてもエラーになりません。接続を確認するには、以下のようにします。正しければPDOオブジェクトを返却します、間違えていればPDOレベルのエラーとなります。

    var_dump(Gnix_Db_Connection_Master::get('twitter'));
    var_dump(Gnix_Db_Connection_Slave::get('twitter'));

上記のコードは新しいサーバーを追加した時に、一度確認するだけです。

また、通常は不要ですが、広告の取得等、時間のかかる処理の前に接続を破棄したい場合等は以下のようにします。

    Gnix_Db_Connection_Master::disconnect('twitter');
    Gnix_Db_Connection_Slave::disconnect('twitter');
    
    // 'twitter'のマスター/スレープを一括で破棄
    Gnix_Db_Connection::disconnect('twitter');
    
    // 'twitter'を含むの全ての接続を破棄
    Gnix_Db_Connection::disconnectAll();

### 3. クラスを作る

[Table Data Gatewayパターン](http://martinfowler.com/eaaCatalog/tableDataGateway.html)を採用しているため、一つのテーブルにつき二つのクラスが必要です。

クエリーの発行を担当するクエリークラスを作成します。**Gnix_Db_Query**を継承します。中身は空です。下記は、twitterスキーマのtweetテーブルに接続するためのクラスです。

    <?php
    class Twitter_Tweet_Query extends Gnix_Db_Query
    {
    }

もしこのテーブルの主キーが'id'という名前で無い場合は、$_key プロパティを設定します。

    <?php
    class Twitter_Tweet_Query extends Gnix_Db_Query
    {
        protected static $_key = 'tweet_id';
    }

※実はマルチカラム主キーには対応していません。マルチカラム主キーでのデータ取得は、以下で説明するクエリー生成を利用すれば簡単にできます。マルチカラム主キーに対応していない理由は、Railsの登場以降、DB設計はAUTO_INCREMENTな人工キーを用いるのが主流であること、またアクセス規模・データ規模が大きくなるにつれ、主キーでの取得/更新の比率が増え（Key-Value Store的になる）、主キーは出来るだけ単純にする傾向があるためです。

次に、tweetテーブルの1行を担当する行クラスを作成します。**Gnix_Db_Row**を継承します。中身は空です。

    <?php
    class Twitter_Tweet extends Gnix_Db_Row
    {
    }

なお、ZendFrameworkのオートローダーを設定しているなら、下記のようにファイルを配置すれば、require_onceが不要になります。

    [include_path]
     `-- Twitter
         |-- Tweet
         |   `-- Query.php
         `-- Tweet.php

### 2. クエリーの発行

#### 単純なパターン

INSERTは以下のようにします。

    $tweet = new Twitter_Tweet();
    $tweet->setScreenName('chikaram');   // screen_nameカラムに値を設定
    $tweet->setText('Good Morning!');    // textカラムに値を設定
    $tweet->save();                      // INSERTを発行
    echo $tweet->getId();                // idカラムの値を表示
    echo $tweet->getCreatedAt();         // created_atカラムの値を表示

上記とは別のプロセスでSELECTする場合、以下のようにします。

    $tweet = Twitter_Tweet_Query::findByKey(1);   // 主キーが「1」のものを取得
    echo $tweet->getId();                         // idカラムの値を表示
    echo $tweet->getScreenName();                 // screen_nameのカラム値を表示
    echo $tweet->getText();                       // textのカラム値を表示
    echo $tweet->getCreatedAt();                  // created_atのカラム値を表示

続けてUPDATEを行います。

    $tweet->setText('Hello!');                            // textカラムに値を設定
    $tweet->setCreatedAt(new Gnix_Db_Literal('NOW()'));   // created_atカラムに値を設定
    $tweet->save();                                       // UPDATEを発行

※MySQLの関数等エスケープ不要な値を設定するには、Gnix_Db_Literalクラスを利用します。setCreatedAt('NOW()') とすると、プリペアドステートメントのプレースホルダーに 'NOW()' という文字列を代入してしまいます。

続けてDELETEを行います。

    $tweet->delete();   // DELETEを発行

上記のような1行をSELECT後にUPDATE/DELETEする方法以外に、一括UPDATE、DELETEも可能です。ページ下部の「メソッド一覧」をご覧ください。


#### 複雑なパターン

複雑なWHERE句の生成は、[Query Object](http://martinfowler.com/eaaCatalog/queryObject.html)に相当するGnix_Db_Criteriaクラスを使用します。このクラスのインスタンスは、クエリークラス（上記の場合Twitter_Tweet_Query）でしか取得できません。こうしてクエリーの責任をクエリークラスに強制することにより、MVCパターンでいうところのコントローラーが肥大化するのを防ぎます。

まず、適当なメソッドを作ります。

    class Twitter_Tweet_Query extends Gnix_Db_Query
    {
        public static function findFooBar()
        {
        }
    }

以下、findFooBar() 内のコードです。

    $criteria = self::_getCriteria();
    $criteria->whereLike('text', '%あ%');   // text LIKE ? を生成
    $criteria->orderByDesc('id');           // ORDER BY id DESC を生成
    $criteria->limit(15);                   // LIMIT 15 を生成
    $criteria->page(3);                     // 3ページ目（31件目以降）のoffset値を自動計算

また、[流暢なインターフェース](http://www.martinfowler.com/bliki/FluentInterface.html)を利用して以下のように書くことも可能です。

    $criteria = self::_getCriteria()
        ->whereLike('text', '%あ%')
        ->orderByDesc('id')
        ->limit(15)
        ->page(3)
    ;

生成したCriteriaはdebug()メソッドで確認できます。

    echo $criteria->debug();
    
    // 下記を出力
    SQL:  WHERE text LIKE ? ORDER BY id DESC LIMIT 30, 15
     -- 
    PARAMS: array(1) {
      [0]=>
      string(5) "%あ%"
    }

findAll() メソッドにCriteriaを渡せばデータを取得できます。完成したメソッドは以下の通りです。

    public static function findFooBar()
    {
        $criteria = self::_getCriteria($keyword, $page)
            ->whereLike('text', '%' . $keyword . '%')
            ->orderByDesc('id')
            ->limit(15)
            ->page($page)
        ;
        return self::findAll($criteria);
    }

コントローラーから以下のように呼び出します。

    $tweets = Twitter_Tweet_Query::findFooBar('あ', 3);

複数行の取得は、行オブジェクトの配列ですので、テンプレートは以下のようになります。（以下はZend_Viewの場合の例ですが、Smarty等でも構いません。）

    <? foreach ($this->tweets as $tweet): ?>
        <p>@<?= $tweet->getScreenName() ?>: <?= $tweet->getText() ?></p>
    <? endforeach ?>

もし、上記のような表示パターンを何度も利用するのであれば、以下のようなメソッドを行クラスに作成します。

    class Twitter_Tweet extends Gnix_Db_Row
    {
        public function getDisplayTweet()
        {
            return '@' . $this->getScreenName() . ': ' . $this->getText();
        }
    }

そうすればテンプレートが簡潔になります。

    <? foreach ($this->tweets as $tweet): ?>
      <p><?= $tweet->getDisplayTweet() ?></p>
    <? endforeach ?>


## メソッド一覧

### Gnix_Db_Criteria

#### WHERE系

1. whereEqual('column', 'value')
2. whereNotEqual('column', 'value')
3. whereGreater('column', 'value')
4. whereGreaterEqual('column', 'value')
5. whereLess('column', 'value')
6. whereLessEqual('column', 'value')
7. whereIsNull('column', 'value')
8. whereIsNotNull('column', 'value')
9. whereNotLike('column', 'value')
10. whereBetween('column', 'from_value', 'to_value')   // なぜか未実装（実装予定）
11. whereIn('column', array('value1', 'value2', 'value3', ...))
12. whereNotIn('column', array('value1', 'value2', 'value3', ...))
13. where('string' [, 'value' OR array('value1', 'value2', 'value3', ...)])

whereメソッドの例）

- where('column1 = ? OR column2 = ?', array(10, 20))
- where('updated_at < (CURRENT_TIMESTAMP - INTERVAL 15 SECOND)')
- where('MATCH (text) AGAINST (? IN BOOLEAN MODE)', '*D+ ' . $keyword)
- where('id IN (SELECT foo FROM bar WHERE baz = ?)', 3)   // サブクエリーを使いたい場合

#### GROUP BY

実装予定

#### HAVING

実装予定

#### ORDER BY系

1. orderBy('column')
2. orderByDesc('column')

#### LIMIT系

1. limit(int)
2. offset(int)
3. page(int)   // offset値の自動計算


### Gnix_Db_Query

#### SELECT系

1. array(Gnix_Db_Row) = findAll(Gnix_Db_Criteria $criteria, array $columns = array('*'), $master = false)
2. Gnix_Db_Row = find(Gnix_Db_Criteria $criteria, array $columns = array('*'), $master = false)
3. Gnix_Db_Row = findByKey($key, array $columns = array('*'), $master = false)
4. int $count = count(Gnix_Db_Criteria $criteria, $master = false)

$master = true でマスターDBから取得します。またデータの取得結果は以下になります。

  - 複数行取得（findAll）で結果あり： array(行オブジェクト, 行オブジェクト, 行オブジェクト...)
  - 複数行取得（findAll）で結果なし： array()
  - 単数行取得（find）で結果あり： 行オブジェクト
  - 単数行取得（find）で結果なし： null

#### INSERT系

1. int $lastInsertId = create(array $data)

#### UPDATE系

1. int $rowCount = update(array $ data, Gnix_Db_Criteria $criteria)
2. int $rowCount = updateByKey(array $ data, $key)

#### DELETE系

1. int $rowCount = delete(Gnix_Db_Criteria $criteria)
2. int $rowCount = deleteByKey($key)

#### REPLACE系

実装予定


## License

    The MIT License
    
    Copyright (c) 2010 GMO Media, Inc.

    Permission is hereby granted, free of charge, to any person obtaining a copy 
    of this software and associated documentation files (the "Software"), to deal 
    in the Software without restriction, including without limitation the rights 
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
    copies of the Software, and to permit persons to whom the Software is 
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in 
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
    THE SOFTWARE.

[Copyright (c) 2010 GMO Media, Inc.](http://www.gmo-media.jp/licence/mit.html)
