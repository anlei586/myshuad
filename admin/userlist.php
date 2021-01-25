<?php
ini_set('display_errors',1); //错误信息 
ini_set('display_startup_errors',1); //php启动错误信息 
error_reporting(-1); //打印出所有的 错误信息
?>
<?php function renderOrderTable($result){ ?>
<div class="layui-form">
<table style="margin-left: 1%; width: 98%;" class="layui-table">
  <colgroup>
    <col width="5%">
    <col width="5%">
    <col width="5%">
    <col width="5%">
    <col width="5%">
    <col width="5%">
    <col width="5%">
    <col width="5%">
    <col>
  </colgroup>
  <thead>
    <tr>
      <th style="text-align: center; cursor: pointer;" onclick="onOrderIdSelectAll(this);">ID</th>
      <th style="text-align: center;">邮箱</th>
      <th style="text-align: center;">注册日期</th>
      <th style="text-align: center;">Paypal</th>
      <th style="text-align: center;">提现总额</th>
      <th style="text-align: center;">他的收入</th>
      <th style="text-align: center;">确认提现</th>
      <th style="text-align: center;">操作</th>
    </tr> 
  </thead>
  <tbody>
  <?php foreach ($result as $key => $value):  $_id=$value["id"];?>
    <tr id="ytr_<?php echo $_id; ?>">
      <td><input id="id_<?php echo $_id; ?>" type="checkbox" name="" title="<?php echo $_id; ?>" lay-skin="primary"></td>
      <td><?php echo $value['email']; ?></td>
      <td><?php echo $value["date"]; ?></td>
      <td><?php echo $value["paypal"]; ?></td>
      <td><?php echo $value["drawmoneyreco"]; ?></td>
      <td id="shouru_<?php echo $_id; ?>"></td>
      <td>
	  		<?php if($value["drawmoneyapply"]==1){ ?>
	  		<div>发起提现<br/><span id="draw_money_txt_<?php echo $_id; ?>"></span></div><hr/>
			<button onclick="confriDrawMoney(this,'<?php echo $_id; ?>');" type="button" class="layui-btn layui-btn-danger layui-btn-sm">确认提现</button>
			<?php } ?>
	  </td>
      <td>
			<button onclick="lookOrderList(this,'<?php echo $_id; ?>');" type="button" class="layui-btn layui-btn-sm">查看他的订单</button>
			<hr/>
			<button onclick="lookTeamOrderList(this,'<?php echo $_id; ?>');" type="button" class="layui-btn layui-btn-sm">查看他的团队订单</button>
	  </td>
    </tr>
  <?php endforeach ?>
  </tbody>
</table>
</div>
<?php } ?>
<?php

require './utils.php';
require './conn.php';

$pdo = new PDO($dsn, $user, $pass);

$fofel_arr = array('A','B','C','D');
$fofel_i = 0;

if(isset($_GET['action'])){
	$action = $_GET['action'];
	if($action == 1){
		if(isset($_GET['id'])){
			$d = $_GET['id'];
			$sql = 'DELETE FROM mission_user WHERE id="'.$d.'"';
			$pdo->exec($sql);
			echo '{"ret":200}';
			die();
			return;
		}
	}else if($action == 2){
		if(isset($_GET['ids'])){
			$ids = $_GET['ids'];
			$sql = 'DELETE FROM mission_user WHERE id in ('.$ids.')';
			//echo $sql;
			$pdo->exec($sql);
			echo '{"ret":200}';
			die();
			return;
		}
	}else if($action == 3){
		$money = isset($_GET['m'])?$_GET['m']:"";//提现金额
		$uid = isset($_GET['uid'])?$_GET['uid']:"";//提现人UID
		$email = isset($_GET['email'])?$_GET['email']:"";//提现人UID
		if(floatval($money)>0 && !empty($uid) && !empty($email)){
			$sql = "select drawmoneyreco FROM mission_user where id={$uid} and email='{$email}'";
			$_result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
			$old_drawmoneyreco = $_result[0]['drawmoneyreco'];
			$new_drawmoneyreco = date("Y-m-d H:i:s").'='.$money.'|'.$old_drawmoneyreco;
			
			//改更drawmoneyapply提现申请，把赠送的钱置0
			$sql = "UPDATE mission_user set drawmoneyapply=0, givemoney=0, drawmoneyreco='{$new_drawmoneyreco}' where id={$uid}";
			$pdo->query($sql);

			//更改自己的订单为审核通过
			$forder_res = findOrderFromEmail($email);
			for($k=0;$k<count($forder_res);$k++) {
				$sql = "UPDATE sd_wc_order_stats set tomoney=1 where status='wc-completed' and tomoney=3 and order_id=".$forder_res[$k]["order_id"];
				$pdo->query($sql);
			}
			//更改自己的每日奖励为审核通过
			$sql = "UPDATE sd_wc_order_stats set tomoney_daymission=1 where status='wc-completed' and tomoney_daymission=3";
			$pdo->query($sql);
			//把再买一次复位
			$sql = "UPDATE sd_wc_order_stats set commission_scale=1 where status='wc-completed'";
			$pdo->query($sql);

			setOrderDrawMoneyFromEmailLoop($email);



			exit('{"ret":200}');
		}
		exit('{"ret":101}');
	}

}

function getLevelCommissionDrawmoney($lv){
	if($lv=="B"){
		return 'tomoney1';
	}
	if($lv=="C"){
		return 'tomoney2';
	}
	if($lv=="D"){
		return 'tomoney3';
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
	global $pdo;
	//查某个email的所有订单ID
	$me_order_post_id_sql = 'SELECT post_id FROM sd_postmeta where meta_key="_billing_email" and meta_value="'.$email.'"';
	$me_order_post_id_result = $pdo->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
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
	$me_order_result = $pdo->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
	return $me_order_result;
}

function findOrderFromEmailLoop($email) {
	global $pdo;
	global $fofel_arr;
	global $fofel_i;
	$fofel_i++;
	//查自己的UID
	$me_order_post_id_sql = 'SELECT id from mission_user where email="'.$email.'"';
	$me_order_post_id_result = $pdo->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
	$me_uid = $me_order_post_id_result[0]["id"];
	//查自己所有员工的email
	$me_team_emeun_email_sql = 'SELECT email from mission_user where parent_id='.$me_uid;
	$me_team_emeun_email_result = $pdo->query($me_team_emeun_email_sql)->fetchAll(PDO::FETCH_ASSOC);
	//查员工的所有订单
	$me_team_emeun_email_count = count($me_team_emeun_email_result);
	$me_team_emeun_arr = array();
	for($i=0;$i<$me_team_emeun_email_count;$i++){
		$_email = $me_team_emeun_email_result[$i]['email'];
		$md5_email = md5($_email);
		$star_email = $_email;//filterStar($_email);
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

function setOrderDrawMoneyFromEmailLoop($email) {
	global $pdo;
	global $fofel_arr;
	global $fofel_i;
	$fofel_i++;
	//查自己的UID
	$me_order_post_id_sql = 'SELECT id from mission_user where email="'.$email.'"';
	$me_order_post_id_result = $pdo->query($me_order_post_id_sql)->fetchAll(PDO::FETCH_ASSOC);
	$me_uid = $me_order_post_id_result[0]["id"];
	//查自己所有员工的email
	$me_team_emeun_email_sql = 'SELECT email from mission_user where parent_id='.$me_uid;
	$me_team_emeun_email_result = $pdo->query($me_team_emeun_email_sql)->fetchAll(PDO::FETCH_ASSOC);
	//查员工的所有订单
	$me_team_emeun_email_count = count($me_team_emeun_email_result);
	$me_team_emeun_arr = array();
	for($i=0;$i<$me_team_emeun_email_count;$i++){
		$_email = $me_team_emeun_email_result[$i]['email'];
		$_tomoney = getLevelCommissionDrawmoney($fofel_arr[$fofel_i]);
		$forder_res = findOrderFromEmail($_email);
		//var_dump($_email);
		for($k=0;$k<count($forder_res);$k++) {
			$sql = "UPDATE sd_wc_order_stats set {$_tomoney}=1 where status='wc-completed' and {$_tomoney}=3 and order_id=".$forder_res[$k]["order_id"];
			//var_dump($sql);
			$pdo->query($sql);
		}

		setOrderDrawMoneyFromEmailLoop($_email);
		$fofel_i--;
	}
	if(count($me_team_emeun_arr)<=0) return null;
	return $me_team_emeun_arr;
}



$num = isset($_GET['num'])?$_GET['num']:10;
$sv = '';

if(isset($_GET['v'])){
	$sv = $_GET['v'];
	$sql = 'SELECT id from mission_user WHERE email<>"shuad" and email like "%'.$sv.'%"';
	$db  = $pdo->query($sql)->fetchAll();
	$total = count($db);

	$cpage = isset($_GET['page'])?$_GET['page']:1;
	$offset = ($cpage-1)*$num;

	$sql = "SELECT * from mission_user WHERE email<>'shuad' and email like '%{$sv}%' order by drawmoneyapply DESC,id DESC  limit {$offset},{$num}";
	$result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}else{
	$sql = "select id from mission_user WHERE email<>'shuad'";
	$db  = $pdo->query($sql)->fetchAll();
	$total = count($db);

	$cpage = isset($_GET['page'])?$_GET['page']:1;
	$offset = ($cpage-1)*$num;

	$sql = "select * from mission_user WHERE email<>'shuad' order by drawmoneyapply DESC,id DESC limit {$offset},{$num}";
	$result  = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

if(!empty($result)){
	$res_len = count($result);
	for($i=0;$i<$res_len;$i++){
		$fofel_i = 0;
		$me_order_result = findOrderFromEmail($result[$i]['email']);
		$me_team_emeun_arr = findOrderFromEmailLoop($result[$i]['email']);
		$result[$i]['meorder'] = $me_order_result;
		$result[$i]['meteamorder'] = $me_team_emeun_arr;
		
	}
	
	$result_str = json_encode($result);
}


$config_sql = 'SELECT * FROM mission_config';
$config_result = $pdo->query($config_sql)->fetchAll(PDO::FETCH_ASSOC);
$config_str = json_encode($config_result);

//$dateArr = array("date"=>date("Y-m-d H:i:s"), "time"=>time(), "start_time"=>$start_time, "end_time"=>$end_time);

$server_date = date("Y-m-d H:i:s");


?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>所有用户</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="./layui/css/mui.min.css">
	<link rel="stylesheet" href="./layui/css/layui.css">
	<script src="./layui/vue.min.js"></script>
	<script src="./layui/lang_cn.js"></script>
	<style>
		::-webkit-scrollbar {width: 14px;height: 14px;}
		::-webkit-scrollbar-track {background: rgba(241, 241, 242, 1);}
		::-webkit-scrollbar-thumb {background-color: #33333375;}
		::-webkit-scrollbar-thumb:window-inactive {background: #d2d2d2;}
		span{
			user-select: text;
			-webkit-user-select:text;
			-moz-user-select:text;
		}
		div{
			user-select: text;
			-webkit-user-select:text;
			-moz-user-select:text;
		}
		td{
			user-select: text;
			-webkit-user-select:text;
			-moz-user-select:text;
		}
		b{
			user-select: text;
			-webkit-user-select:text;
			-moz-user-select:text;
		}
		li{
			user-select: text;
			-webkit-user-select:text;
			-moz-user-select:text;
		}

	</style>
</head>
<body>
<script>
var curr_uid = -1;
var currusername = '';
var search_v = "<?php echo $sv; ?>";
var result_obj = <?php echo $result_str; ?>;
var config_obj = <?php echo $config_str; ?>;
var server_date = "<?php echo $server_date; ?>";
var he_order_content_obj = {};
var team_order_content_obj = {};


var team_isdrawmoney_total=0;//团队可提现的总额
var ordermoney_total=0;//总本金
var commissionmoney_total=0;//总佣金
var interestmoney_total=0;//总利息
var day_mission_reward_total = 0;//每日任务奖励总共有得了多少钱

var my_team_member_total=0;//团队总人数
var my_team_money_total=0;//团队总金额

var interest=0.0000001;//利息分比例
var commission_proportion=0.10;//佣金百分比例

function replace(str, flag, rep){
	str = str.split(flag).join(rep);
	return str;
}

function getConfig(res, key){
	for(var i=0;i<res.length;i++){
		if(res[i].key==key){
			return res[i].value;
		}
	}
	return null;
}

function getConfig2(res, key, value){
	for(var i=0;i<res.length;i++){
		if(res[i][key]==String(value)){
			return res[i];
		}
	}
	return null;
}

function getItemMinMoney(item){
	var total_sales = parseFloat(item.total_sales);
	var net_total = parseFloat(item.net_total);
	var _end = Math.min(total_sales, net_total);
	return _end;
}
//把团队订单树列展平成列表
function coveMeTeamOrder(list, ___tomoney){
	var arr = coveMeTeamOrderLoop(list);
	var isdrawmoney_total = 0;
	for(var i=0;i<arr.length;i++){
		var item = arr[i];
		var _tomoney = parseInt(item[getLevelCommissionDrawmoney(item)]);
		if(item.status == "wc-completed"){
			var _money = parseFloat(getItemMinMoney(item));
			my_team_money_total += _money;
		}
		if(item.status == "wc-completed" && _tomoney == ___tomoney){//用户提现：0=可以提现，3=提现审核中，(1=通过审核，2=已据绝)
			_ovint = getLevelCommissionParam(item);
			var _money = parseFloat(getItemMinMoney(item));
			var money_proportion = _money * parseFloat(getConfig(config_obj, "commission_proportion"));
			_ovint = _ovint * money_proportion;
			isdrawmoney_total += _ovint;
		}
	}
	team_isdrawmoney_total = isdrawmoney_total;
	return arr;
}
//展平列表
function coveMeTeamOrderLoop(list){
	var arr = [];
	var carr = [];
	for(var _item in list){
		my_team_member_total++;//团队总人数
		var _orderList = list[_item].order;
		if(_orderList){
			for(var i=0;i<_orderList.length;i++){
				_orderList[i]['email'] = list[_item].email;
				_orderList[i]['level'] = list[_item].level;
				//_orderList[i]['interest'] = getInterestByLevel(_orderList[i]);
				arr.push(_orderList[i]);
			}
		}
		var _orderChildList = list[_item].childorder;
		if(_orderChildList){
			carr = coveMeTeamOrderLoop(_orderChildList);
		}
	}
	arr = arr.concat(carr);
	return arr;
}

//取抽取下级员工佣金
function getLevelCommissionParam(order){
	var _interest = getConfig(config_obj, "pyramid");
	var _interestArr = _interest.split(",");
	var _interestObj = {
		"B": parseFloat(_interestArr[0]),
		"C": parseFloat(_interestArr[1]),
		"D": parseFloat(_interestArr[2])
	};
	return _interestObj[order["level"]];
}
function getLevelCommissionColor(order){
	if(order["level"]=="B"){
		return '<font color="#0062CC"><b>B</b></font>';
	}
	if(order["level"]=="C"){
		return '<font color="#f4a300"><b>C</b></font>';
	}
	if(order["level"]=="D"){
		return '<font color="#aa00ff"><b>D</b></font>';
	}
}
function getLevelCommissionDrawmoney(order){
	if(order["level"]=="B"){
		return 'tomoney1';
	}
	if(order["level"]=="C"){
		return 'tomoney2';
	}
	if(order["level"]=="D"){
		return 'tomoney3';
	}
}

function dateSub(date_str1, date_str2){
	var date_str1 = date_str1.replace(/\-/g, "/");
	var date_str2 = date_str2.replace(/\-/g, "/");
	var _day = new Date(date_str1)-new Date(date_str2);
	return _day;
}

//展平自己的订单
function coveMeOrder(meorder, ___tomoney){
	for(var i=0;i<meorder.length;i++){
		var item = meorder[i];
		var _tomoney = parseInt(item.tomoney);
		if(item.status == "wc-completed" && _tomoney == ___tomoney){
			var _commission_scale = item.commission_scale;//再买一次的倍数
			var _money = parseFloat(getItemMinMoney(item));
			//总本金
			ordermoney_total += _money;
			//总佣金
			var money_proportion = _money * commission_proportion * _commission_scale;
			commissionmoney_total += money_proportion;
			//总利息
			var _ovint = 0;
			//服务器当前日期-下单日期=共下单了多少天
			var _day = dateSub(server_date, item.date_created_gmt) / 1000 / (60 * 60 * 24);
			_day = parseInt(_day);
			_ovint = interest * parseFloat(getItemMinMoney(item)) * _day;
			interestmoney_total += _ovint;
			
		}
	}
	return meorder;
}
//以oid查找订单
function findOrderItemByOid(orderList, oid){
	for(var i=0;i<orderList.length;i++){
		if(orderList[i].order_id==oid)
		{
			return orderList[i];
		}
	}
}


function getDayMissionReward(__capital_details){
	//每日任务奖励说明
	var daymission_reward_data=[];
	var daymission_reward_data_arr = getConfig(config_obj, "daymissionreward").split(",");
	for(var i=0;i<daymission_reward_data_arr.length;i++){
		var _arr = daymission_reward_data_arr[i].split("=");
		var _obj = {
			level:i,
			ordertotal: parseFloat(_arr[0]),
			reward: parseFloat(_arr[1]),
		}
		daymission_reward_data.push(_obj);
	}
	//把自己的订单分割出来
	//得到某天共有多少单
	var _cdObj = {};
	for(var j1=0;j1<__capital_details.length;j1++){
		var __item = __capital_details[j1];
		var __isComp = __item.status=="wc-completed";
		var __date = __item['date_created_gmt'].split(" ")[0];
		if(!_cdObj.hasOwnProperty(__date))
		{
			_cdObj[__date] = {
				date:__date,
				complete_count: __isComp ? 1 : 0,
				other_count: __isComp ? 0 : 1,
				//complete_count: parseInt(Math.random()*100),	//测试数据，这行需要注释掉
				//other_count: parseInt(Math.random()*100),		//测试数据，这行需要注释掉
			};
		}else{
			if(__isComp){
				_cdObj[__date].complete_count++;
			}else{
				_cdObj[__date].other_count++;
			}
		}
	}
	//把订单按日期从晚到早排序
	//day_mission_reward_total = 0;//每日任务奖励总共有得了多少钱
	var _cdArr = [];
	for(var __date in _cdObj){
		var __progress = "";
		var __reward = 0;
		for(var j2=0;j2<daymission_reward_data.length;j2++){
			if(_cdObj[__date].complete_count>=daymission_reward_data[j2].ordertotal){
				__progress = __progress + "+" + daymission_reward_data[j2].reward;
				__reward = __reward + daymission_reward_data[j2].reward;
				day_mission_reward_total += daymission_reward_data[j2].reward;
			}
		}
		_cdObj[__date]['progress'] = __progress.substr(1);
		_cdObj[__date]['reward'] = __reward;
		_cdArr.push(_cdObj[__date]);
	}
	_cdArr.sort(function(a,b) { return a['date'] < b['date']; });
	var daymission = _cdArr;
	
	return {
		daymission_reward_data:daymission_reward_data,
		daymission:daymission,
		day_mission_reward_total:day_mission_reward_total
	};
}

function display_moneys(){
	//利息百分比
	interest = parseFloat(getConfig(config_obj, "interest"));
	//佣金百分比例
	commission_proportion = parseFloat(getConfig(config_obj, "commission_proportion"));
	for(var i=0;i<result_obj.length;i++){
		team_isdrawmoney_total=0;//团队可提现的总额
		ordermoney_total=0;//总本金
		commissionmoney_total=0;//总佣金
		interestmoney_total=0;//总利息
		day_mission_reward_total = 0;//每日任务奖励总共有得了多少钱
		my_team_member_total=0;//团队总人数
		my_team_money_total = 0;//团队总金额

		var ___tomoney = 0;
		var drawmoneyapply = result_obj[i]['drawmoneyapply'];//提现申请（1=用户发起提现）
		if(drawmoneyapply == 1){
			___tomoney = 3;//提现中的订单
		}else{
			___tomoney = 0;//未提现的订单
		}

		var uid = result_obj[i]['id'];
		var meorder = result_obj[i]['meorder'];
		var meteamorder = result_obj[i]['meteamorder'];
		he_order_content_obj[uid] = coveMeOrder(meorder, ___tomoney);
		team_order_content_obj[uid] = coveMeTeamOrder(meteamorder, ___tomoney);
		getDayMissionReward(meorder);

		var _givemoney = parseFloat(result_obj[i]['givemoney']);

		var __isdrawmoney_total = commissionmoney_total + ordermoney_total + interestmoney_total + team_isdrawmoney_total + day_mission_reward_total + _givemoney;
		if(__isdrawmoney_total>0) {
			//if(_givemoney>0 && __isdrawmoney_total != _givemoney) {
				var _td = document.getElementById("shouru_"+uid);
				_td.innerHTML = '<span class="layui-badge layui-bg-gray">团队:'+my_team_member_total+'人 ($'+my_team_money_total.toFixed(2)+')</span><br/><span class="layui-badge layui-bg-blue">他的佣金:'+commissionmoney_total.toFixed(4)+'</span><br/><span class="layui-badge-rim">团队佣金:'+team_isdrawmoney_total.toFixed(4)+'</span><br/><span class="layui-badge layui-bg-orange">每日奖励:'+day_mission_reward_total.toFixed(4)+'</span><br/><span class="layui-badge layui-bg-cyan">总利息:'+interestmoney_total.toFixed(4)+'</span><br/><span class="layui-badge">总本金:'+ordermoney_total.toFixed(4)+'</span><br/><span class="layui-badge layui-bg-green">可提现:<span id="drawmoney_total_'+uid+'">'+__isdrawmoney_total.toFixed(4)+'</span></span>';

				var draw_money_txt_sp = document.getElementById("draw_money_txt_"+uid);
				if(draw_money_txt_sp){
					draw_money_txt_sp.innerHTML = '<b>$'+__isdrawmoney_total.toFixed(4)+'</b>';
				}
			//}
		}
	}
}


</script>

<center><h1>所有用户</h1></center>
<div class="layui-form-item" style="margin-bottom:0px; padding: 10px;text-align: center;display: flex;">
	<input type="text" name="value_txt" id="value_txt" required lay-verify="required" placeholder="请输入要搜索用户email" autocomplete="off" class="layui-input" style="text-align:center;padding-left: 15%;padding-top: 0px;width: 85%;display: inline;" value="<?php echo $sv; ?>" onkeyup="onKeyUp(this, event)">
	<button class="layui-btn" style="width: 15%;text-align: center;margin-left: 4px;" onclick="onSearch();"><i class="layui-icon">&#xe615;</i> 搜索</button>
</div>


<div id="content_div" style="text-align:center;">
	<?php echo "<span class='layui-badge layui-bg-gray'>共有<b>$total</b>个用户</span>" ?>
	<?php renderOrderTable($result, $dispose_status); ?>
	<br/><br/><br/>
	<div id="orderlist_page" style="position: fixed;width: 100%;bottom: 0px;background-color: white;border-width: 1px; border-style: solid; border-color: #ffffff; border-top-color: #e6e6e6; padding-top: 6px;">
	</div>
</div>


<script src="./layui/layui.all.js"></script>
<script>
var content_div = document.getElementById('content_div');
var value_txt = document.getElementById('value_txt');
var laytpl,layer,form,laypage;
var isFirst = true;

var sel = "<?php echo $sel; ?>";
var limit = "<?php echo $num; ?>";

layui.use(['laypage', 'flow','laytpl','form'], function(){
	layer = layui.layer;
	form = layui.form;
	laytpl = layui.laytpl;
	laypage = layui.laypage;
	layer = layui.layer;
	form = layui.form;
	laytpl = layui.laytpl;
	laypage = layui.laypage;

	//翻页完整功能
	laypage.render({
		elem: 'orderlist_page'
		,count: parseInt("<?php echo $total; ?>")
		,curr: parseInt("<?php echo $cpage; ?>")
		,limit: parseInt("<?php echo $num; ?>")
		,limits: [10,50,100,200,500]
		,layout: ['count', 'prev', 'page', 'next', 'limit', 'refresh', 'skip']
		,jump: function(obj){
			console.log(obj);
			if(!isFirst){
				layer.load(2);
				var _url = "?page="+obj.curr+"&sel="+sel+"&num="+obj.limit+"&status="+status;
				if(search_v){
					_url = _url + '&v='+search_v;
				}
				window.location.href = _url;

			}
			isFirst = false;
		}
	});

	display_moneys();

});

//搜索按钮事件
function onSearch(){
	var v = value_txt.value.trim();
	if(!v || (v && v.length<=0)){
		layui.layer.msg("不能为空");
		return;
	}
	layer.load(2);
	window.location = '?v='+v;
}

function onKeyUp(obj, event){
	if(event.keyCode == 13){
		onSearch()
	}
}

//全/反选订单ID
function onOrderIdSelectAll(_this){
	var _arr = document.querySelectorAll("input[type='checkbox']");
	for(var i=0;i<_arr.length;i++){
		_arr[i].checked = !_arr[i].checked;
	}
	layui.form.render();
}

function confriDrawMoney(that, id){
	var _sp = layui.$('#drawmoney_total_'+id);
	money = _sp[0].innerText;
	email = getConfig2(result_obj, 'id', id).email;//提现人UID
	
	var ind = layer.confirm('确定提现？', 
		{
			btn: ['确定', '取消'] //可以无限个按钮
		},
		function(index, layero) {
			layer.close(ind);
			var _ind = layui.layer.load(2);
			layui.jquery.get("?action=3&uid="+id+"&m="+money+"&email="+email, function(rel){
				layer.close(_ind);
				var ret = JSON.parse(rel);
				if(ret && ret.ret == 200){
					layer.msg("修改成功");
					window.location.reload();
				}else{
					layer.msg("失败");
				}
			});
		},
		function(index) {
			//console.log("按钮【按钮2】的回调");
		}
	);

}


var capital_details_vue;
function lookOrderList(that, id){
	var capital_details = layui.$('#capital_details');
	capital_details[0].style.display = "block";

	//填充-订单明细
	var __capital_details = he_order_content_obj[id];
	if(!capital_details_vue){
		capital_details_vue = new Vue({
			el: '#capital_details',
			data: {
				tab_menu:lang_var.tab_menu,
				capital_details:__capital_details
			},
			methods:{
				getMinMoney:function(item){//取最小的金额
					var _money = parseFloat(getItemMinMoney(item));
					var _commission_scale = item.commission_scale;//再买一次的倍数
					var money_proportion = _money * commission_proportion * _commission_scale;
					var _str = _money.toFixed(2);
					var _tomoney = parseInt(item.tomoney);
					if(item.status == "wc-completed" && _tomoney == 0){
						_str = _str + "(" + money_proportion.toFixed(2) + ")";
					}else{
						if(item.commission_scale>1 && item.status == "wc-processing"){
							_str = _str + "(" + money_proportion.toFixed(2) + ")";
						}else{
							_str = _str;
						}
					}
					return _str;
				},
				getInterest:function(item){//利息
					var _ovint = 0;
					var _tomoney = parseInt(item.tomoney);
					if(item.status == "wc-completed" && _tomoney == 0){
						//服务器当前日期-下单日期=共下单了多少天
						var _day = dateSub(server_date, item.date_created_gmt) / 1000 / (60 * 60 * 24);
						_day = parseInt(_day);
						_ovint = interest * parseFloat(getItemMinMoney(item)) * _day;
						_ovint = parseFloat(_ovint.toFixed(8));
					}
					return _ovint;
				},
				getMeIsDrawMoney:function(item){ //用户提现：0=可以提现，3=提现审核中，(1=通过审核，2=已据绝)
					var _str = "";
					if(item.status == "wc-completed"){
						var _tomoney = parseInt(item.tomoney);
						if(_tomoney == 0){
							_str = lang_var.tab_menu.me.lab.draw_money_ok;
						}else if(_tomoney == 1){
							_str = lang_var.tab_menu.me.lab.draw_money_verify_passport;
						}else if(_tomoney == 2){
							_str = lang_var.tab_menu.me.lab.draw_money_verify_decline;
						}else if(_tomoney == 3){
							_str = lang_var.tab_menu.me.lab.draw_money_verifing;
						}
					}else if(item.status == "wc-processing"){
						_str = lang_var.tab_menu.me.lab.draw_money_wait_completed;
					}
					return _str;
				},
				getOrderDate:function(item){//取下单日期
					var date_created = item.date_created;
					var date_created_gmt = item.date_created_gmt;
					var oid = item.order_id;
					var _str = '['+oid+'] ' + date_created + ' | ' + date_created_gmt;
					return _str;
				},
			}
		});
	}else{
		capital_details_vue.capital_details = __capital_details;
	}
	capital_details = layui.$('#capital_details');
	layer.open({
		type: 1,
		title: getConfig2(result_obj, 'id', id).email,
		area: ['55%', '85%'],
		content: capital_details //这里content是一个DOM，注意：最好该元素要存放在body最外层，否则可能被其它的相对元素所影响
		,cancel: function(){  //右上角关闭回调
			setTimeout(function(){
				capital_details[0].style.display = "none";
			}, 300);
			//return false 开启该代码可禁止点击该按钮关闭
		}
	});
}



var my_business_partner_vue;
function lookTeamOrderList(that, id){
	var my_business_partner = layui.$('#my_business_partner');
	my_business_partner[0].style.display = "block";
	
	var __my_business_partner = team_order_content_obj[id];
		//我的团队
		if(!my_business_partner_vue){
			my_business_partner_vue = new Vue({
				el: '#my_business_partner',
				data: {
					tab_menu:lang_var.tab_menu,
					my_business_partner:__my_business_partner,
					my_team_member_total:my_team_member_total,
					my_team_money_total:my_team_money_total.toFixed(2)
				},
				methods:{
					getMinMoney:function(item){//取最小的金额
						var _money = parseFloat(getItemMinMoney(item));
						var money_proportion = _money * commission_proportion;
						var _str = _money.toFixed(2);
						var _tomoney = parseInt(item.tomoney);
						if(item.status == "wc-completed" && _tomoney == 0){
							_str = _str + "(" + money_proportion.toFixed(2) + ")";
						}else{
							_str = _str;
						}
						return _str;
					},
					getInterest:function(item){//抽成
						var _ovint = "";
						if(item.status == "wc-completed"){
							_ovint = getLevelCommissionParam(item);
							var _money = parseFloat(getItemMinMoney(item));
							var money_proportion = _money * commission_proportion;
							_ovint = _ovint * money_proportion;
							_ovint = _ovint.toFixed(2);
						}
						return _ovint;
					},
					getMeIsDrawMoney:function(item){ //用户提现：0=可以提现，3=提现审核中，(1=通过审核，2=已据绝)
						var _str = "";
						if(item.status == "wc-completed"){
							var _tomoney = parseInt(item[getLevelCommissionDrawmoney(item)]);
							if(_tomoney == 0){
								_str = lang_var.tab_menu.me.lab.draw_money_ok;
							}else if(_tomoney == 1){
								_str = lang_var.tab_menu.me.lab.draw_money_verify_passport;
							}else if(_tomoney == 2){
								_str = lang_var.tab_menu.me.lab.draw_money_verify_decline;
							}else if(_tomoney == 3){
								_str = lang_var.tab_menu.me.lab.draw_money_verifing;
							}
						}else if(item.status == "wc-processing"){
							_str = lang_var.tab_menu.me.lab.draw_money_wait_completed;
						}
						return _str;
					},
					getOrderDate:function(item){//取下单日期
						var levelColor = getLevelCommissionColor(item);
						var buyer_email= item.email;
						var date_created_gmt = item.date_created_gmt;
						var oid = item.order_id;
						var _str = '['+oid+'] ' + levelColor + ' | ' + buyer_email + ' | ' + date_created_gmt;
						return _str;
					}
				}
			});
		}else{
			my_business_partner_vue.my_business_partner = __my_business_partner;
		}
		

	my_business_partner = layui.$('#my_business_partner');
	layer.open({
		type: 1,
		title: getConfig2(result_obj, 'id', id).email,
		area: ['55%', '85%'],
		content: my_business_partner //这里content是一个DOM，注意：最好该元素要存放在body最外层，否则可能被其它的相对元素所影响
		,cancel: function(){  //右上角关闭回调
			setTimeout(function(){
				my_business_partner[0].style.display = "none";
			}, 300);
			//return false 开启该代码可禁止点击该按钮关闭
		}
	});
}

</script>



<br/>

<!-- 订单明细 -->
<div id="capital_details" class="mui-page" style="z-index: 12; display:none;">
	<div class="mui-page-content">
		<div class="mui-content">
			<div class="mui-scroll">
				<div class="mui-card" style=" margin: 10px 10px 5px 10px; ">
					<ul class="mui-table-view mui-grid-view mui-grid-9" style="background-color: #FFFFFF;border-top:none;margin: 8px 8px 8px 0px;padding: 0px;">
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.order_status}}
						</li>
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.total_money}}({{tab_menu.me.lab.commission}})
						</li>
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.order_total_interest}}
						</li>
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.order_draw_money_status}}
						</li>
					</ul>
				</div>
				<div class="mui-card" style=" box-shadow: 0 0px 0px rgba(0,0,0,0); margin: 0px; ">
					<ul class="mui-table-view" style="margin: 0px 0px 8px 0px;padding: 1px 0px 1px 0px; ">
						<div class="mui-card" style="box-shadow: 0 0px 1px rgba(0,0,0,.8);" v-for="item in capital_details">
							<li class="mui-table-view-cell" style="margin: 0px 0px 0px 0px;padding: 0px; border-top: 1px solid #eee;">
								<ul class="mui-table-view mui-grid-view mui-grid-9" style="background-color: #FFFFFF;border-top:none;margin: 8px 8px 8px 0px;padding: 0px;">
									<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
										{{item.status}}
									</li>
									<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #FF5053;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
										${{getMinMoney(item)}}
									</li>
									<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #ee00ff;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
										${{getInterest(item)}}
									</li>
									<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
										{{getMeIsDrawMoney(item)}}
									</li>
								</ul>
								<div class="mui-card-footer" style="font-size: 12px; min-height: 24px;padding: 4px 6px; border-radius: 0 0 0px 0px; background-color:#f0f8ff;">{{getOrderDate(item)}}</div>
							</li>
						</div>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- 我的团队 -->
<div id="my_business_partner" class="mui-page" style="z-index: 12; display:none;">
	<div class="mui-page-content">
		<div class="mui-content">
			<div class="mui-scroll">
				<div class="mui-card" style=" margin: 10px 10px 5px 10px; ">
					<ul class="mui-table-view mui-grid-view mui-grid-9" style="background-color: #FFFFFF;border-top:none;margin: 8px 8px 8px 0px;padding: 0px;">
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.order_status}}
						</li>
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.total_money}}({{tab_menu.me.lab.commission}})
						</li>
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.order_total_cut}}
						</li>
						<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
							{{tab_menu.me.lab.order_draw_money_status}}
						</li>
					</ul>
				</div>
				<div class="mui-card" style=" box-shadow: 0 0px 0px rgba(0,0,0,0); margin: 0px; ">
				<ul class="mui-table-view" style="margin: 0px 0px 8px 0px;padding: 1px 0px 1px 0px; ">
					<div class="mui-card" style="box-shadow: 0 0px 1px rgba(0,0,0,.8);" v-for="item in my_business_partner">
						<li class="mui-table-view-cell" style="margin: 0px 0px 0px 0px;padding: 0px; border-top: 1px solid #eee;">
							<ul class="mui-table-view mui-grid-view mui-grid-9" style="background-color: #FFFFFF;border-top:none;margin: 8px 8px 8px 0px;padding: 0px;">
								<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3" 
								style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
									{{item.status}}
								</li>
								<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3"
								style="font-size: 8px;padding: 0px;vertical-align: middle;color: #FF5053;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
									${{getMinMoney(item)}}
								</li>
								<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3"
								style="font-size: 8px;padding: 0px;vertical-align: middle;color: #ee00ff;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
									${{getInterest(item)}}
								</li>
								<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3"
								style="font-size: 8px;padding: 0px;vertical-align: middle;color: #000000;word-wrap: break-word;word-break: normal;border-color: #FFFFFF;">
									{{getMeIsDrawMoney(item)}}
								</li>
							</ul>
							<div class="mui-card-footer" style="font-size: 12px; min-height: 24px;padding: 4px 6px;background-color: #f0f8ff;border-radius: 0 0 0px 0px;" v-html="getOrderDate(item)">
								{{getOrderDate(item)}}</div>
						</li>
					</div>
				</ul>
				</div>
			</div>
		</div>
	</div>
</div>





</body>
</html>
