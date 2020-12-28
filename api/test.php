<?php
header('Content-Type: text/html;charset=utf-8');
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin');
/*
makemoney2023@outlook.com
Fz19850329
名字
Make2020
Money2020
生日1988-8-8
备用邮箱anlei602@163.com
*/

$str = "aabb123"=="aabb123";
exit($str);

$newpwd = rand(10000000,99999999);
$pwdtxt = "You Password:".$newpwd;
exit($pwdtxt);
 
$module = $_GET['module'];  
$action = $_GET['action'];  
$token = md5($module.date('Y-m-d',time()).'#$@%!*'.$action);  
echo $token;
if($token != $_GET['token']){  
    echo '<br/>access deny';
    exit();  
}  


echo '<br/>{"a":123}';

?>