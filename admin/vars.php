
<?php function getDisposeStatusCombobox($dispose_status, $eleid, $selid){ /*$eleid为select的ID，$selid选择的index ID*/?>
	<div id="<?php echo $eleid; ?>_div" class="layui-form-item" style="width:80px;font-size: 12px;margin-bottom: 4px; margin-top: 4px;">
			<select id="<?php echo $eleid; ?>">
				<?php foreach ($dispose_status as $key => $value): ?>
					<option value="<?php echo $value; ?>"><?php echo $key; ?></option>
				<?php endforeach ?>
			</select>
			<script>
				var selid = '<?php echo isset($selid) ? $selid : ""; ?>';
				var tete = document.getElementById("<?php echo $eleid; ?>");
				tete.selectedIndex = 0;
				for(var i=0;i<tete.options.length;i++){
					if(tete.options[i].value == selid){
						tete.selectedIndex = i;
						break;
					}
				}
				setTimeout(function(){
					var tete_div = document.getElementById("<?php echo $eleid; ?>_div");
					var tete_inp = tete_div.getElementsByTagName("input")
					//console.log(tete_inp[0].value);
					tete_inp[0].style["padding-right"] = "0px";
				}, 3000);
			</script>
	</div>
<?php } ?>

<?php function getDisposeStatusCombobox2($eleid){ global $dispose_status; /*$eleid为select的ID，$selid选择的index ID*/?>
	<div id="<?php echo $eleid; ?>_div" class="layui-form-item" style="margin: 8px; width:80px;font-size: 12px;display: flex;">
			<select id="<?php echo $eleid; ?>">
				<?php foreach ($dispose_status as $key => $value): ?>
					<option value="<?php echo $value; ?>"><?php echo $key; ?></option>
				<?php endforeach ?>
			</select>&nbsp;
			<button onclick="onOkClick_bacth_changeSign(this);" type="button" class="layui-btn layui-btn-primary layui-btn-sm">确定</button>
			<script>
				function onOkClick_bacth_changeSign(_this){
					var id_arr = [];
					var _arr = document.querySelectorAll("input[type='checkbox']");
					for(var i=0;i<_arr.length;i++){
						if(_arr[i].checked == true){
							id_arr.push(_arr[i].title);
						}
					}
					if(id_arr.length<=0){
						layui.layer.msg("没有选中的订单");
						return;
					}
					
					var winsign = document.getElementById("winsign");
					var status = winsign.options[winsign.selectedIndex].value;
					var _ind = layui.layer.load(2);
					layui.jquery.get("./orderlistchild.php?action=2&f=status&v="+status+"&id="+String(id_arr), function(rel){
						layui.layer.close(_ind);
						var ret = JSON.parse(rel);
						if(ret && ret.ret == 200){
							layui.layer.msg("修改成功");
							window.location.reload();
						}
					});
				}
			</script>
	</div>
<?php } ?>

<?php

require './varstatus.php';


//echo array_search("2",$dispose_status);
//echo getDisposeStatusCombobox($dispose_status, "tete", "3");

function getStatusCountStr($dispose_status){
	$str = '';
	global $pdo;
	global $status;
	foreach ($dispose_status as $key => $value){
		//echo $value;
		//echo $key;
		$sql = "select count(id) from orderlist where status={$status} and status={$value}";
		//echo $sql;
		$rel  = $pdo->query($sql)->fetchAll();
		$cou = $rel[0][0];
		$str = $str."<span style='padding-right: 20px;'><a style='color:#1E9FFF;' href='?status={$status}&a=2&v={$value}'>【{$key}】</a><font color='#FF0000'><b>{$cou}</b></font> 笔</span>";
	}
	return $str;
}



?>
