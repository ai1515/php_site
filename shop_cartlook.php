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

	// カート内に商品が存在していたらコピーする(もともとないときにコピーするとエラーになってしまうため)
	if(isset($_SESSION['cart'])==true)
	{
$cart=$_SESSION['cart'];
// 数量変更した場合にもここで値を取り出す
$kazu=$_SESSION['kazu'];
// $max：$cart配列に入っている値の数をカウントできる
$max=count($cart);
	}
	// カート内に商品が存在していなかったら0にする
	else{
		$max=0;
	}

if($max==0)
{
	print'カートに商品が入っていません。<br>';
	print'<br>';
	print'<a href="shop_list.php">商品一覧へ戻る<a>';
	exit();
}

// DBに接続
$dsn='mysql:dbname=shop;host=localhost;charset=utf8';
$user='root';
$password='';
$dbh=new PDO($dsn,$user,$password);
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

// 配列の繰り返し表示：foreach(配列名、添え字、データ)
foreach($cart as $key=>$val)
{
	// 配列$cartに入っているデータを取り出す
	$sql='SELECT code,name,price,gazou FROM mst_product WHERE code=?';
	$stmt=$dbh->prepare($sql);
	$data[0]=$val;
	$stmt->execute($data);

	$rec=$stmt->fetch(PDO::FETCH_ASSOC);

	$pro_name[]=$rec['name'];
	$pro_price[]=$rec['price'];
	if($rec['gazou']=='')
	{
		// 画像がなかったら何も表示しない
		$pro_gazou[]='';
	}
	else
	{
		$pro_gazou[]='<img src="../product/gazou/'.$rec['gazou'].'">';
	}
}
$dbh=null;

}
catch(Exception $e)
{
	print'ただいま障害により大変ご迷惑をお掛けしております。';
	exit();
}

?>

カートの中身<br />
<br />
<form method="post" action="kazu_change.php">
	<table border="1">
		<tr>
			<td>商品</td>
			<td>商品画像</td>
			<td>価格</td>
			<td>数量</td>
			<td>小計</td>
			<td>削除</td>
</tr>
	
<!-- 最初はi=0で、配列の数より少ない場合、ループを繰り返す (=配列の値の数だけループする)-->
<?php for($i=0;$i<$max;$i++)
	{
?>
<tr>
	<td><?php print $pro_name[$i]; ?></td>
	<td><?php print $pro_gazou[$i]; ?></td>
	<td><?php print $pro_price[$i]; ?>円</td>

	<!--nameには商品ごとに異なる名前を付けなければいけないため、ループでkazu0,kazu1,kazu2…を作り出す -->
	<td><input type="text" name="kazu<?php print $i;?>" value="<?php print $kazu[$i]; ?>"></td>
	<td><?php print $pro_price[$i]*$kazu[$i]; ?>円</td>
	<!--ループでkazu0,kazu1,kazu2…を作り出す -->
	<td><input type="checkbox" name="sakujo<?php print $i; ?>"></td>
	</tr>
<?php
	}
?>
</table>
<!-- カート内商品の種類の数をhiddenで渡す -->
<input type="hidden" name="max" value="<?php print $max;?>">
<input type="submit" value="数量変更"><br />
<input type="button" onclick="history.back()" value="戻る">
</form>
<br>
<a href="shop_form.html">ご購入手続きへ進む</a><br>

<?php
	if(isset($_SESSION["member_login"])==true)
	{
		print'<a href="shop_kantan_check.php">会員かんたん注文へ進む</a><br>';
	}
?>

</body>
</html>