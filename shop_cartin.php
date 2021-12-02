<?php
session_start();
session_regenerate_id(true);
if(isset($_SESSION['member_login'])==false)
{
	print 'ようこそゲスト様　';
	print '<a href="member_login.html">会員ログイン</a><br />';
	print '<br />';
}
else
{
	print 'ようこそ';
	print $_SESSION['member_name'];
	print '様　';
	print '<a href="member_logout.php">ログアウト</a><br />';
	print '<br />';
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ろくまる農園</title>
</head>
<body>

<?php

try
{

// カートに入れたコードを受け取る
$pro_code=$_GET['procode'];

// もしSESSIONにカートが入っていれば(すでにカートに入れていたら)
if(isset($_SESSION['cart'])==true)
{
	// 現在のカート内容を$cartにコピーする
	$cart=$_SESSION['cart'];
	// (もしここに来るのが2回目以降なら、$_SESSION['cart']も$_SESSION['kazu']も存在するので同じif文に入れてOK)
	$kazu=$_SESSION['kazu'];

	// 配列の中にすでにあるか探す命令(存在するか知りたいデータ,配列名)
	if(in_array($pro_code,$cart)==true)
{

print'その商品はすでにカートに入っています。<br>';
print'<a href="shop_list.php">商品一覧に戻る</a>';
exit();
}
}
// カートに商品を追加する
$cart[]=$pro_code;
// 数量１をいれている
$kazu[]=1;
// あとで取り出せるようにセッションにカートを保管する
$_SESSION['cart']=$cart;
$_SESSION['kazu']=$kazu;

}
catch(Exception $e)
{
	print 'ただいま障害により大変ご迷惑をお掛けしております。';
	exit();
}

?>

カートに追加しました。<br />
<br />
<a href="shop_list.php">商品一覧に戻る</a>

</body>
</html>