<?php
/**
 * Created by PhpStorm.
 * User: higashiguchi0kazuki
 * Date: 8/15/17
 * Time: 14:04
 */

session_start();

header('Content-type: text/html; charset=utf-8');

// CSRF対策

if($_POST['token'] != $_SESSION['token']){
    echo "不正アクセスの可能性あり";
    exit();
}

// クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

// トリミング
function spaceTrim($str){
    $str = preg_replace('/^[ ]+/u', '', $str);
    $str = preg_replace('/^[ ]+$/u', '', $str);
    return $str;
}

// エラーメッセージの初期化
$errors = array();

if(empty($_POST)){
    header("Location: registration_mail_form.php");
    exit();
}else{
    // POSTデータを変数にセット
    $account = isset($_POST['account']) ? $_POST['account'] : NULL;
    $password = isset($_POST['password']) ? $_POST['password'] : NULL;

    $account = spaceTrim($account);
    $password = spaceTrim($password);

    // アカウント入力判定
    if($account == ''){
        $errors['account'] = "アカウントが入力されていません。";
    }elseif(mb_strlen($account)>10){
        $errors['account_length'] = 'アカウントは10文字以内で入力して下さい。';
    }

    // パスワード入力判定
    if($password == ''){
        $errors['password'] = 'パスワードが入力されていません。';
    }elseif(!preg_match('/^[0-9a-zA-z]{5,30}$/', $_POST["password"])){
        $errors['password_length'] = "パスワードは半角英数字の5文字以上30文字以下で入力してください。";
    }else{
        $password_hide = str_repeat('*', strlen($password));
    }
}

// エラーが無ければセッションに登録
if(count($errors) === 0){
    $_SESSION['account'] = $account;
    $_SESSION['password'] = $password;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>会員登録確認画面</title>
    <meta charset="utf-8">
</head>
<body>
<h1>会員登録確認画面</h1>

<?php if (count($errors) === 0): ?>
<form action="registration_insert.php" method="post">
    <p>メールアドレス：<?=htmlspecialchars($_SESSION['mail'], ENT_QUOTES);?></p>
    <p>アカウント名：<?=htmlspecialchars($account, ENT_QUOTES)?></p>
    <p>パスワード：<?=$password_hide?></p>

    <input type="button" value="戻る" onClick="history.back()">
    <input type="hidden" name="token" value="<?=$_POST['token']?>">
    <input type="submit" value="登録する">
</form>

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

