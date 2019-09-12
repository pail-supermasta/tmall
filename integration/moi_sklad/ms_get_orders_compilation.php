<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.07.2019
 * Time: 9:43
 */


define('MS_USERNAME', 'kurskii@техтрэнд');
define('MS_PASSWORD', 'UR4638YFe');
define('MS_PATH', 'https://online.moysklad.ru/api/remap/1.1');



function curlMS($link = false, $data = false, $username = false)
{


    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_HTTPGET, true);

    if (!$username) {
        curl_setopt($curl, CURLOPT_USERPWD, MS_USERNAME . ':' . MS_PASSWORD);
    } else {
        curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . MS_PASSWORD);
    }

    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    if ($data) {
        $headers[] = 'Content-Length:' . strlen(json_encode($data));
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    curl_close($curl);

    if ($curl_errno == 0) {
        return $result;
    } else {
        return $curl_errno;
    }

}

header('Content-Type: application/json');


$fn = fopen("../ali_express/orders.txt","r");

while(! feof($fn))  {
    $result = fgets($fn);
    $orderId = '99399939';
    $link = MS_PATH . "/entity/customerorder/?search=".$orderId;
    $res = curlMS($link, false, MS_USERNAME);
    echo $res;
    die();
}

fclose($fn);



echo($res);
