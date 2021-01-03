<?php function onLoopItem($result){ ?>
	<?php foreach ($result as $key => $value):  $_id=$value["id"]; $_vars=$value["key"]; ?>
		<div class="layui-form-item anleidiv">
			<label class="layui-form-label" style="width: 70%;"><?php echo $value["desc"]; ?></label>
			<input type="text" name="<?php echo $_vars; ?>_txt" id="<?php echo $_vars; ?>_txt" autocomplete="off" class="layui-input anleiinput" value="<?php echo $value["value"]; ?>">
			<button class="layui-btn anleisavebtn" onclick="onSave(<?php echo $_id; ?>,<?php echo $_vars; ?>_txt.value);">保存</button>
		</div>
	<?php endforeach ?>
<?php } ?>

<?php

require './utils.php';
require './conn.php';

$pdo = new PDO($dsn, $user, $pass);

if(isset($_GET['action'])){
	$action = $_GET['action'];
	if($action == 1){
		$id = zyyhj($_GET['id']);
		$value = zyyhj($_GET['value']);
		$sql = 'UPDATE mission_config SET value="'.$value.'" WHERE id='.$id.'';
		$pdo->exec($sql);
		echo '{"ret":200}';
		die();
		return;
	}
}

$sql = "select * from mission_config";
$result  = $pdo->query($sql)->fetchAll();


?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>所有商品</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="./layui/css/layui.css">
	<style>
		::-webkit-scrollbar {width: 14px;height: 14px;}
		::-webkit-scrollbar-track {background: rgba(241, 241, 242, 1);}
		::-webkit-scrollbar-thumb {background-color: #33333375;}
		::-webkit-scrollbar-thumb:window-inactive {background: #d2d2d2;}
		.anleidiv{
			margin-bottom:0px; padding: 10px;text-align: center;display: flex;
		}
		.anleiinput{
			text-align:center;padding-right: 10px;padding-top: 0px;width: 85%;display: inline;
		}
		.anleisavebtn{
			width: 15%;text-align: center;margin-left: 4px;
		}
	</style>
</head>
<body>
<script>

function replace(str, flag, rep){
	str = str.split(flag).join(rep);
	return str;
}

</script>


<br/><center><h1>设置</h1><br/>
<div style="width:80%;">
	<?php echo onLoopItem($result); ?>
</div>
</center>

<script src="./layui/layui.all.js"></script>
<script>
var content_div = document.getElementById('content_div');
var value_txt = document.getElementById('value_txt');
var isFirst = true;

//搜索按钮事件
function onSave(id, value){
	var v = value;
	if(!v || (v && v.length<=0)){
		layui.layer.msg("不能为空");
		return;
	}
	var _ind = layer.load(2);
	layui.$.get("?action=1&id="+id+"&value="+value, function(rel){
		layui.layer.close(_ind);
		if(rel){
			var obj = JSON.parse(rel);
			if(obj && obj.hasOwnProperty("ret") && obj.ret==200){
				layui.layer.msg("保存成功");
				return;
			}
		}
		layui.layer.msg(rel);
	});
}

</script>

<br/>
</body>
</html>