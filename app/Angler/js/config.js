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