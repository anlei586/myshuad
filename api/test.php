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

$_date = date("Y-m-d H:i:s");

echo $_date.'<br/>'.time();

?>