<?php
/**
 * Created by PhpStorm.
 * User: higashiguchi0kazuki
 * Date: 8/15/17
 * Time: 14:42
 */

session_start();

header('Content-type: text/html; charset=utf-8');

// CSRF対策のトークン判定
if($_POST['token'] != $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
}

// クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

// データベース接続
require_once("db.php");
$dbh = db_connect();

// エラーメッセージの初期化
$errors = array();

if(empty($_POST)){
    header("LOCATION: registration_mail_form.php");
    exit();
}

$mail = $_SESSION['mail'];
$account = $_SESSION['account'];

// パスワードのハッシュ化
$password_hash = password_hash($_SESSION['password'], PASSWORD_DEFAULT);

// データベース登録処理
try{
    //例外処理
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // トランザクション開始
    $dbh->beginTransaction();

    // memberテーブルへの登録
    $statement = $dbh->prepare("INSERT INTO member (account, mail, password) VALUES (:account, :mail, :password_hash)");
    // プレースホルダに値をセットする。
    $statement->bindValue(':account', $account, PDO::PARAM_STR);
    $statement->bindValue(':mail', $mail, PDO::PARAM_STR);
    $statement->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
    $statement->execute();

    // pre_memberのflagの1にする
    $statement = $dbh->prepare("UPDATE pre_member SET flag=1 WHERE mail=(:mail)");
    // プレースホルダに値をセットする。
    $statement->bindValue(':mail', $mail, PDO::PARAM_STR);
    $statement->execute();

    // トランザクション完了
    $dbh->commit();

    // データベース接続切断
    $dbh = null;

    // セッション変数を解除
    $_SESSION = array();

    // セッションクッキーの削除
    if(isset($_COOKIE["PHPSESSID"])){
        setcookie("PHPSESSID", '', time() - 1800, '/');
    }

    // セッション破棄
    session_destroy();

    /*
     * Todo: 登録完了メール送信
     */
}catch(PDOException $e){
    // トランザクション取り消し
    $dbh->rollBack();
    $errors['error'] = "もう一度やり直してください。";
    print("Error:".$e->getMessage());
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>会員登録完了画面</title>
    <meta charset="utf-8">
</head>
<body>

<?php if (count($errors) === 0): ?>
    <h1>会員登録完了画面</h1>

    <p>登録完了いたしました。ログイン画面からどうぞ。</p>
    <p><a href="">ログイン画面（未リンク）</a></p>

<?php elseif(count($errors) > 0): ?>

    <?php
    foreach($errors as $value){
        echo "<p>".$value."</p>";
    }
    ?>

<?php endif; ?>

</body>