<?php
ini_set('display_errors',1); //错误信息 
ini_set('display_startup_errors',1); //php启动错误信息 
error_reporting(-1); //打印出所有的 错误信息
?>
<?php function renderOrderTable($result, $dispose_status){
global $status; 
global $total;
global $dayhmd_total;
global $onc_arr;
global $pdo;
?>
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
    <col width="5%">
    <col width="5%">
    <col width="5%">
    <col width="5%">
  </colgroup>
  <thead>
  	<tr style="background-color: #f7f7f7;">
		<td colspan="18" style="text-align: left; ">
			<button onclick="onTopBtnChangeSignClick(this);" type="button" class="layui-btn layui-btn-normal layui-btn-sm"><i class="layui-icon">&#xe716;</i>批量更改状态</button>
			<button onclick="onAutoChangeOrderTwoDay(this);" type="button" class="layui-btn layui-btn-normal layui-btn-sm"><i class="layui-icon">&#xe716;</i>自动批量更改>2天的订单状态</button>
			<?php
			echo "<span style='padding-right: 20px;'>共查询到<font color='#FF0000'><b> {$total} </b></font>笔订单</span><span style='padding-right: 20px;'>今日有<font color='#FF0000'><b> {$dayhmd_total} </b></font>笔订单</span> 双击创建日期可编辑";
			?>
		</td>
	</tr>
    <tr>
      <th style="text-align: center; cursor: pointer;" onclick="onOrderIdSelectAll(this);">订单ID</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" title='双击以下内容可编辑'>创建日期1</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" title='双击以下内容可编辑'>创建日期2</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >数量</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >价格1</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >价格2</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >状态</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >买家提现</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >1级提现</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >2级提现</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >3级提现</th>
      <th style="text-align: center; border-bottom-color: #673AB7;" >复购次数</th>
    </tr> 
  </thead>
  <tbody>
  <?php $iid=0; foreach ($result as $key => $value): 
  	$iid++;
	$order_id = zyyhj($value["order_id"]);
	$_id = $order_id;
	$date_created = zyyhj($value["date_created"]);
	$date_created_gmt = zyyhj($value["date_created_gmt"]);
	$num_items_sold = zyyhj($value["num_items_sold"]);
	$total_sales = zyyhj($value["total_sales"]);

	//查订单的使用优惠券
	$me_order_coupon_sql = 'SELECT order_id,discount_amount FROM sd_wc_order_coupon_lookup where order_id in('.$order_id.')';
	$me_order_coupon_result = $pdo->query($me_order_coupon_sql)->fetchAll(PDO::FETCH_ASSOC);
	$me_order_coupon_count = count($me_order_coupon_result);//优惠券总数
	//var_dump($me_order_coupon_result);
	//把优惠券的折价加到net_total字段里
	for($k=0;$k<$me_order_coupon_count;$k++){
		if($value['order_id'] == $me_order_coupon_result[$k]['order_id']){
			$value['net_total'] = floatval($value['net_total']) + floatval($me_order_coupon_result[$k]['discount_amount']);
		}
	}
	$net_total = zyyhj($value["net_total"]);

	$status = zyyhj($value["status"]);
	$tomoney = zyyhj($value["tomoney"]);
	$tomoney1 = zyyhj($value["tomoney1"]);
	$tomoney2 = zyyhj($value["tomoney2"]);
	$tomoney3 = zyyhj($value["tomoney3"]);
	$commission_scale = zyyhj($value["commission_scale"]);
  ?>
    <tr id="ytr_<?php echo $_id; ?>">
      <td><input id="id_<?php echo $_id; ?>" type="checkbox" name="" title="<?php echo $_id; ?>" lay-skin="primary"></td>
      <td ondblclick="popModifyBox(this,<?php echo $_id; ?>,'date_created','创建日期1');"><?php echo $date_created; ?></td>
      <td ondblclick="popModifyBox(this,<?php echo $_id; ?>,'date_created_gmt','创建日期2');"><?php echo $date_created_gmt; ?></td>
      <td><?php echo $num_items_sold; ?></td>
      <td><?php echo $total_sales; ?></td>
      <td><?php echo $net_total; ?></td>
      <td ondblclick="popModifyBox(this,<?php echo $_id; ?>,'status','状态');"><?php echo getDisposeStatusCombobox($dispose_status, "dsc_".$_id, $status);  ?></td>
      <td><?php echo $tomoney; ?></td>
      <td><?php echo $tomoney1; ?></td>
      <td><?php echo $tomoney2; ?></td>
      <td><?php echo $tomoney3; ?></td>
      <td><?php echo $commission_scale; ?></td>
    </tr>
  <?php endforeach ?>
  </tbody>
</table>
</div>
<?php } ?>
<?php

$dayhmd = date("Y-m-d");

require './vars.php';
require './utils.php';
require './conn.php';

//require './orderlistchild.php';



$pdo = new PDO($dsn, $user, $pass);

$action = isset($_GET['action'])?$_GET['action']:"";
if(!empty($action)){
	if($action == 3){
		$sql = "UPDATE sd_wc_order_stats set status='wc-completed' WHERE status='wc-processing' and TO_DAYS( NOW( ) ) - TO_DAYS( date_created_gmt) >= 2";
		$pdo->query($sql);
		exit('{"ret":200}');
	}
}



$num = isset($_GET['num'])?$_GET['num']:10;

$sql = "select order_id from sd_wc_order_stats order by order_id DESC";
$db  = $pdo->query($sql)->fetchAll();
$total = count($db);

$cpage = isset($_GET['page'])?$_GET['page']:1;
$offset = ($cpage-1)*$num;
$sql = "select * from sd_wc_order_stats order by order_id DESC  limit {$offset},{$num}";
$result  = $pdo->query($sql)->fetchAll();

//今日有几单。
$sql = "select order_id from sd_wc_order_stats where date_created like '%{$dayhmd}%' order by order_id DESC";
$db  = $pdo->query($sql)->fetchAll();
$dayhmd_total = count($db);


?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>所有订单</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="./layui/css/layui.css">
	
<script>
var curr_uid = -1;
var currusername = '';
var search_v = "<?php echo $sv; ?>";
var status = "<?php echo $status; ?>";

function getKey(str, sk, ek) {
	var s = str.indexOf(sk);
	if(s == -1){
		return '-1';
	}
	var st = str.substr(s+sk.length);
	var e = st.indexOf(ek);
	str = str.substring(s + sk.length, s + sk.length + e);
	return str;
}
function getKeyLastms(str, sk, ek){
	var s = str.lastIndexOf(ek);
	if(s == -1){
		return '-1';
	}
	var st = str.substr(0,s);
	var e = st.lastIndexOf(sk);
	str = str.substring(e + sk.length, s);
	return str;
}
function replace(str, flag, rep){
	str = str.split(flag).join(rep);
	return str;
}





</script>

	<script src="./layui/orderlist.js"></script>
	<style>
		::-webkit-scrollbar {width: 14px;height: 14px;}
		::-webkit-scrollbar-track {background: rgba(241, 241, 242, 1);}
		::-webkit-scrollbar-thumb {background-color: #33333375;}
		::-webkit-scrollbar-thumb:window-inactive {background: #d2d2d2;}
	</style>
</head>
<body>


<div id="content_div" style="text-align:center;">
	<?php renderOrderTable($result, $dispose_status); ?>
	<br/><br/><br/>
	<div id="orderlist_page" style="position: fixed;width: 100%;bottom: 0px;background-color: white;border-width: 1px; border-style: solid; border-color: #ffffff; border-top-color: #e6e6e6; padding-top: 6px;">
	</div>
</div>

<div id="pop_change_sign_div" style="display:none;">
	<?php echo getDisposeStatusCombobox2("winsign"); ?>
</div>
<div id="pop_inport_logno_div" class="layui-form-item" style="display:none;text-align: center;margin: 8px;width: 260px;height: 300px;">
	<textarea id="logno_txt" name="logno_txt" placeholder="订单号+分隔符+运单号
分隔符为\tab即从Execl表格中复制出来的符号" class="layui-textarea" style="height: 80%;"></textarea><br>
	<button onclick="onOkClick_bacth_inportLogno(this);" type="button" class="layui-btn layui-btn-primary layui-btn-sm" style="width: 50%;">确定</button>
</div>


<script src="./layui/layui.all.js"></script>
<script>
var content_div = document.getElementById('content_div');
var value_txt = document.getElementById('value_txt');
var search_content_div = document.getElementById('search_content_div');
var laytpl,layer,form,laypage;
var isFirst = true;

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
				var _url = "?page="+obj.curr+"&num="+obj.limit+"&status="+status;
				if(search_v){
					_url = _url + '&v='+search_v;
				}
				window.location.href = _url;

			}
			isFirst = false;
		}
	});

	initLayui2();
});

function onAutoChangeOrderTwoDay(that){
	var ind = layer.confirm('确定？', 
	{
		btn: ['确定', '取消'] //可以无限个按钮
	},
	function(index, layero) {
		layer.close(ind);
		var _ind = layui.layer.load(2);
		layui.jquery.get("?action=3", function(rel){
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


</script>
<br/></body></html>
