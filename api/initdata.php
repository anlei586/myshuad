<?php
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:*'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with,Origin,Authorization,email,token');
header('Access-Control-Max-Age: 1728000');

require './conn.php';
$dbh = new PDO($dsn, $user, $pass);


$action = isset($_GET['ac'])?$_GET['ac']:"";
if($action==1){//取初始数据
	//http://192.168.123.85/api/initdata.php?ac=1
	
	if(verificationToken())
	{
		//配置
		$config_sql = 'SELECT * FROM mission_config';
		$config_result = $dbh->query($config_sql)->fetchAll(PDO::FETCH_ASSOC);

		//公告
		$notice_sql = 'SELECT * FROM mission_notice';
		$notice_result = $dbh->query($notice_sql)->fetchAll(PDO::FETCH_ASSOC);

		//任务商品
		$mission_sql = 'SELECT * FROM mission_mission';
		$mission_result = $dbh->query($mission_sql)->fetchAll(PDO::FETCH_ASSOC);
		
		$arr = array("config"=>$config_result, "notice"=>$notice_result, "mission"=>$mission_result);
		
		$_str = json_encode($arr);
		exit($_str);

	}else{
		exit(retmsg(108,"logout"));
	}
}

//验证token登录
function verificationToken(){
	$header_arr = em_getallheaders();
	$token=createToken($header_arr["Email"]);
	return $token==$header_arr["Token"];
}


exit('{"ret":100,"msg":"no ac param"}');

?>