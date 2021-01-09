var config_var = {
	host:"http://192.168.123.85/api/",
};

function myajax(_url, _obj, _header){
	if(!_header){
		_header = {};
	}
	var token = localStorage.getItem("token");
	var email = localStorage.getItem("email");
	
    _header = {'token':token,'email':email};
	_obj["headers"] = _header;
	
	var _success = _obj['success'];
	_obj['success']=function(res){
		if(res && res.ret && res.ret == 108){
			quitAccount();
		}else{
			_success(res);
		}
	}
	_obj['error']=function(res){
		mui.alert(lang_var.code_lab.ERROR_1, lang_var.code_lab.TIP);
	}
	
	mui.ajax(_url, _obj);
}

function getConfig(res, key){
	for(var i=0;i<res.length;i++){
		if(res[i].key==key){
			return res[i].value;
		}
	}
	return null;
}

function dateSub(date_str1, date_str2){
	var date_str1 = date_str1.replace(/\-/g, "/");
	var date_str2 = date_str2.replace(/\-/g, "/");
	var _day = new Date(date_str1)-new Date(date_str2);
	return _day;
}