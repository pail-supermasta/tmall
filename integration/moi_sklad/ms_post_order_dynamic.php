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
    $headers[] = 'X-Lognex-Precision: true';
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    echo "Post body is: \n" . $post . "\n";

    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    if (strpos($result, 'обработка-ошибок') > 0 || $result == '') {
        telegram("Ошибка создания заказа в МС. $curl_errno", '-320614744');
        error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . "Return error " . $result . " POST BODY IS " . $post . PHP_EOL, 3, "orderCreateErr.log");
    }


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
            /*        BF-6*/

            /*  BF-7
            send errors to telegram bot */
            $newOrderTeleGMsg = "Новый заказ #[$orderId]($orderLinkMS).";
            if ($memo != '' && isset($memo)) {
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

function fillOrderTemplate(array $orderDetails)
{

    // признак оплаты в ALIE
//    $paid = $orderDetails['paid'] == 'PAY_SUCCESS' ? true : false;
    $availableMS = true;
    foreach ($orderDetails['productStocks'] as $productStock) {
        if (reset($productStock) == false) {
            $availableMS = false;
        }
    }
    if (!isset($orderDetails['productStocks'])) {
        /*отработать*/
        $state = '552a994e-2905-11e7-7a31-d0fd002c3df2';
        $logisticsProvider = '1 Не нужна доставка';
    } elseif ($availableMS == true && $orderDetails['err'] == '' && $orderDetails['paid'] == 'PAY_SUCCESS') {
        $paid = true;
        /*в работе*/
        $state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
        $logisticsProvider = 'Cainiao';
    } elseif ($availableMS == true && $orderDetails['err'] == '' && ($orderDetails['paid'] == 'PLACE_ORDER_SUCCESS' || $orderDetails['paid'] == 'NOT_PAY')) {
        $paid = false;
        /*ждем оплаты*/
        $state = '327c0111-75c5-11e5-7a40-e89700139936';
        $logisticsProvider = '1 Не нужна доставка';
    } else {
        $paid = false;
        /*отработать*/
        $state = '552a994e-2905-11e7-7a31-d0fd002c3df2';
        $logisticsProvider = '1 Не нужна доставка';
    }


    // указываем магазин в котором купили на ALI
    $shopId = '';
    switch ($orderDetails['shop']) {
        case "novinkiooo@yandex.ru":
            $shopId = "Avax store (ID 4901001)";
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
    $escrowComment = '\\nescrow_fee_rates: ' . $orderDetails['escrow_fee_rates'];
    $memoComment = isset($orderDetails['memo']) ? '\\n' . $orderDetails['memo'] : '';

    $dshSumComment = isset($orderDetails['dshSum']) ? '\\nКомиссия:' . $orderDetails['dshSum'] : '';
    $couponComment = isset($orderDetails['coupon']) ? '\\nКупон / Доп. скидка:' . $orderDetails['coupon'] : '';


    $comment = '"' . $orderDetails['order'] . '\\n' . $shop . $memoComment . $markAsPaid . $escrowComment .
        $dshSumComment . $couponComment . '"';

    $postdata = '{
        "name": "' . $orderDetails['order'] . '",
        "moment": "' . $orderDetails['gmt_create'] . '",
        "deliveryPlannedMoment": "' . $orderDetails['deliveryPlannedMoment'] . '",
        "applicable": true,
        "owner": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/employee/96e93084-5588-11e9-9107-5048000ac025",
                "type": "employee",
                "mediaType": "application/json"
            }
        },
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
                "value": ' . (int)$orderDetails['dshSum'] . '
            },
            {
                "id": "4552a58b-46a8-11e7-7a34-5acf002eb7ad",
                "value": {
                    "name": "' . $logisticsProvider . '"
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

    curlMSCreate('робот_next@техтрэнд', $postdata, $orderDetails['memo'] ?? '');


}





