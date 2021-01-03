<?php

/*
ini_set('display_errors', 1); //错误信息 
ini_set('display_startup_errors', 1); //php启动错误信息 
error_reporting(-1); //打印出所有的 错误信息
*/

try{
	session_start();
} catch (Exception $e) {
}

if (empty($_SESSION["admin"])) {
	if(empty($debug)){
		echo "您未登陆";
		header('Location: ./shuad.php');
	}
}

function zyyhj($v){
	if(empty($v)) return $v;
	return htmlspecialchars(addslashes($v));
}



function vpost($url, $data){
	$data_string = json_encode($data);

	$ch = curl_init();  
	curl_setopt($ch,CURLOPT_URL,$url);  
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_string)));  
	$output = curl_exec($ch);
	if (!$output) {
		echo '{"code":-2, "msg":"Error"}';
	}
	curl_close($ch);
	return $output;
}


?>