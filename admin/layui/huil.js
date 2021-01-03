//根据国家符号得到汇率
function getHuil($c){
	$huil = 10;
	if($c == 'tw'){
		//$huil = 4.6;
		$huil = parseFloat(getSettingValue("twhuil"));
	}else if($c == 'hk'){
		//$huil = 1.1;
		$huil = parseFloat(getSettingValue("hkhuil"));
	}
	return $huil;
}
//根据国家得到货币符号
function getHb(val){
	$coin = '$';
	if(val == 'tw'){
		$coin = 'NT$';
	}else if(val == 'hk'){
		$coin = 'HKD$';
	}
	return $coin;
}
//根据元素节点设置值
function dingEleEcho(eleid, val, ishtml){
	var ele = document.getElementById(eleid);
	if(ishtml){
		ele.innerHTML = val;
	}else{
		ele.innerText = val;
	}
}
//根据国家转换成销售价 $p=进价，$c=国家符号，$w=重量
function coveHuilPrice($p,$c,$w){
	$p = parseFloat($p);
	var sjreg = $p>160?300: $p>=140?250: $p>=120?200: $p>=100?170: $p>=80?140:120;
	var kdmoney = parseFloat(getSettingValue("kdmoney"));
	var wtop = weightToPrice($w);
	sjreg = ($p + kdmoney + wtop + sjreg) * getHuil($c);
	sjreg = parseInt(sjreg);
	return sjreg;
}
//根据重量取得运费
function weightToPrice($w){
	var $ew = Math.ceil($w - 1);//除了首重，共需要续重多少。
	var $firstweight = parseFloat(getSettingValue("firstweight"));
	var $keepweight = parseFloat(getSettingValue("keepweight"));
	var $pri = $firstweight + $ew * $keepweight;
	return $pri;
}