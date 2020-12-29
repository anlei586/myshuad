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
	mui.ajax(_url, _obj);
}