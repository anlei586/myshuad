<?php

require_once '../wp-config.php';

//$host = DB_HOST.':3306';
$host = '127.0.0.1:3306';
$user = DB_USER;
$pass = DB_PASSWORD;
$dbName = DB_NAME;


$dsn="mysql:host=$host;dbname=$dbName";

function retmsg($retCode, $msg){
	return '{"ret":'.$retCode.',"msg":"'.$msg.'"}';
}

function createToken($module){
	$_date = date('Y-m',time());
	$token = md5($module.$_date.'#$@%!*');  
	return $token;
}


function em_getallheaders()
{
   foreach ($_SERVER as $name => $value)
   {
	   if (substr($name, 0, 5) == 'HTTP_')
	   {
		   $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
	   }
   }
   return $headers;
}

?>