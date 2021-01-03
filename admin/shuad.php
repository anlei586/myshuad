<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>管理员登录</title>
    <meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="./layui/css/layui.css">
</head>
<body>
<center>
<br/><p><h1>管理员登录</h1></p><br/>
<form class="layui-form" action="#" target="_self" method="post">
	<div class="layui-form-item" style="padding: 20px;">
		<input type="text" name="mobile" required  lay-verify="required" placeholder="请输入用户名" autocomplete="off" class="layui-input" style="text-align:center; width: 200px; "><br/>
		<input type="password" name="passwd" required lay-verify="required" placeholder="请输入密码" autocomplete="off" class="layui-input"  style="text-align:center; width: 200px;">
	</div>
  
	<div class="layui-form-item" align="center">
		<button class="layui-btn" lay-submit lay-filter="formDemo">立即登陆</button>
	</div>
</form>
</center>
<script src="./layui/layui.all.js"></script>
<script>
//一般直接写在一个js文件中
layui.use(['form'], function(){
  var layer = layui.layer
  ,form = layui.form;

  //layer.msg('Hello World');
});

</script> 

<?php

ini_set('display_errors', 1); //错误信息 
ini_set('display_startup_errors', 1); //php启动错误信息 
error_reporting(-1); //打印出所有的 错误信息


require './conn.php';



session_start();
if (isset($_SESSION["admin"]) && $_SESSION["admin"] === true) {
	if(empty($debug)){
		echo "您未登陆";
		//header('Location: /anleiorsys/admin/main.php');
		header('Location:./main.php');
	}
}

try{
	if( isset($_REQUEST["mobile"]) && isset($_REQUEST["passwd"]) ){
		
		$mobile = $_REQUEST["mobile"];
		$passwd = $_REQUEST["passwd"];
	
		$mobile = str_replace(" ","", $mobile);
		$passwd = str_replace(" ","", $passwd);
		
		$mobile = str_replace("'","", $mobile);
		$passwd = str_replace("'","", $passwd);
		
		$mobile = str_replace('"','', $mobile);
		$passwd = str_replace('"','', $passwd);

		$ismang = false;

		
		$getmd5passwd = strtoupper(md5($passwd));
		
		
		try {
			$dbh = new PDO($dsn, $user, $pass);
			$sql = 'SELECT email,passwd from mission_user where email="'.$mobile.'"';
			$cx = $dbh->query($sql);
			$a = $cx->fetch();
			if(!empty($a) && $a['passwd'] == $getmd5passwd){
				$_SESSION["email"]=$mobile;
				$_SESSION["admin"] = true;
				header('Location: ./main.php');
			}else{
				echo "<script>layer.msg('用户名密码出错');</script>";
			}
			$dbh = null;
		} catch (PDOException $e) {
			echo '<script>layer.msg("数据库连接出问题:");</script>';
			echo $e;
		}
	}
	
} catch (Exception $e) {
    echo "<script>layer.msg('参数错误');</script>";
	
}
