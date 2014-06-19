<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
require_once("../paysys/kkb.utils.php");
$self = $_SERVER['PHP_SELF'];
$path1 = '../paysys/config.txt';	// Путь к файлу настроек config.dat
$order_id = 520;				// Порядковый номер заказа - преобразуется в формат "000001", 
							// номер заказа должен быть уникальным и состоит только из цифр
							// пожалуйста поменяйте значение  $order_id на другуюб потому что по этому номеру уже тестировали!
$currency_id = "398"; 			// Шифр валюты  - 840-USD, 398-Tenge
$amount = 10;				// Сумма платежа
$content = process_request($order_id,$currency_id,$amount,$path1); // Возвращает подписанный и base64 кодированный XML документ для отправки в банк
	//в поле email укажите реальный электронный адрес	
	//если вы тестируете то поменяйте значение action на https://3dsecure.kkb.kz/jsp/process/logon.jsp
$str = '<document>
   <item number="1" name="Пополнение счета" quantity="1" amount="'.$amount.'"/>
</document> ';
$appendix = base64_encode($str);
?>
<html>
<head>
<title>Pay</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<form name="SendOrder" method="post" action="https://3dsecure.kkb.kz/jsp/process/logon.jsp">
<!-- <form name="SendOrder" method="post" action="https://epay.kkb.kz/jsp/process/logon.jsp"> -->
   <input type="hidden" name="Signed_Order_B64" value="<?php echo $content;?>">
   E-mail: <input type="text" name="email" size=50 maxlength=50  value="test@test.kz">
   <p>
   <input type="hidden" name="Language" value="rus"> <!-- язык формы оплаты rus/eng -->
   <input type="hidden" name="BackLink" value="http://services.local">
   <input type="hidden" name="appendix" value="<?php echo $appendix;?>">
   <input type="hidden" name="PostLink" value="http://localhost/paysystem_PHP/paytest/postlink.php">
   Со счетом согласен (-а)<br>
   <input type="submit" name="GotoPay"  value="Да, перейти к оплате" >&nbsp;
</form>
</body>
</html>
