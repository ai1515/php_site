<?php
// IDを自動で変えてセッションハイジャック対策
	session_start();
	session_regenerate_id(true);
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

require_once('../common/common.php');

$post=sanitize($_POST);
// ①画面に注文を受け付けた旨の表示をする
// ②お客様にお礼のメールを自動送信する
// ③お店側には「注文あり」のメールを送信する
// ④データベースに注文データを保存する

$onamae=$post['onamae'];
$email=$post['email'];
$postal1=$post['postal1'];
$postal2=$post['postal2'];
$address=$post['address'];
$tel=$post['tel'];
$chumon=$post['chumon'];
$pass=$post['pass'];
$danjo=$post['danjo'];
$birth=$post['birth'];

print $onamae.'様<br />';
print 'ご注文ありがとうござました。<br />';
print $email.'にメールを送りましたのでご確認ください。<br />';
print '商品は以下の住所に発送させていただきます。<br />';
print $postal1.'-'.$postal2.'<br />';
print $address.'<br />';
print $tel.'<br />';
// 最初だけ空っぽをコピーする(初期値)
// \nはメール内の改行、n.=は連結

$honbun='';
$honbun.=$onamae."様\n\nこのたびはご注文ありがとうございました。\n";
$honbun.="\n";
$honbun.="ご注文商品\n";
$honbun.="--------------------\n";

$cart=$_SESSION['cart'];
$kazu=$_SESSION['kazu'];
$max=count($cart);

$dsn='mysql:dbname=shop;host=localhost;charset=utf8';
$user='root';
$password='';
$dbh=new PDO($dsn,$user,$password);
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

for($i=0;$i<$max;$i++)
{
	$sql='SELECT name,price FROM mst_product WHERE code=?';
	$stmt=$dbh->prepare($sql);
	$data[0]=$cart[$i];
	$stmt->execute($data);

	$rec=$stmt->fetch(PDO::FETCH_ASSOC);

	$name=$rec['name'];
	$price=$rec['price'];
	$kakaku[]=$price;
	$suryo=$kazu[$i];
	$shokei=$price*$suryo;

	$honbun.=$name.' ';
	$honbun.=$price.'円 x ';
	$honbun.=$suryo.'個 = ';
	$honbun.=$shokei."円\n";
}
// 別のデータをDBに保存する処理をしている間、他のデータの処理が通らないようにする(テーブルロック)
$sql='LOCK TABLES dat_sales WRITE,dat_sales_product WRITE,dat_member WRITE';
$stmt=$dbh->prepare($sql);
$stmt->execute();
// 注文データを追加する、今追加したばかりの注文コードを取得する、注文明細データを追加する、商品点数と同じ回数のループを組んで、次々に追加する。

// もし今回だけの注文だったら、コードを今まで通り0でセット。会員登録もする場合は、会員情報を作成後、LAST_INSERT_ID()でIDを作成
$lastmembercode=0;
if($chumon=='chumontouroku')
{
	$sql='INSERT INTO dat_member (password,name,email,postal1,postal2,address,tel,danjo,born) VALUES (?,?,?,?,?,?,?,?,?)';
	$stmt=$dbh->prepare($sql);
	// $data=array();：配列変数に既に入っているデータをクリアする

	$data=array();
	$data[]=md5($pass);
	$data[]=$onamae;
	$data[]=$email;
	$data[]=$postal1;
	$data[]=$postal2;
	$data[]=$address;
	$data[]=$tel;
	if($danjo=='dan')
	{
		$data[]=1;
	}
	else
	{
		$data[]=2;
	}
	$data[]=$birth;
	$stmt->execute($data);
// SELECT LAST_INSERT_ID():直近に発番された番号を取得する
	// 今取得した注文コードを$lastcodeに入れる

	$sql='SELECT LAST_INSERT_ID()';
	$stmt=$dbh->prepare($sql);
	$stmt->execute();
	$rec=$stmt->fetch(PDO::FETCH_ASSOC);
	$lastmembercode=$rec['LAST_INSERT_ID()'];
}

$sql='INSERT INTO dat_sales (code_member,name,email,postal1,postal2,address,tel) VALUES (?,?,?,?,?,?,?)';
$stmt=$dbh->prepare($sql);
$data=array();
$data[]=$lastmembercode;
$data[]=$onamae;
$data[]=$email;
$data[]=$postal1;
$data[]=$postal2;
$data[]=$address;
$data[]=$tel;
$stmt->execute($data);

$sql='SELECT LAST_INSERT_ID()';
$stmt=$dbh->prepare($sql);
$stmt->execute();
$rec=$stmt->fetch(PDO::FETCH_ASSOC);
$lastcode=$rec['LAST_INSERT_ID()'];

// 配列変数$kakakuに価格を保存しておいたので、ここで読み出す。商品明細を追加する
for($i=0;$i<$max;$i++)
{
	$sql='INSERT INTO dat_sales_product (code_sales,code_product,price,quantity) VALUES (?,?,?,?)';
	$stmt=$dbh->prepare($sql);
	$data=array();
	$data[]=$lastcode;
	$data[]=$cart[$i];
	$data[]=$kakaku[$i];
	$data[]=$kazu[$i];
	$stmt->execute($data);
}

// テーブルロックの解除
$sql='UNLOCK TABLES';
$stmt=$dbh->prepare($sql);
$stmt->execute();

$dbh=null;

if($chumon=='chumontouroku')
{
	print '会員登録が完了いたしました。<br />';
	print '次回からメールアドレスとパスワードでログインしてください。<br />';
	print 'ご注文が簡単にできるようになります。<br />';
	print '<br />';
}

$honbun.="送料は無料です。\n";
$honbun.="--------------------\n";
$honbun.="\n";
$honbun.="代金は以下の口座にお振込ください。\n";
$honbun.="ろくまる銀行 やさい支店 普通口座 １２３４５６７\n";
$honbun.="入金確認が取れ次第、梱包、発送させていただきます。\n";
$honbun.="\n";

if($chumon=='chumontouroku')
{
	$honbun.="会員登録が完了いたしました。\n";
	$honbun.="次回からメールアドレスとパスワードでログインしてください。\n";
	$honbun.="ご注文が簡単にできるようになります。\n";
	$honbun.="\n";
}

$honbun.="□□□□□□□□□□□□□□\n";
$honbun.="　～安心野菜のろくまる農園～\n";
$honbun.="\n";
$honbun.="○○県六丸郡六丸村123-4\n";
$honbun.="電話 090-6060-xxxx\n";
$honbun.="メール info@rokumarunouen.co.jp\n";
$honbun.="□□□□□□□□□□□□□□\n";
// 動作テスト
// print '<br />';
// print nl2br($honbun);

// メールタイトル
$title='ご注文ありがとうございます。';
// 送信元
$header='From:info@rokumarunouen.co.jp';
$honbun=html_entity_decode($honbun,ENT_QUOTES,'UTF-8');
mb_language('Japanese');
mb_internal_encoding('UTF-8');
// メールを送信する命令($emailがお客さんのメールアドレス)
mb_send_mail($email,$title,$honbun,$header);

$title='お客様からご注文がありました。';
// 送信元(お客様のメールアドレス)
$header='From:'.$email;
$honbun=html_entity_decode($honbun,ENT_QUOTES,'UTF-8');
mb_language('Japanese');
mb_internal_encoding('UTF-8');
// メールを送信する命令(送信先をお店のメールアドレスに)
mb_send_mail('info@rokumarunouen.co.jp',$title,$honbun,$header);

}
catch (Exception $e)
{
	print 'ただいま障害により大変ご迷惑をお掛けしております。';
	exit();
}

?>

<br />
<a href="shop_list.php">商品画面へ</a>

</body>
</html>