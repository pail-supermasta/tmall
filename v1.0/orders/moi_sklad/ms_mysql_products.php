<?php

require_once 'mysql_class.php';

$db_hostname = 'avaks.org';
$db_username = 'avaks';
$db_password = 'SbTZN8L9fCpVDxtc';
$db_database = 'avaks';

$settings['telegram_bot_token'] = '345217125:AAE4o7Bs-QeQnusf3SQ-xSuSBm2RGMVH97w';

// если товар не найден то сообщать об ошибке

// сейчас кейсы:
// ключ верный товар не верный
// ключ не верный товар верный









// unique id for field contains products id from Tmall
//bestgoodsstore@yandex.ru
$product_key = '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8';
// id from Ali test for bestgoods
$product_value= '33042576027';


//NezabudkaND@yandex.ru
//$product_key = '0bbce15c-81f4-11e9-9109-f8fc0004decb';
// id from Ali test for NezabudkaND
//$product_value= '33022142079';


//novinkiooo@yandex.ru
//$product_key = 'e8a40577-77b9-11e9-912f-f3d40003d45d';
// id from Ali test for novinkiooo
//$product_value= '';

//NezabudkaMR@yandex.ru
//$product_key = '0bbcd991-81f4-11e9-9109-f8fc0004dec9';
// id from Ali test for NezabudkaMR
//$product_value= '';

//NezabudkaiRobot@yandex.ru
//$product_key = '0bbcde02-81f4-11e9-9109-f8fc0004deca';
// id from Ali test for NezabudkaiRobot
//$product_value= '33019845507';


$sql = new MySQL($db_hostname, $db_username, $db_password, $db_database);
$query = "SELECT id FROM `ms_product` WHERE attributes LIKE '%".$product_key."\":\"".$product_value."%'";


$result = $sql->query($query);

if ($result->num_rows > 0) {
    // output data of each row
    var_dump($result);

} else {
    echo "0 results";
}
