<?php
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:*'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with,Origin,Authorization,email,token');
header('Access-Control-Max-Age: 1728000');

if($_SERVER['REQUEST_METHOD']=='OPTIONS') return;

require './conn.php';
$dbh = new PDO($dsn, $user, $pass);

$fofel_arr = array('tomoney','tomoney1','tomoney2','tomoney3');
$fofel_i = 0;

$action = isset($_GET['ac'])?$_GET['ac']:"";
//http://192.168.123.85/api/change.php?ac=1&paypal=123
$start_time = time();
$gt = verificationToken();
if($gt['passport'])
{
	if($action==1){//提交paypal
		$paypal = isset($_GET['paypal'])?$_GET['paypal']:"";
		if(!empty($paypal)){
			//查自己的UID
			$sql = "UPDATE mission_user set paypal='{$paypal}' where email='".$gt['Email']."'";
			$cx = $dbh->query($sql);
			$me_order_post_id_sql = 'SELECT paypal from mission_user where email="'.$gt['Email'].'"';
			$me_order_post_id_res = $dbh->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
			$_str = '';
			if(count($me_order_post_id_res)){
				$_str = $me_order_post_id_res[0]['paypal'];
			}
			$arr = array("ret"=>0, "paypal"=>$_str);
			$_str = json_encode($arr);
			exit($_str);
		}else{
			exit(retmsg(111,"not paypal"));
		}
	}else if($action==2){//提交提现审核
		$ww = date("w");//取星期几
		if($ww!=2 || $ww!=5){//如果不是星期2或星期5
			exit(retmsg(116,"Time is not up"));
			return;
		}
		$drawmoneyapply_sql = "SELECT drawmoneyapply FROM `mission_user` where email='".$gt['Email']."'";
		$drawmoneyapply_res = $dbh->query($drawmoneyapply_sql)->fetchAll(PDO::FETCH_ASSOC);
		if($drawmoneyapply_res[0]['drawmoneyapply']==0){//只有当不是取现申请的时候才会去改变记录
			setOrderFromEmail($gt['Email'], "tomoney", 3, true);
			//echo 'bbb->'.$drawmoneyapply_res[0]['drawmoneyapply'];
			setOrderFromEmailLoop($gt['Email'], 3);
			//echo 'cccc->'.$drawmoneyapply_res[0]['drawmoneyapply'];
			$sql = "UPDATE mission_user set drawmoneyapply=1 where email='".$gt['Email']."'";
			$dbh->query($sql);
		}
		exit(retmsg(0,"success"));
	}else if($action==3){//再次购买	change.php?ac=3&oid=406
		$oid = isset($_GET['oid'])?$_GET['oid']:"";
		if(!empty($oid)){
			$sql = 'SELECT post_id FROM sd_postmeta where meta_key="_billing_email" and meta_value="'.$gt['Email'].'" and post_id='.$oid;
			$res = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if(count($res)){
				$sql = 'SELECT commission_scale,status from sd_wc_order_stats where order_id='.$oid;
				$res = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
				if($res[0]['status']=='wc-completed'){
					$commission_scale = $res[0]['commission_scale'];
					$commission_scale++;
					$_date = date("Y-m-d H:i:s");
					$sql = "UPDATE sd_wc_order_stats set date_created='{$_date}', date_created_gmt='{$_date}', status='wc-processing', commission_scale=".$commission_scale.' where order_id='.$oid;
					$cx = $dbh->query($sql);
					exit(retmsg(0,"success"));
				}else{
					exit(retmsg(114,"this order is not completed"));
				}
			}else{
				exit(retmsg(113,"this order is not you"));
			}
		}else{
			exit(retmsg(112,"not order id"));
		}
	}else if($action==4){//用户提交的商品评论	change.php?ac=4&oid=406&comments=这是给商品的评论
		$oid = isset($_GET['oid'])?$_GET['oid']:"";
		if(!empty($oid)){
			$sql = 'SELECT post_id FROM sd_postmeta where meta_key="_billing_email" and meta_value="'.$gt['Email'].'" and post_id='.$oid;
			$res = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			if(count($res)){
				$sql = 'SELECT * from sd_wc_order_stats where order_id='.$oid;
				$res = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
				if($res[0]['status']=='wc-processing'){
					$a = date($res[0]['date_created_gmt']);
					//echo "AA-->".$a;
					$b = date('Y-m-d H:i:s');
					$aa = strtotime($b)-strtotime($a);
					$gap_day = intval($aa / (60 * 60 * 24) );
					if($gap_day>=2){
						$sql = "UPDATE sd_wc_order_stats set status='wc-completed' where order_id=".$oid;
						$cx = $dbh->query($sql);
						exit(retmsg(0,"success"));
					}else{
						exit(retmsg(115, "time is not up"));
					}
				}else{
					exit(retmsg(114,"this order is not completed"));
				}
			}else{
				exit(retmsg(113,"this order is not you"));
			}
		}else{
			exit(retmsg(112,"not order id"));
		}
	}else{
		exit(retmsg(110,"not action"));
	}
}else{
	exit(retmsg(108,"logout"));
}


function setOrderFromEmail($email, $tomoney_field, $tomoney_value, $tomoney_daymission) {
	global $dbh;
	//查某个email的所有订单ID
	$me_order_post_id_sql = 'SELECT post_id FROM sd_postmeta where meta_key="_billing_email" and meta_value="'.$email.'"';
	//echo '<'.$me_order_post_id_sql.'>';
	$me_order_post_id_result = $dbh->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
	//组合所有订单ID
	$me_order_post_id_str="";
	$me_order_post_id_count = count($me_order_post_id_result);
	for($i=0;$i<$me_order_post_id_count;$i++){
		$isEndStr = $i==$me_order_post_id_count-1 ? "" : ",";
		$me_order_post_id_str .= '"'.$me_order_post_id_result[$i]['post_id'] .'"'. $isEndStr;
	}
	//查所有符合条件的订单
	$me_order_post_id_sql = 'UPDATE sd_wc_order_stats SET '.$tomoney_field.'='.$tomoney_value.' where '.$tomoney_field.'=0 and order_id in('.$me_order_post_id_str.')';
	$dbh->query($me_order_post_id_sql);

	if(!empty($tomoney_daymission)){//每日所完成的任务是否提现
		$me_order_post_id_sql = 'UPDATE sd_wc_order_stats SET tomoney_daymission='.$tomoney_value.' where tomoney_daymission=0 and order_id in('.$me_order_post_id_str.')';
		$dbh->query($me_order_post_id_sql);
	}
}

function setOrderFromEmailLoop($email, $tomoney_value) {
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
		setOrderFromEmail($_email, $fofel_arr[$fofel_i], $tomoney_value, "");
		setOrderFromEmailLoop($_email, $tomoney_value);
		$fofel_i--;
	}
	
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