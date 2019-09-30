<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 25.07.2019
 * Time: 15:15
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("error_log", "../php-error.log");


define('MS_USERNAME', 'робот_next@техтрэнд');
define('MS_PASSWORD', 'Next0913');


error_log(file_get_contents('php://input') . " \n", 3, "wh.log");





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


//$rawHookResponse = '{"events":[{"meta":{"type":"customerorder","href":"https://online.moysklad.ru/api/remap/1.1/entity/customerorder/73e863a9-b530-11e9-9109-f8fc00093af7"},"action":"UPDATE","accountId":"32c52dd2-75c5-11e5-90a2-8ecb000018f0"}]}';
$rawHookResponse = file_get_contents('php://input');
$arrayHookResponse = json_decode($rawHookResponse, true);
$events = $arrayHookResponse['events'];
//echo $arrayHookResponse;

//file_put_contents('wh.log', $rawHookResponse);


define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID

foreach ($events as $event) {
    checkChanges($event);
}
// Проверка изменений аудита
function checkChanges($event)
{
    preg_match(ID_REGEXP, $event['meta']['href'], $matches);

    $entity_id = $matches[0]; // Получаем UUID события

    $res = curlMS(MS_PATH . '/entity/' . $event['meta']['type'] . '/' . $entity_id . '/audit?filter=entityType=customerorder'); // Получаем подробности события
//    $res = curlMS(MS_PATH . '/entity/' . $event['meta']['type'] . '/ffd67ea7-b43f-11e9-912f-f3d400049606/audit?filter=entityType=customerorder'); // Получаем подробности события
    $res = json_decode($res, true);

//    echo  $res;

    $oldFound = false;
    $newTrackSet = false;
    foreach ($res['rows'] as $row) {
        if (isset($row['diff']['#Логистика: трек..']['newValue'])) {
            $newTrack = $row['diff']['#Логистика: трек..']['newValue'];
            echo 'newVal ' . $newTrack . PHP_EOL;
            $newTrackSet = true;
        }
        if ($newTrackSet == true && isset($row['diff']['#Логистика: трек..']['oldValue'])) {
            $oldTrack = $row['diff']['#Логистика: трек..']['oldValue'];
            echo 'oldVal ' . $oldTrack . PHP_EOL;
            $oldFound = true;
        }

    }
//    if (checkOrderPaid($entity_id) == true && $oldFound == false) {
    if (checkOrderPaid($entity_id) == true ) {
        error_log($newTrack . " \n", 3, "wh-works.log");
    }

}

