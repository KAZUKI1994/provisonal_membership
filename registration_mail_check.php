<?php
/**
 * Created by PhpStorm.
 * User: higashiguchi0kazuki
 * Date: 8/15/17
 * Time: 13:02
 */

session_start();

header("Content-type: text/html; charset=utf-8");

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
    header('LOCATION: registration_mail_form.php');
    exit();
}else{
    // POSTされたデータを変数に入れる
    $mail = isset($_POST['mail']) ? $_POST['mail'] : NULL;

    // メール入力判定
    if($mail == ''){
        $errors['mail'] = 'メールが入力されていません。';
    }else{
        if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-])+$/", $mail)){
            $errors['mail_check'] = 'メールアドレスの形式が正しくありません。';
        }

        /**
         * Todo: 本会員テーブルにすでに登録されているかチェックする
         */
    }
}

if(count($errors) === 0){
    $urltoken = hash('sha256', uniqid(rand(), 1));
    $url = "http://localhost/provisonal_membership/registration_form.php"."?urltoken=".$urltoken;

    // 登録処理
    try{
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $dbh->prepare("INSERT INTO pre_member (urltoken, mail, date) VALUES (:urltoken, :mail, now())");

        // プレースホルダに値設定
        $statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
        $statement->bindValue(':mail', $mail, PDO::PARAM_STR);
        $statement->execute();

        // データベース接続切断
        $dbh = null;

    }catch(PDOException $e){
        print('Error '. $e->getMessage());
        die();
    }

    // メールの宛先
    $mailTo = $mail;

    // Return-pathに指定するメールアドレス
    $returnMail = "contact@example.com";

    $name = "sample shop";
    $mail = "index@example.com";
    $subject = "本会員登録のお願い";

    $body = <<<EOM
24時間以内に下記のURLからご登録ください。
{$url}
EOM;

    mb_language('ja');
    mb_internal_encoding('UTF-8');

    // FROMヘッダーを作成
    $header = 'From: ' . mb_encode_mimeheader($name). ' <' .$mail. '>';

    if(mb_send_mail($mailTo, $subject, $body, $header, '-f'.$returnMail)){
        // セッション変数を全て削除
        $_SESSION = array();

        // クッキーの削除
        if(isset($_COOKIE['PHPSESSID'])){
            setcookie('PHPSESSID', '', time() - 1800, '/');
        }

        // セッションを破棄する
        session_destroy();

        $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";

    }else{
        $errors['mail_error'] = "メールの送信に失敗しました。";
    }
}

?>


<!DOCTYPE html>
<html>
<head>
    <title>メール確認画面</title>
    <meta charset="utf-8">
</head>
<body>
<h1>メール確認画面</h1>
<?php if(count($errors) === 0): ?>
    <p><?=$message?></p>

    <p>本会員登録URL（postfixがローカルにないため仮置き）</p>
    <a href="<?=$url?>"><?=$url?></a>

<?php elseif(count($errors) > 0): ?>
    <?php
    foreach($errors as $value){
        echo "<p>".$value."</p>";
    }
    ?>

<input type="button" value="戻る" onClick="history.back()">

<?php endif; ?>

</body>
</html>

