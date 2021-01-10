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

function getQueryVariable(variable)
{
   var query = window.location.search.substring(1);
   var vars = query.split("&");
   for (var i=0;i<vars.length;i++) {
	   var pair = vars[i].split("=");
	   if(pair[0] == variable){return pair[1];}
   }
   return "";
}

function setLanguageVars(){
	//取URL语言变量
	var language_str = getQueryVariable("lang");//cn,en
	if(!language_str){//如果没有则去缓存取
		language_str = localStorage.getItem("lang");
		if(!language_str){//如果连缓存也没有就用默认的英文
			language_str = 'en';
		}
	}
	//保存到本地
	localStorage.setItem("lang", language_str);
	//判断是否有这个登事的变量
	if(window['login_var']){
		login_var = window['login_'+language_str];
	}
	//判断是否有这个登事的变量
	if(window['lang_var']){
		lang_var = window['lang_'+language_str];
	}
}