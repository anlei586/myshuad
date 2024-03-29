
function initLayui3(){
	
	
	//选择国家
	layui.form.on('select(country)', function(data){
		console.log(data.elem); console.log(data.value); console.log(data.othis);
		selectedCountry(data.value);//切换国家
		country = data.value;//记录当前切换的国家
		onPayTypeClick({value:"cod"});//切换配送方式 
		onRzPrice();//重计当前国家货币的价格
		reSetCoinToText();//切换货币符号
	}); 
	//自动判断国家
	$("select[name=country]").val(country);
	selectedCountry(country);
	
	//配送方式选择
	layui.form.on('radio(paytype)', onPayTypeClick);
	
	//省份选择-tw cod 1
	layui.form.on('select(tw_cod_1)', onSelectTwCod_1);
	layui.form.on('select(tw_cod_2)', onSelectTwCod_2);
	
	//省份选择-tw 711 1
	layui.form.on('select(tw_711_1)', onSelectTw711_1);
	//地址选择-tw 711 2
	layui.form.on('select(tw_711_2)', onSelectTw711_2);
	//地址选择-tw 711 3
	layui.form.on('select(tw_711_3)', onSelectTw711_3);
	
	//省份选择-hk cod 1
	layui.form.on('select(hk_cod_1)', onSelectHkCod_1);
	layui.form.on('select(hk_cod_2)', onSelectHkCod_2);
	
	//初始显示
	onPayTypeClick({value:"cod"});
	
}

//按付款方式选择不同的配送方式
function onPayTypeClick(data){
	setAllAddressVal('');
	
	var select_tw_cod_addr = $("#select_tw_cod_addr")[0];
	var select_tw_711_addr = $("#select_tw_711_addr")[0];
	var select_hk_cod_addr = $("#select_hk_cod_addr")[0];

	var end_hk_cod_addr = $("#end_hk_cod_addr")[0];
	var end_tw_cod_addr = $("#end_tw_cod_addr")[0];
	var end_tw_711_addr = $("#end_tw_711_addr")[0];
	
	select_tw_711_addr.style.display = "none";
	select_tw_cod_addr.style.display = "none";
	select_hk_cod_addr.style.display = "none";
    
	end_hk_cod_addr.style.display = "none";
	end_tw_cod_addr.style.display = "none";
	end_tw_711_addr.style.display = "none";
    
	var _list = null;
	if(country =='tw'){
		if(data.value == "cod"){
			select_tw_cod_addr.style.display = "block";
			end_tw_cod_addr.style.display = "block";
			_list = findPidObj(5);//台灣cod
		}else if(data.value == "711"){
			select_tw_711_addr.style.display = "block";
			end_tw_711_addr.style.display = "block";
			_list = findPidObj(5);//台灣711
		}
	}else if(country =='hk'){
		if(data.value == "cod"){
			select_hk_cod_addr.style.display = "block";
			end_hk_cod_addr.style.display = "block";
			_list = findPidObj(1);//香港cod
		}
	}
	//显示省份。
	if(_list){
		var _str = getOptHtml(_list);
		if(country =='tw'){
			if(data.value == "cod"){//台灣cod  tw_cod_1  tw_cod_2
				appendOptElement("#tw_cod_1", _str);
				$("#tw_cod_2")[0].innerHTML = _defaultFirstOptHtml;
				layui.form.render("select");
			}else if(data.value == "711"){//台灣711  tw_711_1  tw_711_2  tw_711_3
				/*appendOptElement("#tw_711_1", _str);
				$("#tw_711_2")[0].innerHTML = _defaultFirstOptHtml;
				$("#tw_711_3")[0].innerHTML = _defaultFirstOptHtml;
				layui.form.render("select");*/
			}
		}else if(country =='hk'){
			if(data.value == "cod"){//香港cod  hk_cod_1  hk_cod_2  hk_cod_3
				appendOptElement("#hk_cod_1", _str);
				$("#hk_cod_2")[0].innerHTML = _defaultFirstOptHtml;
				layui.form.render("select");
			}
		}
	}
}

//选择国家时，切换付款方式
function selectedCountry($c){
	$("input[name=country]").val($c);
	setAllAddressVal('');
	var _val = $("input[name='paytype']");
	_val[0].setAttribute("disabled","");
	_val[1].setAttribute("disabled","");
	_val[2].setAttribute("disabled","");
	

	if($c=="tw"){
		_val[0].removeAttribute("disabled");
		_val[1].removeAttribute("disabled");
		_val[2].setAttribute("disabled","");
		$("input[name=paytype][value=711]").prop("checked","false");
		$("input[name=paytype][value=zsk]").prop("checked","false");
		$("input[name=paytype][value=cod]").prop("checked","true");
	}else if($c=="hk"){
		_val[0].setAttribute("disabled","");
		_val[1].setAttribute("disabled","");
		_val[2].removeAttribute("disabled");
		$("input[name=paytype][value=cod]").prop("checked","false");
		$("input[name=paytype][value=711]").prop("checked","false");
		$("input[name=paytype][value=zsk]").prop("checked","true");
	}
	layui.form.render();
}

//按父ID查找
function findPidObj(pid){
	var arr = [];
	var list = addr_json.list;
	var len = list.length;
	for(var i=0;i<len;i++){
		if(list[i].pid == pid){
			arr.push({id:list[i].id, name:list[i].name});
		}
	}
	return arr;
}

var _defaultFirstOptHtml = '<option value="">--請選擇--</option>';

//新建option节点元素
function getOptHtml(_list){
	var _str = '';
	for(var i=0; i<_list.length; i++){
		_str += '<option value="' + _list[i].id + '">'+_list[i].name+'</option>';
	}
	_str = _defaultFirstOptHtml + _str;
	return _str;
}

//把节点html添加到相应元素里。
function appendOptElement($eid, $str){
	$($eid)[0].innerHTML = $str;
	//$($eid).append($str);
	layui.form.render("select");
}
function appendOptElement2($eid, $pid){
	var _list = findPidObj($pid);
	var _str = getOptHtml(_list);
	appendOptElement($eid, _str);
}

function onSelectTwCod_1(data){
	setAllAddressVal('');
	appendOptElement2("#tw_cod_2", data.value);
}

function onSelectTwCod_2(data){
	var _v1 = $("#tw_cod_1 option:selected").text();
	var _v2 = $("#tw_cod_2 option:selected").text();
	setAllAddressVal(_v1 + _v2);
	setCurrAddressFocus("end_tw_cod_addr");
}

function onSelectTw711_1($data){
	setAllAddressVal('');
	var data = {
		commandid:'GetTown',
        city:'',
        town:'',
		cityid:$data.value
	};
	var loadInd = layui.layer.load(2, {time: 10*1000});
	$.post('./ajax_711.php?action=city',data,function(res){
		layui.layer.close(loadInd);
		$("#tw_711_2 option:gt(0)").remove();
		$("#tw_711_3 option:gt(0)").remove();
		var str_city = '';
		for (var i = 0; i < res['GeoPosition'].length; i++) {
			str_city += '<option value="'+res['GeoPosition'][i]['TownName']+'">'+res['GeoPosition'][i]['TownName']+'</option>';
		}
		$("#tw_711_2").append(str_city);
		layui.form.render("select");
	},'json');
}
function onSelectTw711_2($data){
	setAllAddressVal('');
	var data_area = {
		commandid:'SearchStore',
		city:$("#tw_711_1 option:selected").text(),
		town:$data.value,
        cityid:''
	};
	var loadInd = layui.layer.load(2, {time: 10*1000});
	$.post('./ajax_711.php?action=area',data_area,function(res){
		layui.layer.close(loadInd);
		$("#tw_711_3 option:gt(0)").remove();
		var str_area = '';
		if(res['GeoPosition']){
			for (var i = 0; i < res['GeoPosition'].length; i++) {
				str_area += '<option value="'+res['GeoPosition'][i]['Address']+'。 店名：'+res['GeoPosition'][i]['POIName']+ '。 店號：' + res['GeoPosition'][i]['POIID'].trim() +'。 電話：'+res['GeoPosition'][i]['Telno']+'">'+res['GeoPosition'][i]['POIName']+' — '+res['GeoPosition'][i]['Address']+'</option>';
			}
			$("#tw_711_3").append(str_area.trim());
			layui.form.render("select");
		}
	},'json');
}
function onSelectTw711_3(data){
	setAllAddressVal(data.value.trim());
	$("#oname").focus();
}

function onSelectHkCod_1(data){
	appendOptElement2("#hk_cod_2", data.value);
	setAllAddressVal('');
}
function onSelectHkCod_2(data){
	var _v1 = $("#hk_cod_1 option:selected").text();
	var _v2 = $("#hk_cod_2 option:selected").text();
	setAllAddressVal(_v1 + _v2);
	setCurrAddressFocus("end_hk_cod_addr");
}

function setAllAddressVal(val){
	var _cls = document.getElementsByName("address");
	for(var i=0;i<_cls.length;i++){
		_cls[i].value= val;
	}
}

function setCurrAddressFocus(eid){//$("#address").focus();
	var _ele = $("#"+eid)[0];
	var _c1 = _ele.getElementsByTagName("input");

	if(_c1 && _c1.length>0){
		_c1[0].focus();
	}else{
		_c1 = _ele.getElementsByTagName("textarea");
		if(_c1 && _c1.length>0){
			_c1[0].focus();
		}
	}
}