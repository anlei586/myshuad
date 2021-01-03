<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>修改密码</title>
    <script type="text/javascript">
    	
   		function replace(str, flag, rep){
			str = str.split(flag).join(rep);
			return str;
		}
		
		function onOk(){
			//alert("A");
			var tip_div = document.getElementById("tip_div");
				tip_div.innerHTML = "";
			var user_txt = document.getElementById("user_txt");
			var curr_pwd_txt = document.getElementById("curr_pwd_txt");
			
			var new_pwd_txt = document.getElementById("new_pwd_txt");
			var new_repwd_txt = document.getElementById("new_repwd_txt");
			
			if(!user_txt.value || user_txt.value.length<=5){
				//tip_div.innerHTML = "用户名长度要大于5位，且不能为空";
				//return;
			}
			if(!curr_pwd_txt.value || curr_pwd_txt.value.length<=5){
				tip_div.innerHTML = "原密码长度要大于5位，且不能为空";
				return;
			}
			if(!new_pwd_txt.value || new_pwd_txt.value.length<=5){
				tip_div.innerHTML = "新密码长度要大于5位，且不能为空";
				return;
			}
			if(new_pwd_txt.value != new_repwd_txt.value){
				tip_div.innerHTML = "新密码与重复密码要相同";
				return;
			}
			tip_div.innerHTML = "填写正确，发送请求中。";
			
			user_txt.value =  replace(user_txt.value, " ", "");
			curr_pwd_txt.value =  replace(curr_pwd_txt.value, " ", "");
			new_pwd_txt.value =  replace(new_pwd_txt.value, " ", "");
			new_repwd_txt.value =  replace(new_repwd_txt.value, " ", "");
			
			
			var form1 = document.getElementById("form1");
			form1.submit();
		}
   		
    </script>
    <style>
		.input {
			background-color: #fafafa;
			font-size: 16px;
			box-sizing: border-box;
			line-height: 32px;
			border-radius: 8px;
   			width: 100%;
			padding: 10px 15px;
			border: solid 1px #e3e3e3;
    		overflow: hidden;
			margin-top:8px;
		}
	</style>
</head>
<body>
<center><div style="text-align: center; width: 100%; background-color: #FFFFFF; padding-top: 10px; top: 0px; padding-bottom: 10px; z-index: 2; ">修改密码</div></center>
<?php
ini_set('display_errors',1); //错误信息 
ini_set('display_startup_errors',1); //php启动错误信息 
error_reporting(-1); //打印出所有的 错误信息

require './utils.php';

if(isset($_GET["action"])){
	try{
		$mobile = $_REQUEST["user_txt"];
		$passwd = $_REQUEST["curr_pwd_txt"];
		$newpasswd = $_REQUEST["new_pwd_txt"];
		
		$mobile = str_replace(" ","", $mobile);
		$passwd = str_replace(" ","", $passwd);
		$newpasswd = str_replace(" ","", $newpasswd);
		
		$mobile = str_replace("'","", $mobile);
		$passwd = str_replace("'","", $passwd);
		$newpasswd = str_replace("'","", $newpasswd);
		
		$mobile = str_replace('"','', $mobile);
		$passwd = str_replace('"','', $passwd);
		$newpasswd = str_replace('"','', $newpasswd);
		
	} catch (Exception $e) {
		echo "REQUEST参数错误";
		die();
	}
	$getmd5passwd = strtoupper(md5($passwd));
	$getmd5newpasswd = strtoupper(md5($newpasswd));
	
	//////////////////////
	
	
	require './conn.php';
	
	try {
		$dbh = new PDO($dsn, $user, $pass);
		$sql = 'SELECT * from mission_user where email="'.$mobile.'"';
		$cx = $dbh->query($sql);
		$a = $cx->fetch();
		if($a['passwd'] == $getmd5passwd){//原密码正确
			$sql = 'UPDATE mission_user SET passwd="'.$getmd5newpasswd.'" WHERE email="'.$mobile.'"';
			$cx = $dbh->query($sql);
			echo "<center>密码修改成功</center>";
			die();
		}else{
			echo '<center><a href="javascript:window.history.go(-1);">密码修改失败，也许是原密码不对，点击返回</a></center>';
			die();
		}
		//
		$dbh = null;
	} catch (PDOException $e) {
		echo "数据库连接出问题";
		die();
	}
}
?>
<br/>
<form id="form1" name="form1" method="post" action="?action=update">
  <input type="text" name="user_txt" id="user_txt" placeholder="用户名" class="input"><br/> 
  <input type="text" name="curr_pwd_txt" id="curr_pwd_txt" placeholder="原密码" class="input"><br/>
  <input type="text" name="new_pwd_txt" id="new_pwd_txt" placeholder="新密码" class="input"><br/>
  <input type="text" name="new_repwd_txt" id="new_repwd_txt" placeholder="重复新密码" class="input"><br/>
  <input name="确定" value="确定" type="button" onClick="onOk()" style="display: inline-block; height: 38px; line-height: 38px; padding: 0 18px; margin-top:8px; background-color: #1670c1; color: #fff; white-space: nowrap; text-align: center; font-size: 14px; border: none; border-radius: 2px; cursor: pointer; width:100%;">
</form>

<div id="tip_div" style="color:#FF0000">
</div>
</body>
</html>

