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

?>