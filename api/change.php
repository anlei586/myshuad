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
		$type = isset($_GET['type'])?$_GET['type']:"";
		if($type==0){
			$ww = date("w");//取星期几
			if($ww!=2 || $ww!=5){//如果不是星期2或星期5
				exit(retmsg(116,"Time is not up"));
				return;
			}
		}
		$drawmoneyapply_sql = "SELECT drawmoneyapply FROM `mission_user` where email='".$gt['Email']."'";
		$drawmoneyapply_res = $dbh->query($drawmoneyapply_sql)->fetchAll(PDO::FETCH_ASSOC);
		if($drawmoneyapply_res[0]['drawmoneyapply']==0){//只有当不是取现申请的时候才会去改变记录
			setOrderFromEmail($gt['Email'], "tomoney", 3, true);
			//echo 'bbb->'.$drawmoneyapply_res[0]['drawmoneyapply'];
			setOrderFromEmailLoop($gt['Email'], 3);
			//echo 'cccc->'.$drawmoneyapply_res[0]['drawmoneyapply'];
			$sql = "UPDATE mission_user set drawmoneyapply=1,coveconput=".$type." where email='".$gt['Email']."'";
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
	}else if($action==5){//用户提交的兑换优惠券的商品	change.php?ac=5&oid=406
		$oid = isset($_GET['oid'])?$_GET['oid']:"";
		if(!empty($oid)){
			//0.读佣金配置
			$smp_sql = 'SELECT value from mission_config where `key`="commission_proportion"';
			$smp_res = $dbh->query($smp_sql)->fetchAll(PDO::FETCH_ASSOC);
			$commission_proportion = $smp_res[0]['value'];
			//1.读订单价格
			$order_sql = 'SELECT order_id,net_total from sd_wc_order_stats where order_id='.$oid;
			$order_res = $dbh->query($order_sql)->fetchAll(PDO::FETCH_ASSOC);
			$me_order_count = count($order_res);//订单总数
			//var_dump($order_res);
			

			//查订单的使用优惠券
			$me_order_coupon_sql = 'SELECT order_id,discount_amount FROM sd_wc_order_coupon_lookup where order_id in('.$oid.')';
			$me_order_coupon_result = $dbh->query($me_order_coupon_sql)->fetchAll(PDO::FETCH_ASSOC);
			$me_order_coupon_count = count($me_order_coupon_result);//优惠券总数
			//var_dump($me_order_coupon_result);
			for($i=0;$i<$me_order_count;$i++){//把优惠券的折价加到net_total字段里
				for($k=0;$k<$me_order_coupon_count;$k++){
					if($order_res[$i]['order_id'] == $me_order_coupon_result[$k]['order_id']){
						$order_res[$i]['net_total'] = floatval($order_res[$i]['net_total']) + floatval($me_order_coupon_result[$k]['discount_amount']);
						//var_dump($order_res[$i]['net_total']);
					}
				}
			}
			$net_total = $order_res[0]['net_total'];
			$order_price = $net_total;//订单价格



			
			
			
			
			$order_price_comm = $order_price*$commission_proportion;//佣金
			$total_order_price = $order_price + $order_price_comm + 0.1;
			//1.把订单时间改为最新, 状态改为 (wc-on-hold)  wc-completed
			$_date = date('Y-m-d H:i:s');
			$sql = "UPDATE sd_wc_order_stats set date_created='{$_date}', date_created_gmt='{$_date}', status='wc-completed', tomoney=1 where order_id=".$oid;
			$dbh->query($sql);
			//2.生成优惠券
			$arr = duifcoupon($gt['Email'], $total_order_price);
			$str = json_encode($arr);
			exit($str);
		}else{
			exit(retmsg(112,"not order id"));
		}
	}else if($action==6){//用户提交的拆分优惠券	change.php?ac=6&id=1565&title=abc&m1=1&m2=9
		$pid = isset($_GET['id'])?$_GET['id']:"";
		$pid = intval($pid);
		if(!empty($pid)){
			//取好url参数
			$m1 = isset($_GET['m1'])?$_GET['m1']:0;
			//$m2 = isset($_GET['m2'])?$_GET['m2']:0;
			$m1 = floatval($m1);
			//$m2 = floatval($m2);
			if($m1<=0){
				exit(retmsg(124,"coupon amount error"));
			}
			$title = isset($_GET['title'])?$_GET['title']:0;
			$title = strtolower($title);
			//读这张优惠是否是自己的
			$coupon_sql = "SELECT ID,post_title FROM sd_posts where lower(post_title)=lower(post_name) and ID=".$pid." and lower(post_title)='".$title."' and post_type='shop_coupon' and post_excerpt='".$gt['Email']."' order by id DESC";
			//echo '->'.$coupon_sql.'<-';
			$coupon_result = $dbh->query($coupon_sql)->fetchAll(PDO::FETCH_ASSOC);
			//var_dump($coupon_result);
			//说明有此卷
			if(count($coupon_result)>0)
			{
				//则去查卷的价格
				$coupon_amount_sql = "SELECT * FROM sd_postmeta where meta_key='coupon_amount' and post_id=".$pid." order by meta_id DESC";
				//echo '->'.$coupon_amount_sql.'<-';
				$coupon_amount_result = $dbh->query($coupon_amount_sql)->fetchAll(PDO::FETCH_ASSOC);
				$amount = $coupon_amount_result[0]['meta_value'];
				//var_dump($coupon_amount_result);
				//说明有查到卷的价格
				if(count($amount)>0 && $m1<$amount)
				{
					$m2 = $amount - $m1;
					//如果URL参数发来的两个拆分价格相加等于库中查到的价格相等则说明成功
					if($amount == $m1+$m2){
						//可以在此拆分卷
						//1.修改原始卷的价格为m2
						$sql = "UPDATE sd_postmeta set meta_value={$m2} where meta_key='coupon_amount' and post_id=".$pid;
						$dbh->query($sql);
						//2.新增一张价格为m1的卷
						$arr = duifcoupon($gt['Email'], $m1);
						$arr["m2"] = $m2;
						$arr["msg"] = "success";
						$str = json_encode($arr);
						exit($str);
					}else{
						exit(retmsg(123,"coupon amount error"));
					}
				}else{
					exit(retmsg(122,"not coupon amount"));
				}
			}else{
				exit(retmsg(121,"not coupon"));
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