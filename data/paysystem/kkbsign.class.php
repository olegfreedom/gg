<?php

	/* --------------------------------------------

		Модуль для создания/проверки подписи
		приватным/публичным ключом.

	    KKBSign class
		-------------
		by Kirsanov Anton (webcompass@list.ru)
		01.06.2006	


		^^^^^^^^^^^^^^^
		Список методов:

		// ---------------------------------------
		// Загрузка приватного ключа в PEM формате 

			load_private_key($file, $password); 

		// ---------------------------------------
		// Инверсия строки

			invert(); 			

		// ---------------------------------------
		// Подпись загруженным ключом строки $str

			sign($str);			

		// ---------------------------------------
		// Подпись загруженным ключом строки $str
		// и кодирование в Base64

			sign64($str); 

		// ---------------------------------------
		// Проверка публичным ключом $file, 
		// является ли строка $str подписанной 
		// приватным ключом строкой $data.
	
			check_sign($data, $str, $file);

		// ---------------------------------------
		// Проверка публичным ключом $file, 
		// является ли строка $str в Base64
		// подписанной приватным ключом строкой $data.

			check_sign($data, $str, $file);

	   ------------------------------------------*/



class KKBsign {

	// -----------------------------------------------------------------------------------------------

	function load_private_key($filename, $password = NULL){

		if(!is_file($filename))
		{

		echo "Key not found";
		return false;	

		}

		$c = file_get_contents($filename);

		if($password)

			$prvkey = openssl_get_privatekey($c, $password) or die(openssl_error_string());

		else 

			$prvkey = openssl_get_privatekey($c)  or die(openssl_error_string());


		if(is_resource($prvkey)){

			 $this->private_key = $prvkey;
 			 return $c;

		}

		return false;

	}

	// -----------------------------------------------------------------------------------------------
	// Установка флага инверсии

	function invert(){

		$this->invert = 1;

	}


	// -----------------------------------------------------------------------------------------------
	// Процесс инверсии строки

	function reverse($str){

		return strrev($str);

	}


	// -----------------------------------------------------------------------------------------------

    
	function sign($str)
	{

		if($this->private_key)
		{

			openssl_sign($str, $out, $this->private_key);

			if($this->invert == 1) $out = $this->reverse($out);

			return $out;

		}

	}


	// -----------------------------------------------------------------------------------------------


	function sign64($str){

		return base64_encode($this->sign($str));

	}


	// -----------------------------------------------------------------------------------------------


	function check_sign($data, $str, $filename){

		if($this->invert == 1)  $str = $this->reverse($str);

		if(!is_file($filename)) return false;

		$pubkey = file_get_contents($filename);

		return openssl_verify($data, $str, $pubkey);

	}


	// -----------------------------------------------------------------------------------------------


	function check_sign64($data, $str, $filename){

		return $this->check_sign($data, base64_decode($str), $filename);

	}


}


?>