<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15.07.2019
 * Time: 9:43
 */


define('MS_USERNAME', 'робот_next@техтрэнд');
define('MS_PASSWORD', 'Next0913');
define('MS_PATH', 'https://online.moysklad.ru/api/remap/1.1');
define('MS_LINK', MS_PATH . '/entity/customerorder');
define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID


function curlMSCreate($username = false, $post, $memo, $order = false)
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
//    $headers[] = 'X-Lognex-WebHook-Disable: true';
    $headers[] = 'X-Lognex-Precision: true';
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    echo "Post body is: \n" . $post . "\n";

    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    if (strpos($result, 'обработка-ошибок') > 0 || $result == '') {
        telegram("Ошибка создания заказа в МС. $order", '-320614744');
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
        $logisticsProvider = 'Cainiao';
    } elseif ($availableMS == true && $orderDetails['err'] == '' && $orderDetails['paid'] == 'PAY_SUCCESS') {
        $paid = true;
        /*в работе*/
        $state = 'ecf45f89-f518-11e6-7a69-9711000ff0c4';
        $logisticsProvider = 'Cainiao';
    } elseif ($availableMS == true && $orderDetails['err'] == '' && ($orderDetails['paid'] == 'PLACE_ORDER_SUCCESS' || $orderDetails['paid'] == 'NOT_PAY')) {
        $paid = false;
        /*ждем оплаты*/
        $state = '327c0111-75c5-11e5-7a40-e89700139936';
        $logisticsProvider = 'Cainiao';
    } else {
        $paid = false;
        /*отработать*/
        $state = '552a994e-2905-11e7-7a31-d0fd002c3df2';
        $logisticsProvider = 'Cainiao';
    }


    /*city - Other now not processed by Cainiao, deliver with other shipper*/
    /*if (strpos($orderDetails['fullAddress'], "Other") > 0) {
        $logisticsProvider = '0 Нужна доставка';
    }*/


    // указываем магазин в котором купили на ALI
    $shopId = '';
    $organization = '';
    $organizationAccount = '';
    switch ($orderDetails['shop']) {
        case "bestgoodsstore@yandex.ru":
            $shopId = "BESTGOODS (ID 5041091)";
            $organization = '2d916f14-3e6b-11e8-9107-5048000ceb7a'; // "ООО \"ИПА\""
            $organizationAccount = '08e8d50e-fca4-11ea-0a80-033400049ed9';
            break;
        case "orionstore360@gmail.com":
            $shopId = "orion (ID 911725024)"; //id можно увидеть когда переходишь на страницу магазина в алике в урле
            $organization = '20118958-cc01-11ea-0a80-019e0006076a';
            $organizationAccount = '20118e45-cc01-11ea-0a80-019e0006076b';
            break;
        case "novinkiooo@yandex.ru":
            $shopId = "novinkiooo (ID 4901001)";
            $organization = 'b2c9e371-1b98-11e6-7a69-93a700176cab';
            $organizationAccount = 'cecd7735-b55a-11ea-0a80-012b000c8066';
            break;
    }

    $shop = $shopId . '\\n/*Складу - вложить гарантийный талон \\n/*Логистам - Отправить клиенту согласно заполненным полям';
    $markAsPaid = ($paid == true) ? '\\nЗАКАЗ ОПЛАЧЕН' : '';
    $escrowComment = '\\nescrow_fee_rates: ' . $orderDetails['escrow_fee_rates'];

    /*удалить двойные ковычки*/
    $vanishMemo = str_replace('"', '', htmlspecialchars($orderDetails['memo']));
    /*удалить новую строку*/
    $vanishMemo = preg_replace('/\s+/', ' ', trim($vanishMemo));
    $memoComment = isset($orderDetails['memo']) ? '\\n' . $vanishMemo : '';

    $dshSumComment = isset($orderDetails['dshSum']) ? '\\nКомиссия:' . $orderDetails['dshSum'] : '';
    $couponComment = isset($orderDetails['coupon']) ? '\\nКупон / Доп. скидка:' . $orderDetails['coupon'] : '';


    $comment = '"' . $orderDetails['order'] . '\\n' . $shop . $memoComment . $markAsPaid . $escrowComment . $dshSumComment . $couponComment . '"';

    $postdata = '{
        "name": "' . $orderDetails['order'] . '",
        "moment": "' . $orderDetails['gmt_create'] . '",
        "deliveryPlannedMoment": "' . $orderDetails['deliveryPlannedMoment'] . '",
        "applicable": true,
        "owner": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/employee/fc4f96d3-d5fe-11e9-0a80-046b000e7e4d",
                "type": "employee",
                "mediaType": "application/json"
            }
        },
        "description": ' . $comment . ',
        "vatEnabled": false,
        "shared": true,
        "organization": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/organization/' . $organization . '",
                "type": "organization",
                "mediaType": "application/json"
            }
        },
        "organizationAccount": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/organization/' . $organization . '/accounts/' . $organizationAccount . '",
                "type": "account",
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
        "contract": {
            "meta": {
                "href": "https://online.moysklad.ru/api/remap/1.1/entity/contract/59313308-3604-11eb-0a80-09730004c48f",
                "metadataHref": "https://online.moysklad.ru/api/remap/1.1/entity/contract/metadata",
                "type": "contract",
                "mediaType": "application/json",
                "uuidHref": "https://online.moysklad.ru/app/#contract/edit?id=59313308-3604-11eb-0a80-09730004c48f"
            }
        },
        "attributes": [{
                "id": "5b766cb9-ef7e-11e6-7a31-d0fd001e5310",
                "value": "' . htmlspecialchars($orderDetails['contact_person']) . '"
            },
            {
                "id": "547ff930-ef8e-11e6-7a31-d0fd0021d13e",
                "value": "' . htmlspecialchars(stripslashes($orderDetails['fullAddress'])) . '"
            },
            {
                "id": "1bf09429-ef77-11e6-7a31-d0fd001c9667",
                "value": "' . $orderDetails['phone'] . '"
            },
            {
                "id": "535dd809-1db1-11ea-0a80-04c00009d6bf",
                "value": ' . $orderDetails['dshSum'] . '
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

    curlMSCreate('робот_next@техтрэнд', $postdata, $orderDetails['memo'] ?? '', $orderDetails['order']);


}





