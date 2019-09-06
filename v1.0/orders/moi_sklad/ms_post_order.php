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

function curlMS($link = false, $username = false, $post)
{


    $curl = curl_init();
//    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_URL, $link);
//    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . MS_PASSWORD);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_POST, true);

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    curl_close($curl);

    if ($curl_errno == 0) {
//        $result = json_decode($result, true);
//        if (isset($result['errors'])){
//            $sql->query("INSERT INTO errors SET datetime = NOW(), from = 'curlMS', error = '".$sql->escape(json_encode($result, JSON_UNESCAPED_UNICODE))."', data= '".$sql->escape(json_encode($data, JSON_UNESCAPED_UNICODE))."', state = '0'");
//        }
        return $result;
    } else {
        return $curl_errno;
    }

}

header('Content-Type: application/json');
$link = MS_PATH . '/entity/customerorder';


// Добавляем комментарий

// признак оплаты в ALIE
$paid = true;

// указываем магазин в котором купили на ALI
$shop = 'магазин: BESTGOODS (ID 5041091)';

$comment = $paid == true ? '"ОПЛАЧЕНО ' . $shop . '"' : '""';


$postdata = '{
	"name": "705171383041043",
	"deliveryPlannedMoment": "2017-12-15 15:04:00",
	"applicable": false,
	"description": ' . $comment . ',
	"vatEnabled": false,
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
			"href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/327bfd05-75c5-11e5-7a40-e89700139935",
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
			"value": "Фамилия Имя Отчество"
		},
		{
			"id": "547ff930-ef8e-11e6-7a31-d0fd0021d13e",
			"value": "г Город, ул Улица"
		},
		{
			"id": "1bf09429-ef77-11e6-7a31-d0fd001c9667",
			"value": "8 (999) 999-88-77"
		},
		{
			"id": "4552a58b-46a8-11e7-7a34-5acf002eb7ad",
			"value": {
				"name": "0 Нужна доставка"
			}
		}
	],
	"positions": [{
			"quantity": 1,
			"price": 2242500,
			"discount": 0,
			"vat": 0,
			"assortment": {
				"meta": {
					"href": "https://online.moysklad.ru/api/remap/1.1/entity/product/d74d0e2e-368c-11e9-9ff4-315000304b64",
					"type": "product",
					"mediaType": "application/json"
				}
			},
			"reserve": 1
		},
		{
			"quantity": 1,
			"price": 600000,
			"discount": 0,
			"vat": 0,
			"assortment": {
				"meta": {
					"href": "https://online.moysklad.ru/api/remap/1.1/entity/product/059f39cf-f73f-11e6-7a34-5acf000da452",
					"type": "product",
					"mediaType": "application/json"
				}
			},
			"reserve": 1
		}
	]	
}';


$res = curlMS($link, MS_USERNAME, $postdata);

echo($res);
