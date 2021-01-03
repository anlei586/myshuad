<?php

require './utils.php';
require './conn.php';

$param_action = isset($_GET['action'])?$_GET['action']:0;
$param_v = isset($_GET['v'])?$_GET['v']:"";
$param_f = isset($_GET['f'])?$_GET['f']:"";
$param_id = isset($_GET['id'])?$_GET['id']:"";


if(!empty($param_f)){
	$dbh = new PDO($dsn, $user, $pass);
	if($param_action == 1){//更新
		$sql = "UPDATE sd_wc_order_stats SET {$param_f}='{$param_v}' WHERE order_id='{$param_id}'";
		//echo $sql;
		$cx = $dbh->query($sql);
		$dbh = null;
		echo '{"ret":200}';
		return;
	}else if($param_action == 2){//更新
		$sql = "UPDATE sd_wc_order_stats SET {$param_f}='{$param_v}' WHERE order_id in ({$param_id})";
		$cx = $dbh->query($sql);
		$dbh = null;
		echo '{"ret":200}';
		return;
	}
	echo '{"ret":201,"msg":"没有收到action参数"}';
	return;
}
?>