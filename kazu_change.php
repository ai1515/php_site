<?php
// 数だけ変更して、すぐshop_cartlookに帰る画面
// セッション開始
	session_start();
	session_regenerate_id(true);

	// インクルードする(共通関数を読み込む)
	require_once('../common/common.php');

	// $_POSTに安全対策を施して$postにコピー
	$post=sanitize($_POST);

	// 商品の種類の数を$postから$maxにコピー
	$max=$post['max'];
	// 商品の数だけ回るforループ
	for($i=0;$i<$max;$i++)
	{
		// 半角数字じゃないときの分岐
		if(preg_match("/\A[0-9]+\z/",$post['kazu'.$i])==0)
		{
			print '数量に誤りがあります。';
			print'<a href="shop_cartlook.php">カートに戻る</a>';
			exit();
		}

		// 0と10以上は入力できないようにする
		if($post['kazu'.$i]<1||10<$post['kazu'.$i])
		{
			print '数量は必ず1個以上、10個までです。';
			print'<a href="shop_cartlook.php">カートに戻る</a>';
			exit();
		}


		// 前の画面で入力された数量を配列に入れていく(kazu0,kazu1,kazu2...)
		$kazu[]=$post['kazu'.$i];
	}
	$cart=$_SESSION['cart'];
	// 削除した際順番がズレて意図しないものを消さないよう、for文を--にする
	for($i=$max;0<=$i;$i--){
		// もしsakujoに値が入っていたら削除する
		if(isset($_POST['sakujo'.$i])==true)
		{
			// 削除する命令(配列変数名、何番目を削除するか、何個削除するか)
			array_splice($cart,$i,1);
			array_splice($kazu,$i,1);
		}
		// 動作テスト
		// var_dump($cart);
	}

	// セッションに保管する
	$_SESSION['cart']=$cart;
	$_SESSION['kazu']=$kazu;

	// shop_cartlookに戻る
	header('Location:shop_cartlook.php');
?>