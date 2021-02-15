<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

/*
makemoney2023@outlook.com
Fz19850329
名字
Make2020
Money2020
生日1988-8-8
备用邮箱anlei602@163.com


godaddy.com
amara1897x@gmail.com
amara1897
dave741236
goodsummerday2020.com

google.com
手机+85266445946
amara1897x@gmail.com
dave741236

vultr.com
amara1897x@gmail.com
Dave741236


stripe
amara1897x@gmail.com
shoptago0087


可发布的密钥
pk_test_51HytwELYGllvnGPLslkvyoqajm23OiTgFltM1bBzTdQ9q4AjqwtLg9bTQ48NofJXJDwyWcaxeTU16evkwljhT6w70087hJo67G
密钥
sk_test_51HytwELYGllvnGPLtSKvAAGvJ3EeBJbjdD3XDciqLHDTU3J6Q39EM0DEjN9utzJ8iNY8DnJpB6TmsH8vN3nZDdwa00SbGzuSNI
Test Webhook Secret
whsec_J1F3AjZVPahLOCurWbXSrxjo043TGFFP

可发布的密钥
pk_live_51HytwELYGllvnGPLa6JG0tfg5a63Zy4ptnlpwW0CDctyebPCQ6F4uyVtUxv2scUPU9ts4NmnkYN1eiHCL0hLroIo000N8Z6bKD
密钥
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
Webhook Secret
whsec_J1F3AjZVPahLOCurWbXSrxjo043TGFFP



http://45.77.66.117:9630/e506eefe
root
W6{c-t{)YSxFW*xV
username: hgwgzxoh
password: 685cf1ff


maxmind.com
amara1897x@gmail.com
Dave741236
Account/User ID
480829
License key
8u3ffvBLZbxwMl1m


https://www.pionex.cc/
amara1897x@gmail.com
Dave741236


*/


/*
test
http://192.168.123.85/api/sendmail.php?title1=aaabbbsd&title2=aa123456&toemail=anlei602@163.com&content=asdfs%20123fjajsfjksf

*/

function sendmail($title1, $title2, $toemail, $content){
	$mail = new PHPMailer(true);

    $mail->SMTPDebug = false;//SMTP::DEBUG_SERVER;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.126.com';                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'louguosen@126.com';                     // SMTP username
    $mail->Password   = 'DMLOVHDZWZVDYOGD';                               // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

    //Recipients
    $mail->setFrom('louguosen@126.com', $title1);
    $mail->addAddress($toemail);               // Name is optional

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $title2;
    $mail->Body    = $content;
    //$mail->AltBody = '';

    $mail->send();
    return '{"code":0,"msg":"success"}';
}

?>