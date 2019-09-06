<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.07.2019
 * Time: 9:43
 */

define('MS_LINK', MS_PATH . '/entity/customerorder');


function curlMSCreate($username = false, $post, $memo)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, MS_LINK);
    curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . MS_PASSWORD);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_POST, true);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'X-Lognex-WebHook-Disable: true';
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    curl_close($curl);

    if ($curl_errno == 0) {
        $result = json_decode($result, true);
        echo '<pre>';
        print_r($result);
        echo '</pre>';


        /*  send email about new order  */

        if (isset($result['name'])) {
            $orderId = $result['name'];
            $orderLinkMS = $result['meta']['uuidHref'];
            /*        BF-6
            $newOrderMsg = "Получен новый заказ Tmall номер $orderId";
            mail("p.kurskii@avaks.org", "Tmall новый заказ", $newOrderMsg);*/

            /*  BF-7
            send errors to telegram bot */
            $newOrderTeleGMsg = "Получен новый заказ Tmall номер [$orderId]($orderLinkMS). Заказ не отработан";
            if ($memo != "" && isset($memo)) {
                $userCommentTeleGMsg = "Комментарий к заказу [$orderId]($orderLinkMS): $memo";
                telegram($userCommentTeleGMsg, '-278688533', 'Markdown');
            }

            telegram($newOrderTeleGMsg, '-278688533', 'Markdown');


        };

        return true;
    } else {
        return $curl_errno;
    }

}

function fillOrderTemplate($orderDetails)
{

    // признак оплаты в ALIE
//    $paid = $orderDetails['paid'] == 'PAY_SUCCESS' ? true : false;
    if ($orderDetails['paid'] == 'PAY_SUCCESS') {
        $paid = true;
        $state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
    } else {
        $paid = false;
        $state = '327c0111-75c5-11e5-7a40-e89700139936';
    }


    // указываем магазин в котором купили на ALI
    $shopId = '';
    switch ($orderDetails['shop']) {
        case "novinkiooo@yandex.ru":
            $shopId = "novinkiooo (ID 4901001)";
            break;
        case "bestgoodsstore@yandex.ru":
            $shopId = "BESTGOODS (ID 5041091)";
            break;
        case "NezabudkaND@yandex.ru":
            $shopId = "Noerden (ID 5012047)";
            break;
        case "NezabudkaMR@yandex.ru":
            $shopId = "Morphy Richards (ID 5017058)";
            break;
        case "NezabudkaiRobot@yandex.ru":
            $shopId = "iRobot (ID 5016030)";
            break;
    }

    $shop = $shopId . '\\n/*Складу - вложить гарантийный талон \\n/*Логистам - Отправить клиенту согласно заполненным полям';
    $markAsPaid = ($paid == true) ? '\\nЗАКАЗ ОПЛАЧЕН' : '';
    $escrowComment = 'escrow_fee_rates: ' . $orderDetails['escrow_fee_rates'];

    $comment = '"Tmall id: ' . $orderDetails['order'] . '\\n' . $shop . '\\n' . $orderDetails['memo'] . '\\n' . $escrowComment . $markAsPaid . '"';

    $postdata = '{
        "name": "' . $orderDetails['order'] . '",
        "moment": "' . $orderDetails['gmt_create'] . '",
        "deliveryPlannedMoment": "' . $orderDetails['deliveryPlannedMoment'] . '",
        "applicable": true,
        "description": ' . $comment . ',
        "vatEnabled": false,
        "shared": true,
        "organization": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/organization/326d65ca-75c5-11e5-7a40-e8970013991b",
                "type": "organization",
                "mediaType": "application/json"
            }
        },
        "agent": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/counterparty/1b33fbc1-5539-11e9-9ff4-315000060bc8",
                "type": "counterparty",
                "mediaType": "application/json"
            }
        },	
        "state": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/' . $state . '",
                "type": "state",
                "mediaType": "application/json"
            }
        },
        "store": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/store/48de3b8e-8b84-11e9-9ff4-34e8001a4ea1",
                "metadataHref": "https://online.moysklad.ru/api/remap/1.1/entity/store/metadata",
                "type": "store",
                "mediaType": "application/json",
                "uuidHref": "https://online.moysklad.ru/app/#warehouse/edit?id=48de3b8e-8b84-11e9-9ff4-34e8001a4ea1"
            }
        },
        "attributes": [{
                "id": "5b766cb9-ef7e-11e6-7a31-d0fd001e5310",
                "value": "' . $orderDetails['contact_person'] . '"
            },
            {
                "id": "547ff930-ef8e-11e6-7a31-d0fd0021d13e",
                "value": "' . $orderDetails['fullAddress'] . '"
            },
            {
                "id": "1bf09429-ef77-11e6-7a31-d0fd001c9667",
                "value": "' . $orderDetails['phone'] . '"
            },
            {
                "id": "547ffc2a-ef8e-11e6-7a31-d0fd0021d141",
                "value": ' . $orderDetails['discountApplied'] . '
            },
            {
                "id": "4552a58b-46a8-11e7-7a34-5acf002eb7ad",
                "value": {
                    "name": "0 Нужна доставка"
                }
            },
            {
                "id": "c4e03fe6-46f3-11e8-9ff4-34e8002246bf",
                "value": {
                    "name": "--"
                }
            }
        ],
        "positions": ' . $orderDetails['positions'] . '
    }';


    curlMSCreate('kurskii@техтрэнд', $postdata, $orderDetails['memo']);


}





