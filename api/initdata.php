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
		$config_sql = 'SELECT `id`,`key`,`value` FROM mission_config';
		$config_result = $dbh->query($config_sql)->fetchAll(PDO::FETCH_ASSOC);

		//公告
		$notice_sql = 'SELECT * FROM mission_notice order by id DESC';
		$notice_result = $dbh->query($notice_sql)->fetchAll(PDO::FETCH_ASSOC);

		//任务商品
		$mission_sql = 'SELECT * FROM mission_mission order by price ASC';
		$mission_result = $dbh->query($mission_sql)->fetchAll(PDO::FETCH_ASSOC);

		//优惠券列表
		//取列表
		$coupon_sql = "SELECT ID,post_title,post_date FROM sd_posts where lower(post_title)=lower(post_name) and post_type='shop_coupon' and post_excerpt='".$gt['Email']."'";
		$coupon_result = $dbh->query($coupon_sql)->fetchAll(PDO::FETCH_ASSOC);
		//var_dump($coupon_result);
		$coupon_post_id_str="";
		$coupon_post_id_count = count($coupon_result);
		if($coupon_post_id_count>0){
			//把ID集中起来
			for($i=0;$i<$coupon_post_id_count;$i++){
				$isEndStr = $i==$coupon_post_id_count-1 ? "" : ",";
				$coupon_post_id_str .= $coupon_result[$i]['ID'] . $isEndStr;
			}
			
			if(!empty($coupon_post_id_str)){
				//查使用过的优惠券，以便剔除
				$coupon_used_sql = "SELECT post_id FROM sd_postmeta where meta_key='_used_by' and post_id in(".$coupon_post_id_str.") order by meta_id DESC";
				$coupon_used_result = $dbh->query($coupon_used_sql)->fetchAll(PDO::FETCH_ASSOC);
				$coupon_used_count = count($coupon_used_result);
				for($i=0;$i<$coupon_used_count;$i++){
					$post_id = $coupon_used_result[$i]['post_id'];
					$coupon_post_id_str = str_replace($post_id, "-1", $coupon_post_id_str);
					for($m=0;$m<count($coupon_result);$m++){
						if($coupon_result[$m]['ID'] == $post_id){
							array_splice($coupon_result, $m, 1);
							//var_dump($coupon_result);
							break;
						}
					}
				}
				//按ID查优惠券的价格
				$coupon_amount_sql = "SELECT * FROM sd_postmeta where meta_key='coupon_amount' and post_id in(".$coupon_post_id_str.") order by meta_id DESC";
				//echo '->'.$coupon_amount_sql.'<-';
				$coupon_amount_result = $dbh->query($coupon_amount_sql)->fetchAll(PDO::FETCH_ASSOC);
				//var_dump($coupon_amount_result);
				$coupon_amount_count = count($coupon_amount_result);
				//把价格堆入列表
				for($i=0;$i<$coupon_post_id_count;$i++){
					for($k=0;$k<$coupon_amount_count;$k++){
						if($coupon_result[$i]['ID'] == $coupon_amount_result[$k]["post_id"] && $coupon_amount_result[$k]['usage_count']<=0){
							$coupon_result[$i]['amount'] = $coupon_amount_result[$k]["meta_value"];
						}
					}
				}
			}
		}

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
			"meteamorder"=>$me_team_emeun_arr, "meuser"=>$meuser_result, "coupons"=>$coupon_result,
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
	$me_order_count = count($me_order_result);//订单总数
	//查订单的使用优惠券
	$me_order_coupon_sql = 'SELECT order_id,discount_amount FROM sd_wc_order_coupon_lookup where order_id in('.$me_order_post_id_str.')';
	$me_order_coupon_result = $dbh->query($me_order_coupon_sql)->fetchAll(PDO::FETCH_ASSOC);
	$me_order_coupon_count = count($me_order_coupon_result);//优惠券总数
	for($i=0;$i<$me_order_count;$i++){//把优惠券的折价加到net_total字段里
		for($k=0;$k<$me_order_coupon_count;$k++){
			if($me_order_result[$i]['order_id'] == $me_order_coupon_result[$k]['order_id']){
				$me_order_result[$i]['net_total'] = floatval($me_order_result[$i]['net_total']) + floatval($me_order_coupon_result[$k]['discount_amount']);
			}
		}
	}
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