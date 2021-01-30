<?php
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:*'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with,Origin,Authorization,email,token');
header('Access-Control-Max-Age: 1728000');

if($_SERVER['REQUEST_METHOD']=='OPTIONS') return;

require './conn.php';
$dbh = new PDO($dsn, $user, $pass);


$action = isset($_GET['ac'])?$_GET['ac']:"";
if($action==1){//登录
	//http://192.168.123.85/api/auth.php?ac=1&email=1001@fb.com&passwd=41003494
	require './sendmail.php';
	$toemail = isset($_GET['email'])?$_GET['email']:"";
	$topasswd = isset($_GET['passwd'])?$_GET['passwd']:"";
	if(!empty($toemail) && !empty($topasswd)){
		$isEmail = filter_var($toemail, FILTER_VALIDATE_EMAIL);
		if(!$isEmail){
			exit(retmsg(103,"Email format error"));
		}
		$sql = 'SELECT * FROM mission_user where email="'.$toemail.'"';
		$result = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(count($result)>0){//已存在，那么去对比密码
			$uid = $result[0]['id'];
			$paypal = $result[0]['paypal'];
			$passwd = strtoupper($result[0]['passwd']);
			$topasswd_md5 = strtoupper(md5($topasswd));
			if($passwd == $topasswd_md5){
				$emailtoken=createToken($toemail);
				$str = '{"ret":0,"uid":"'.$uid.'","email":"'.$toemail.'","paypal":"'.$paypal.'","token":"'.$emailtoken.'"}';
				exit($str);
			}else{
				exit(retmsg(106,"password error !"));
			}
		}else{
			exit(retmsg(109,"email error !"));
		}
	}else{
		exit(retmsg(105,"not email or not passwd ?"));
	}

}else if($action==2){//注册
	//http://192.168.123.85/api/auth.php?ac=2&email=1001@fb.com
	require './sendmail.php';
	$toemail = isset($_GET['email'])?$_GET['email']:"";
	$incode = isset($_GET['incode'])?$_GET['incode']:"";
	if(!empty($toemail)){
		$isEmail = filter_var($toemail, FILTER_VALIDATE_EMAIL);
		if(!$isEmail){
			exit(retmsg(103,"Email format error"));
		}

		$sql = 'SELECT id FROM mission_user where email="'.$toemail.'"';
		$result = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(count($result)>0){//已存在
			exit(retmsg(102,"This email has been registered"));
		}

		$smm_sql = 'SELECT value from mission_config where `key`="share_make_money"';
		$smm_res = $dbh->query($smm_sql)->fetchAll(PDO::FETCH_ASSOC);
		$share_make_money = $smm_res[0]['value'];

		$title1 = "Make Money";
		$title2 = "Make Money Password !";
		$newpwd = rand(10000000,99999999);
		$pwdtxt = "You Password: <b>".$newpwd.'</b>, <a href="'.$share_make_money.'">click this go to login</a>';

		$newpwd_md5 = strtoupper(md5($newpwd));

		$_date = date("Y-m-d H:i:s");
		//否则插入
		$sql = "insert INTO mission_user(email, passwd, parent_id, date) VALUES('{$toemail}','{$newpwd_md5}','{$incode}','{$_date}')";
		$cx = $dbh->query($sql);


/*发送恭喜邮件给客户**/
$gongxi_html = 'Welcome to make money.Our company mainly works with Shopee, Lazada and Amazon.Buy products to boost your site\'s sales rankings.We offer the following two ways of cooperation:<br/>&nbsp;&nbsp;&nbsp;&nbsp;1. By buying products.3 days later confirm receipt and give praise.We will refund the money to your account in the form of points.And we will give you a 10% commission of the total amount.You can either withdraw cash or continue to purchase products in the form of credits to earn commissions (after credits are deposited into the account, we will give a certain percentage of interest as compensation).<br/>&nbsp;&nbsp;&nbsp;&nbsp;2. Earn a certain amount of team commission through the development team.For specific cooperation methods, please refer to the relevant instructions of the platform or consult customer service.Thank you for your cooperation.';
mysendmail($title1, $title1, $toemail, $gongxi_html);




		
		echo mysendmail($title1, $title2, $toemail, $pwdtxt);

		duifcoupon($toemail, 10);

		exit();
		die();
		return;
	}else{
		exit(retmsg(101,"not email"));
	}
}else if($action==3){//忘记密码
	//http://192.168.123.85/api/auth.php?ac=3&email=1001@fb.com
	require './sendmail.php';
	$toemail = isset($_GET['email'])?$_GET['email']:"";
	if(!empty($toemail)){
		$sql = 'SELECT id FROM mission_user where email="'.$toemail.'"';
		$result = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(count($result)>0){//已存在


			$title1 = "Make Money";
			$title2 = "Make Money New Password !";
			$newpwd = rand(10000000,99999999);
			$pwdtxt = "You New Password: <b>".$newpwd.'</b>';
			$newpwd_md5 = strtoupper(md5($newpwd));
			$_date = date("Y-m-d H:i:s");
			$sql = "UPDATE mission_user set passwd='{$newpwd_md5}' where email='{$toemail}'";
			$cx = $dbh->query($sql);
			echo mysendmail($title1, $title2, $toemail, $pwdtxt);
			exit();
			die();
			return;
		}else{//不存在注册过的邮箱
			exit(retmsg(104,"not reg email"));
		}
	}else{
		exit(retmsg(101,"not email"));
	}
}else if($action==4){//修改密码

}else if($action==5){//验证token登录
	//http://192.168.123.85/api/auth.php?ac=5
	$header_arr = em_getallheaders();
	//var_dump($header_arr);
	$token=createToken($header_arr["Email"]);
	//echo "token:".$token;
	if($token==$header_arr["Token"])
	{
		exit(retmsg(0,"success"));
	}else{
		exit(retmsg(107,"logout"));
	}
}



function mysendmail($title1, $title2, $toemail, $content){
	//	exit('{"ret":0,"msg":"'.$content.'"}');
	return sendmail($title1, $title2, $toemail, $content);
}


exit('{"ret":100,"msg":"not ac param"}');

?>