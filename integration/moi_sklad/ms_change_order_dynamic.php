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

            $newOrderTeleGMsg = "ВНИМАНИЕ. Заказ [$orderId]($orderLinkMS) попал в статус ОТМЕНЕН.";
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

            $newOrderTeleGMsg = "ВНИМАНИЕ. Заказ [$orderId]($orderLinkMS) попал в статус ОПЛАЧЕН. Срочно отработать";
            telegram($newOrderTeleGMsg, '-278688533', 'Markdown');
        };

        return true;
    } else {
        return $curl_errno;
    }

}

function fillOrderTemplate($order, $state)
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


        curlMSCancel('kurskii@техтрэнд', $postdata, $order);
    } else if ($state == 'paid') {
        /*set state as В работе*/
        $postdata = '{
        "state": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/ecf45f89-f518-11e6-7a69-9711000ff0c4",
                "type": "state",
                "mediaType": "application/json"
            }
	}
    }';
        curlMSPaid('kurskii@техтрэнд', $postdata, $order);
    }


}







