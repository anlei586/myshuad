//layui 初始化完成调用
function initLayui2(){
	//签收状态更改
	form.on('select', function(data){
		var selectid = data.elem.id;
		var dsc_arr = selectid.split("dsc_");
		if(dsc_arr.length>1){
			var index = data.elem.selectedIndex;
			var val = data.elem.options[index].value;
			
			var _ind = layui.layer.load(2);
			layui.jquery.get("./orderlistchild.php?action=1&f=status&v="+val+"&id="+dsc_arr[1], function(rel){
				layui.layer.close(_ind);
				var ret = JSON.parse(rel);
				if(ret && ret.ret == 200){
					layui.layer.msg("修改成功");
					console.log(rel);
				}
			});
		}
	});
}
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
//回车搜索
function onKeyUp(obj, event){
	if(event.keyCode == 13){
		onSearch()
	}
}
///////////////////////////////////////
//弹窗
function popBox(v,t, fn){
	layer.prompt({
		formType: 2,
		value: v,//'初始值',
		title: t,//'请输入值',
		area: ['200px', '50px']
		}, function(value, index, elem){
			value = replace(value, '\n', '');
			value = replace(value, '\r', '');
			fn(value); //得到value
			layer.close(index);
	});
}

var _dbEleObj;//被双击的格子
var _updateAction;//要更新的动作
var _updateF;//要更新的字段
var _updateId;//要更新的ID

//弹窗修改确实的回调
function popBoxBack(val){
	var _ind = layui.layer.load(2);
	layui.jquery.get("./orderlistchild.php?action="+_updateAction+"&f="+_updateF+"&v="+val+"&id="+_updateId, function(rel){
		layui.layer.close(_ind);
		var ret = JSON.parse(rel);
		if(ret && ret.ret == 200){
			layui.layer.msg("修改成功");
			_dbEleObj.innerText = val;
			console.log(val);
		}
	});
}
//双击弹窗修改
function popModifyBox(_this,updateId,updateF,_lab){
	_dbEleObj=_this;
	_updateAction=1;
	_updateF=updateF;
	_updateId=updateId;
	_arr = _this.innerText.split("-(");
	popBox(_arr[0], _lab, popBoxBack);
}

//删除单个订单按钮事件
function onDeleteOrder(_this,id){
	setOrderStatus(_this, id, 2, 1);
}
//还原单个订单按钮事件
function onReplyOrder(_this,id){
	setOrderStatus(_this, id, 0, 1);
}
function setOrderStatus(_this, id, statu, action,isremove){
	var _ind = layui.layer.load(2);
	layui.jquery.get("./orderlistchild.php?action="+action+"&f=status&v="+statu+"&id="+id, function(rel){
		layui.layer.close(_ind);
		var ret = JSON.parse(rel);
		if(ret && ret.ret == 200){
			layui.layer.msg("删除成功");
			if(!isremove){
				_this.parentElement.parentElement.remove();
			}else{
				isremove();
			}
		}
	});
}
//批量删除订单按钮事件
function onTopBtnDeleteOrders(_this){
	setOrdersStatus(_this, 2);
}

//批量还原订单按钮事件
function onTopBtnReplyOrders(_this){
	setOrdersStatus(_this, 0);
}

function setOrdersStatus(_this, statu){
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
	setOrderStatus(_this, String(id_arr), statu, 2, function(){window.location.reload();});
}

//回收站
function onTopBtnRubshClick(_this){
	var _ind = layui.layer.load(2);
	window.location = '?status=2';
}
//返回订单列表
function onTopBtnOrderListClick(_this){
	var _ind = layui.layer.load(2);
	window.location = './orderlist.php';
}
//全/反选订单ID
function onOrderIdSelectAll(_this){
	var _arr = document.querySelectorAll("input[type='checkbox']");
	for(var i=0;i<_arr.length;i++){
		_arr[i].checked = !_arr[i].checked;
	}
	layui.form.render();
}

//更改签收状态
function onTopBtnChangeSignClick(_this){
	layui.layer.open({
		type: 1,
		title: '更改签收状态',
		content: layui.jquery("#pop_change_sign_div")
	});
}
//导入运单号
function onTopBtnInportLognoClick(_this){
	layui.layer.open({
		type: 1,
		title: '导入运单号',
		content: layui.jquery("#pop_inport_logno_div")
	});
}

function sendDiyFV($action,$field,$val,$id, $back){
	var _ind = layui.layer.load(2);
	layui.jquery.get("./orderlistchild.php?action="+$action+"&f="+$field+"&v="+$val+"&id="+$id, function(rel){
		layui.layer.close(_ind);
		var ret = JSON.parse(rel);
		if(ret && ret.ret == 200){
			layui.layer.msg($id+"修改成功");
		}
		if($back) $back();
	});
}

var logno_items_arr;

//批量导入确定按钮。
function onOkClick_bacth_inportLogno(_this){
	var logno_txt = document.getElementById('logno_txt');
	if((!logno_items_arr || logno_items_arr.length<=0) && logno_txt.value==""){
		layui.layer.alert("导入运单号完成，刷新后可显示");
		return;
	}
	if(!logno_items_arr || logno_items_arr.length<=0){
		logno_items_arr = logno_txt.value.split("\n");
		logno_txt.value = "";
	}
	var _itemArr = logno_items_arr.shift();
	var _arr = _itemArr.split("	");
	var _orderid = _arr[0];
	var _logno = _arr[1];
	sendDiyFV(2,'logno', _logno, _orderid, onOkClick_bacth_inportLogno);
}

//
function aiDisposeAddr($id){
	var _msgEle = document.getElementById('msg_td_'+$id);
	var _msgStr = _msgEle.innerText;
	var numArr = _msgStr.match(/\d+/g);
	if(!numArr || (numArr && numArr.length<=0)){
		_msgStr = _msgStr.innerText;
	}else{
		_msgStr = '';
		for(var i=0; i<numArr.length; i++){
			if(numArr[i].length > _msgStr.length){
				_msgStr = numArr[i];
			}
		}
	}
	var _firstIsZero = false;
	if(_msgStr.charAt(0) == 0){
		_firstIsZero = true;
		_msgStr = _msgStr.substring(1);
	}
	_msgStr = _msgEle.innerText.replace(_msgStr, "13" + _msgStr);
	console.log(_msgStr);

	var _ind = layui.layer.load(2);
	layui.jquery.get("../lib/aipnlp.php?text="+_msgStr, function(rel){
		var ret = JSON.parse(rel);
		if(ret.hasOwnProperty("ret") && ret.ret==301){
			layui.layer.msg(ret.msg);
			return;
		}
		person = jttoft(ret.person);
		phonenum = ret.phonenum.substring(2);
		if(_firstIsZero){
			phonenum = '0' + phonenum;
		}
		var addres = _msgEle.innerText.replace(person,"");
		addres = addres.replace(phonenum,"");
		console.log(person +','+ phonenum +','+ addres);
		
		layui.jquery.get("./orderlistchild.php?action=4&id="+$id+"&oname="+person+"&otel="+phonenum+"&oaddr="+addres, function(rel){
			layui.layer.close(_ind);
			console.log(rel);
			var ret = JSON.parse(rel);
			if(ret && ret.ret == 200){
				layui.layer.msg($id+"智能解析地址完成");
				var oname_td =document.getElementById('oname_td_'+$id);
				var otel_td =document.getElementById('otel_td_'+$id);
				var oaddr_td =document.getElementById('oaddr_td_'+$id);
				oname_td.innerText = person;
				otel_td.innerText = phonenum;
				oaddr_td.innerText = addres;
			}
		});


	});
}


function ftPYStr(){
	return '';
}
function simpPYStr(){
	return '';
}
function jttoft(cc){
	var str='';
	for(var i=0;i<cc.length;i++){
	if(simpPYStr().indexOf(cc.charAt(i))!=-1)
		str+=ftPYStr().charAt(simpPYStr().indexOf(cc.charAt(i)));
	else
		str+=cc.charAt(i);
	}
	return str;
}
function fttojt(cc){
	var str='';
	for(var i=0;i<cc.length;i++){
	if(ftPYStr().indexOf(cc.charAt(i))!=-1)
		str+=simpPYStr().charAt(ftPYStr().indexOf(cc.charAt(i)));
	else
		str+=cc.charAt(i);
	}
	return str;
}
////////////////////////////