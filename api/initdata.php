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
	$gt = verificationToken();
	if($gt['passport'])
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

		//自己订单列表
		$me_order_post_id_sql = 'SELECT post_id FROM sd_postmeta where meta_key="_billing_email" and meta_value="'.$gt['Email'].'"';
		$me_order_post_id_result = $dbh->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
		$me_order_post_id_str="";
		$me_order_post_id_count = count($me_order_post_id_result);
		for($i=0;$i<$me_order_post_id_count;$i++){
			$isEndStr = $i==$me_order_post_id_count-1 ? "" : ",";
			$me_order_post_id_str .= $me_order_post_id_result[$i]['post_id'] . $isEndStr;
		}
		$me_order_post_id_sql = 'SELECT * FROM sd_wc_order_stats where order_id in("'.$me_order_post_id_str.'")';
		$me_order_result = $dbh->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);

		
		$dateArr = array("date"=>date("Y-m-d H:i:s"), "time"=>time());
		$arr = array("date"=>$dateArr, "config"=>$config_result, "notice"=>$notice_result, "mission"=>$mission_result, "meorder"=>$me_order_result);
		
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
	$header_arr['passport'] = $token==$header_arr["Token"];
	return $header_arr;
}


exit('{"ret":100,"msg":"no ac param"}');

?>