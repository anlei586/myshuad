<?php function renderOrderTable($result){ ?>
<div class="layui-form">
<br/><button onclick="" type="button" class="layui-btn layui-btn-danger layui-btn-sm">批量确认提现</button>
<table style="margin-left: 1%; width: 98%;" class="layui-table">
  <colgroup>
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
      <th style="text-align: center;">Paypal</th>
      <th style="text-align: center;">注册日期</th>
      <th style="text-align: center;">提现总额</th>
      <th style="text-align: center;">操作</th>
      <th style="text-align: center;">确认提现</th>
    </tr> 
  </thead>
  <tbody>
  <?php foreach ($result as $key => $value):  $_id=$value["id"];?>
    <tr id="ytr_<?php echo $_id; ?>">
      <td><input id="id_<?php echo $_id; ?>" type="checkbox" name="" title="<?php echo $_id; ?>" lay-skin="primary"></td>
      <td><?php echo $value['email']; ?></td>
      <td><?php echo $value["paypal"]; ?></td>
      <td><?php echo $value["date"]; ?></td>
      <td><?php echo $value["drawmoneyreco"]; ?></td>
      <td>
			<button onclick="confriDrawMoney(this,'<?php echo $_id; ?>');" type="button" class="layui-btn layui-btn-danger layui-btn-sm"><i class="layui-icon">&#xe640;</i>查看他的订单</button>
			<hr/>
			<button onclick="confriDrawMoney(this,'<?php echo $_id; ?>');" type="button" class="layui-btn layui-btn-danger layui-btn-sm"><i class="layui-icon">&#xe640;</i>查看他的团队订单</button>
	  </td>
      <td>
			<button onclick="confriDrawMoney(this,'<?php echo $_id; ?>');" type="button" class="layui-btn layui-btn-danger layui-btn-sm"><i class="layui-icon">&#xe640;</i>确认提现</button>
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
	}

}


$num = isset($_GET['num'])?$_GET['num']:10;
$sv = '';

if(isset($_GET['v'])){
	$sv = $_GET['v'];
	$sql = 'SELECT id from mission_user WHERE title like "%'.$sv.'%" order by id DESC';
	
	$db  = $pdo->query($sql)->fetchAll();
	$total = count($db);

	$cpage = isset($_GET['page'])?$_GET['page']:1;
	$offset = ($cpage-1)*$num;

	$sql = "SELECT * from mission_user WHERE title like '%{$sv}%' order by id DESC  limit {$offset},{$num}";
	//echo $sql;
	$result = $pdo->query($sql)->fetchAll();
}else{
	$sql = "select id from mission_user order by id DESC";
	$db  = $pdo->query($sql)->fetchAll();
	$total = count($db);

	$cpage = isset($_GET['page'])?$_GET['page']:1;
	$offset = ($cpage-1)*$num;

	$sel = isset($_GET['sel'])?$_GET['sel']:"";
	if(empty($sel)){
		$ob = "id";
	}else{
		$ob = "flow";
	}
	$sql = "select * from mission_user order by {$ob} DESC  limit {$offset},{$num}";
	$result  = $pdo->query($sql)->fetchAll();

}


?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>所有用户</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="./layui/css/layui.css">
	<style>
		::-webkit-scrollbar {width: 14px;height: 14px;}
		::-webkit-scrollbar-track {background: rgba(241, 241, 242, 1);}
		::-webkit-scrollbar-thumb {background-color: #33333375;}
		::-webkit-scrollbar-thumb:window-inactive {background: #d2d2d2;}
	</style>
</head>
<body>
<script>
var curr_uid = -1;
var currusername = '';
var search_v = "<?php echo $sv; ?>";


function replace(str, flag, rep){
	str = str.split(flag).join(rep);
	return str;
}

</script>

<center><h1>所有用户</h1></center>
<div class="layui-form-item" style="margin-bottom:0px; padding: 10px;text-align: center;display: flex;">
	<input type="text" name="value_txt" id="value_txt" required lay-verify="required" placeholder="请输入要搜索商品标题" autocomplete="off" class="layui-input" style="text-align:center;padding-left: 15%;padding-top: 0px;width: 85%;display: inline;" value="<?php echo $sv; ?>" onkeyup="onKeyUp(this, event)">
	<button class="layui-btn" style="width: 15%;text-align: center;margin-left: 4px;" onclick="onSearch();"><i class="layui-icon">&#xe615;</i> 搜索</button>
</div>


<div id="content_div" style="text-align:center;">
	<?php echo "<span class='layui-badge layui-bg-gray'>共有<b>$total</b>个商品</span>" ?>
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


});

//搜索按钮事件
function onSearch(){
	var v = value_txt.value;
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
// 按流量排序
function onFlowSort(_this){
	var _url = "?page=1&sel=1&num="+limit;
	window.location.href = _url;
}



</script>

<br/>
</body>
</html>