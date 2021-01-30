<?php

require_once '../wp-config.php';

//$host = DB_HOST.':3306';
$host = '127.0.0.1:3306';
$user = DB_USER;
$pass = DB_PASSWORD;
$dbName = DB_NAME;


$dsn="mysql:host=$host;dbname=$dbName";

function retmsg($retCode, $msg){
	return '{"ret":'.$retCode.',"msg":"'.$msg.'"}';
}

function createToken($module){
	$_date = date('Y-m',time());
	$token = md5($module.$_date.'#$@%!*');  
	return $token;
}


function em_getallheaders()
{
   foreach ($_SERVER as $name => $value)
   {
	   if (substr($name, 0, 5) == 'HTTP_')
	   {
		   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
	   }
   }
   return $headers;
}

// 随机字符
// 当前的毫秒时间戳
function msectime(){
	$arr = explode(' ', microtime());
	$tmp1 = $arr[0];
	$tmp2 = $arr[1];
    return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
}
// 10进制转62进制
function dec62($dec){
	$base = 62;
	$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$ret = '';
	for($t = floor(log10($dec) / log10($base)); $t >= 0; $t--){
		$a = floor($dec / pow($base, $t));
		$ret .= substr($chars, $a, 1);
		$dec -= $a * pow($base, $t);
	}
	return $ret;
}
// 随机字符
function rand_char(){
	$base = 62;
	$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	return $chars[mt_rand(1, $base) - 1];
}

//生成优惠券
function duifcoupon($email,$money){
	global $dsn;
	global $user;
	global $pass;

	$_date = date("Y-m-d H:i:s");
	$str_time = dec62(msectime());
	//8位随机字符串
	$code8 = rand_char().$str_time;
	
	//插入优惠码1
	$dbh = new PDO($dsn, $user, $pass);
	$sql = "INSERT INTO sd_posts (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('1', '{$_date}', '{$_date}', '', '{$code8}', '{$email}', 'publish', 'closed', 'closed', '', '{$code8}', '', '', '{$_date}', '{$_date}', '', '0', '', '0', 'shop_coupon', '', '0')";
	$dbh->query($sql);

	//取新插入的自增ID
	$lastid_sql = "SELECT LAST_INSERT_ID()";
	$lastid_res = $dbh->query($lastid_sql)->fetchAll(PDO::FETCH_ASSOC);
	$lastid = $lastid_res[0]['LAST_INSERT_ID()'];

	//插入优惠码2
	$_time = time();
	$sql = "INSERT INTO sd_postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', '_edit_last', '1');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', '_edit_lock', '{$_time}:1');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'discount_type', 'fixed_cart');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'coupon_amount', '{$money}');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'individual_use', 'no');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'usage_limit', '1');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'usage_limit_per_user', '1');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'limit_usage_to_x_items', '0');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'usage_count', '0');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'date_expires', null);".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'free_shipping', 'no');".
	"INSERT INTO `sd_postmeta` (`post_id`, `meta_key`, `meta_value`) VALUES ('{$lastid}', 'exclude_sale_items', 'no');";
	$dbh->query($sql);

	$arr = array("ret"=>0, "ID"=>$lastid, "amount"=>$money, "post_title"=>$code8, "post_date"=>$_date);
	
	return $arr;
}


?>