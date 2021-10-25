<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.07.2019
 * Time: 9:43
 */


function curlMSCancel($username = false, $post, $order)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, MS_LINK . "/$order");
    curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . MS_PASSWORD);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    /*    $headers[] = 'X-Lognex-WebHook-Disable: true';*/
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    curl_close($curl);

    if ($curl_errno == 0) {
        $result = json_decode($result, true);
        /*        echo '<pre>';
                print_r($result);
                echo '</pre>';*/


        if (isset($result['name'])) {
            $orderId = $result['name'];
            $orderLinkMS = $result['meta']['uuidHref'];

            $newOrderTeleGMsg = "ОТМЕНЕН заказ #[$orderId]($orderLinkMS)";
            telegram($newOrderTeleGMsg, '-278688533', 'Markdown');
        };

        return true;
    } else {
        return $curl_errno;
    }

}
function curlMSFinish($username = false, $post, $order)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, MS_LINK . "/$order");
    curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . MS_PASSWORD);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($curl);
    var_dump($result);
    $curl_errno = curl_errno($curl);
    curl_close($curl);

    if ($curl_errno == 0) {
        $result = json_decode($result, true);

        if (isset($result['name'])) {
            $orderId = $result['name'];
            $orderLinkMS = $result['meta']['uuidHref'];

            $newOrderTeleGMsg = "ЗАВЕРШЕН заказ #[$orderId]($orderLinkMS)";
            telegram($newOrderTeleGMsg, '-278688533', 'Markdown');
        };

        return true;
    } else {
        return $curl_errno;
    }

}

function curlMSPaid($username = false, $post, $order)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, MS_LINK . "/$order");
    curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . MS_PASSWORD);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    /*    $headers[] = 'X-Lognex-WebHook-Disable: true';*/
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    curl_close($curl);

    if ($curl_errno == 0) {
        $result = json_decode($result, true);

        if (isset($result['name'])) {
            $orderId = $result['name'];
            $orderLinkMS = $result['meta']['uuidHref'];

            $newOrderTeleGMsg = "ОПЛАЧЕН заказ #[$orderId]($orderLinkMS)";
            telegram($newOrderTeleGMsg, '-278688533', 'Markdown');
        };

        return true;
    } else {
        return $curl_errno;
    }

}

function fillOrderTemplate($order, $state, $oldDescription = false)
{

    /*if cancel state - then call cancel Update*/
    /*else if paid state - then call paid Update*/
    if ($state == 'cancel') {
        $postdata = '{
        "state": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/327c070c-75c5-11e5-7a40-e8970013993b",
                "type": "state",
                "mediaType": "application/json"
            }
	}
    }';


        curlMSCancel('робот_next@техтрэнд', $postdata, $order);
    } else if ($state == 'paid') {
        /*удалить двойные ковычки*/
        $oldDescription = str_replace('"', '', $oldDescription);

        /*удалить новую строку*/
        $oldDescription = preg_replace('/\s+/', ' ', trim($oldDescription));

        /*set state as В работе*/
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/ecf45f89-f518-11e6-7a69-9711000ff0c4",
                    "type": "state",
                    "mediaType": "application/json"
                }
            },
            "description": "' . $oldDescription . ' ЗАКАЗ ОПЛАЧЕН",
            "attributes": [{
                    "id": "4552a58b-46a8-11e7-7a34-5acf002eb7ad",
                    "value": {
                        "name": "Cainiao"
                    }
            }]
         }';
        curlMSPaid('робот_next@техтрэнд', $postdata, $order);
    }


}







