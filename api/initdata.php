<?php
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:*'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with,Origin,Authorization,email,token');
header('Access-Control-Max-Age: 1728000');

if($_SERVER['REQUEST_METHOD']=='OPTIONS') return;

require './conn.php';
$dbh = new PDO($dsn, $user, $pass);

$fofel_arr = array('A','B','C','D');
$fofel_i = 0;

$action = isset($_GET['ac'])?$_GET['ac']:"";
if($action==1){//取初始数据
	//http://192.168.123.85/api/initdata.php?ac=1
	$start_time = time();
	$gt = verificationToken();
	if($gt['passport'])
	{
		//配置
		$config_sql = 'SELECT * FROM mission_config';
		$config_result = $dbh->query($config_sql)->fetchAll(PDO::FETCH_ASSOC);

		//公告
		$notice_sql = 'SELECT * FROM mission_notice order by id DESC';
		$notice_result = $dbh->query($notice_sql)->fetchAll(PDO::FETCH_ASSOC);

		//任务商品
		$mission_sql = 'SELECT * FROM mission_mission order by price ASC';
		$mission_result = $dbh->query($mission_sql)->fetchAll(PDO::FETCH_ASSOC);

		//自己
		$meuser_sql = 'SELECT * FROM mission_user where email="'.$gt['Email'].'"';
		$meuser_result = $dbh->query($meuser_sql)->fetchAll(PDO::FETCH_ASSOC);
		$meuser_result = $meuser_result[0];
		$meuser_result['passwd']='';

		//自己订单列表
		$me_order_result = findOrderFromEmail($gt['Email']);

		//团队的订单
		$me_team_emeun_arr = findOrderFromEmailLoop($gt['Email']);

		$end_time = time();
		$dateArr = array("date"=>date("Y-m-d H:i:s"), "time"=>time(), "start_time"=>$start_time, "end_time"=>$end_time);
		$arr = array(
			"date"=>$dateArr, "config"=>$config_result,
			"notice"=>$notice_result, "mission"=>$mission_result, "meorder"=>$me_order_result,
			"meteamorder"=>$me_team_emeun_arr, "meuser"=>$meuser_result
		);
		
		$_str = json_encode($arr);
		exit($_str);

	}else{
		exit(retmsg(108,"logout"));
	}
}

function filterStar($val){
	$_len = mb_strlen($val);
	if($_len >= 3){
		$_elen= intval($_len * 0.4);
		$_selen = $_len-$_elen;
		$_selen2= intval($_selen/2);
		$_msg1 = mb_substr($val, 0, $_selen2);
		$_msg2 = mb_substr($val, -$_selen2, $_selen2);
		$val = $_msg1.'******'.$_msg2;
	}
	return $val;
}

function findOrderFromEmail($email) {
	global $dbh;
	//查某个email的所有订单ID
	$me_order_post_id_sql = 'SELECT post_id FROM sd_postmeta where meta_key="_billing_email" and meta_value="'.$email.'"';
	$me_order_post_id_result = $dbh->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
	//组合所有订单ID
	$me_order_post_id_str="";
	$me_order_post_id_count = count($me_order_post_id_result);
	for($i=0;$i<$me_order_post_id_count;$i++){
		$isEndStr = $i==$me_order_post_id_count-1 ? "" : ",";
		$me_order_post_id_str .= '"'.$me_order_post_id_result[$i]['post_id'] .'"'. $isEndStr;
	}
	if(empty($me_order_post_id_str)){
	    return array();
	}
	//查所有符合条件的订单
	$me_order_post_id_sql = 'SELECT * FROM sd_wc_order_stats where order_id in('.$me_order_post_id_str.')';
	$me_order_result = $dbh->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
	return $me_order_result;
}

function findOrderFromEmailLoop($email) {
	global $dbh;
	global $fofel_arr;
	global $fofel_i;
	$fofel_i++;
	//查自己的UID
	$me_order_post_id_sql = 'SELECT id from mission_user where email="'.$email.'"';
	$me_order_post_id_result = $dbh->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
	$me_uid = $me_order_post_id_result[0]["id"];
	//查自己所有员工的email
	$me_team_emeun_email_sql = 'SELECT email from mission_user where parent_id='.$me_uid;
	$me_team_emeun_email_result = $dbh->query($me_team_emeun_email_sql)->fetchAll(PDO::FETCH_ASSOC);
	//查员工的所有订单
	$me_team_emeun_email_count = count($me_team_emeun_email_result);
	$me_team_emeun_arr = array();
	for($i=0;$i<$me_team_emeun_email_count;$i++){
		$_email = $me_team_emeun_email_result[$i]['email'];
		$md5_email = md5($_email);
		$star_email = filterStar($_email);
		$_ret = findOrderFromEmail($_email);
		$me_team_emeun_arr[$md5_email]['email'] = $star_email;
		$me_team_emeun_arr[$md5_email]['level'] = $fofel_arr[$fofel_i];
		$me_team_emeun_arr[$md5_email]['order'] = $_ret;
		$me_team_emeun_arr[$md5_email]['childorder'] = findOrderFromEmailLoop($_email);
		$fofel_i--;
	}
	if(count($me_team_emeun_arr)<=0) return null;
	return $me_team_emeun_arr;
}

//验证token登录
function verificationToken(){
	$header_arr = em_getallheaders();
	$token=createToken($header_arr["Email"]);
	$header_arr['passport'] = $token==$header_arr["Token"];
	return $header_arr;
}


exit('{"ret":100,"msg":"not ac param"}');

?>