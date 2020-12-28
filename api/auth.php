<?php

require './conn.php';
$dbh = new PDO($dsn, $user, $pass);

$action = isset($_REQUEST['ac'])?$_REQUEST['ac']:"";
if($action==1){//登录
	//http://192.168.123.85/api/auth.php?ac=1&email=1001@fb.com&passwd=41003494
	require './sendmail.php';
	$toemail = isset($_REQUEST['email'])?$_REQUEST['email']:"";
	$topasswd = isset($_REQUEST['passwd'])?$_REQUEST['passwd']:"";
	if(!empty($toemail) && !empty($topasswd)){
		$isEmail = filter_var($toemail, FILTER_VALIDATE_EMAIL);
		if(!$isEmail){
			exit(retmsg(103,"Email format error"));
		}
		$sql = 'SELECT * FROM mission_user where email="'.$toemail.'"';
		$result = $dbh->query($sql)->fetchAll();
		if(count($result)>0){//已存在，那么去对比密码
			$passwd = strtoupper($result[0]['passwd']);
			$topasswd_md5 = strtoupper(md5($topasswd));
			if($passwd == $topasswd_md5){
				$str = '{"ret":0,"email":"'.$toemail.'","token":"'.$passwd.'"}';
				exit($str);
			}else{
				exit(retmsg(106,"Incorrect email or password !"));
			}
		}
	}else{
		exit(retmsg(105,"not email or not passwd ?"));
	}

}else if($action==2){//注册
	//http://192.168.123.85/api/auth.php?ac=2&email=1001@fb.com
	require './sendmail.php';
	$toemail = isset($_REQUEST['email'])?$_REQUEST['email']:"";
	if(!empty($toemail)){
		$isEmail = filter_var($toemail, FILTER_VALIDATE_EMAIL);
		if(!$isEmail){
			exit(retmsg(103,"Email format error"));
		}

		$sql = 'SELECT id FROM mission_user where email="'.$toemail.'"';
		$result = $dbh->query($sql)->fetchAll();
		if(count($result)>0){//已存在
			exit(retmsg(102,"This email has been registered"));
		}

		$title1 = "Make Money";
		$title2 = "Make Money Password !";
		$newpwd = rand(10000000,99999999);
		$pwdtxt = "You Password: <b>".$newpwd.'</b>';

		$newpwd_md5 = strtoupper(md5($newpwd));

		$_date = date("Y-m-d H:i:s");
		//否则插入
		$sql = "insert INTO mission_user(email, passwd, date) VALUES('{$toemail}','{$newpwd_md5}','{$_date}')";
		$cx = $dbh->query($sql);
		
			exit($pwdtxt);
		//sendmail($title1, $title2, $toemail, $pwdtxt);
	}else{
		exit(retmsg(101,"not email"));
	}
}else if($action==3){//忘记密码
	//http://192.168.123.85/api/auth.php?ac=3&email=1001@fb.com
	require './sendmail.php';
	$toemail = isset($_REQUEST['email'])?$_REQUEST['email']:"";
	if(!empty($toemail)){
		$sql = 'SELECT id FROM mission_user where email="'.$toemail.'"';
		$result = $dbh->query($sql)->fetchAll();
		if(count($result)>0){//已存在
			$title1 = "Make Money";
			$title2 = "Make Money New Password !";
			$newpwd = rand(10000000,99999999);
			$pwdtxt = "You New Password: <b>".$newpwd.'</b>';
			$newpwd_md5 = strtoupper(md5($newpwd));
			$_date = date("Y-m-d H:i:s");
			$sql = "UPDATE mission_user set passwd='{$newpwd_md5}' where email='{$toemail}'";
			$cx = $dbh->query($sql);
				exit($pwdtxt);
			//sendmail($title1, $title2, $toemail, $pwdtxt);
		}else{//不存在注册过的邮箱
			exit(retmsg(104,"not reg email"));
		}
	}else{
		exit(retmsg(101,"not email"));
	}
}else if($action==4){//修改密码
}


?>