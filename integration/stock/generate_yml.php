<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Europe/Moscow');


require_once "../vendor/autoload.php";
require_once '../class/telegram.php';


use MongoDB\BSON\Regex;
use MongoDB\Client;


$tmallIndexes = [
    'novinkiooo' => 'e8a40577-77b9-11e9-912f-f3d40003d45d', //32998880655
    'bestgoods' => '0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',// 4000330839485
    'Morphy Richards' => '0bbcd991-81f4-11e9-9109-f8fc0004dec9', // 33017680130
    'iRobot' => '0bbcde02-81f4-11e9-9109-f8fc0004deca', //33022490020
    'Noerden' => '0bbce15c-81f4-11e9-9109-f8fc0004decb' //33018957808
];

$categoriesAll = [
    "novinkiooo" => [
        'Беспроводные пылесосы' => '516763058',
        'Роботы-мойщики окон' => '516668105',
        'Роботы-полотеры' => '516759323',
        'Роботы-пылесосы' => '516759324',
        'Запчасти и Аксессуары' => '516763059',

        'Пылесосы' => '516664941',
        'Детские матрасики' => '516759328',
        'Игрушки' => '516763061',
        'Детская продукция' => '516759327',
        '3D ручки' => '516664942',

        'Чернила для 3D ручек' => '516763062',
        'Трафареты для 3D ручек' => '516759330',
        '3D печать' => '516759329',
        'Умные весы' => '516668109',
        'Мониторы сердечной активности' => '516668110',

        'Электрические велосипеды' => '516759332',
        'Умные устройства' => '516664943',
        'Рюкзаки' => '516664944',
        'Кошельки' => '516664945',
        'Сумки и рюкзаки' => '516668111',

        'Подушка для разглаживания морщин' => '516763065',
        'Простыни на резинке' => '516664947',
        'Постельные принадлежности' => '516664946',
    ],
    "bestgoods" => [
        'Беспроводные пылесосы' => '516735936',
        'Роботы-мойщики окон' => '516739676',
        'Роботы-полотеры' => '516735937',
        'Роботы-пылесосы' => '516739677',
        'Запчасти и Аксессуары' => '516739678',
        'Пылесосы' => '516735935',
        'Детские матрасики' => '516370037',
        'Игрушки' => '516652545',
        'Детская продукция' => '516652544',
        '3D ручки' => '516370038',
        'Чернила для 3D ручек' => '516652546',
        'Трафареты для 3D ручек' => '516739800',
        '3D печать' => '516739799',
        'Умные весы' => '516739801',
        'Мониторы сердечной активности' => '516652548',
        'Электрические велосипеды' => '516648821',
        'Умные устройства' => '516652547',
        'Рюкзаки' => '516759288',
        'Кошельки' => '516664907',
        'Сумки и рюкзаки' => '516763026',
        'Подушка для разглаживания морщин' => '516668078',
        'Простыни на резинке' => '516759289',
        'Постельные принадлежности' => '516664908',
        'Audiotechnics' => '516666141',
        'something' => '516858193',
        'Cosmetics' => '517103694',
        'Promotional Goods' => '516977910',

    ],
    "Morphy Richards" => [
        'Электрические чайники' => '516755775',
        'Электрические утюги' => '516397904',
        'Блендеры и миксеры' => '516397905',
        'Аксессуары и запчасти' => '516759121',
        'Кофеварки' => '516755853',
        'Хлебопечки' => '516755854',
        'Тостеры' => '516755855',
        'Суповарки' => '516397906',
        'Грильбокс' => '516755857',
        'Миксеры' => '516820134',
        'Блендеры' => '516577047',
        'Миксеры и блендеры' => '516820133'

    ],
    "iRobot" => [
        'Роботы-мойщики полов' => '516759225',
        'Роботы-пылесосы' => '516759223',
        'Запчасти и аксессуары' => '516755970'
    ],
    "Noerden" => [
        'MINIMI' => '516397950',
        'Умные весы' => '516664789',
        'LIZ' => '516664791',
        'Умная бутылочка' => '516664790',
        'ZERO' => '516397951',

        'Умный тонометр' => '516759157',
        'Ремешки для умных часов Noerden' => '516759158',
        'Аксессуары' => '516664793',
        'LIFE 2' => '516755957',
        'LIFE 2 Плюс' => '516755958',

        'MATE 2' => '516755959',
        'CITY' => '516755960',
        'MATE 2 Плюс' => '516664848',
        'Умные часы' => '516664847'
    ],
];

function parseAttributes($attributes)
{

    $productAttributes = array();

    $needleArray = [
        "vendor" => "f3f556dc-afe9-11e7-7a6c-d2a900036ddd",
        "price" => "a4e869cf-8dc0-11e9-9ff4-31500015e1ea",
        "category" => "031b1310-0106-11e8-7a6c-d2a9000c0ea7",
        "weight" => "bdedd780-13ef-11e7-7a34-5acf0017ad00",
        "length" => "d0a22ef2-42f1-11e8-9107-504800067e96",
        "width" => "d0a22cac-42f1-11e8-9107-504800067e95",
        "height" => "d0a2276d-42f1-11e8-9107-504800067e94"
    ];


    foreach ($needleArray as $needleKey => $needleVal) {
        foreach ($attributes as $attributeKey => $attributeValue) {
            if (false !== stripos($attributeValue, $needleVal)) {
                $attributeValue = json_decode($attributeValue, true);
                $productAttributes[$needleKey] = $attributeValue["value"];
            }
        }
    }


    return $productAttributes;
}

function getProducts($tmallIndex, $categories)
{


//    $regex = new Regex($tmallIndex);
    $regex = new Regex($tmallIndex.'","value":"yes"');

    $collection = (new Client('mongodb://MSSync-read:1547e70be122dc285a2d24ad@23.105.225.41:27017/?authSource=MSSync&readPreference=primary&appname=MongoDB%20Compass&ssl=false'))->mysales->products;


    /*    $cursor = $collection->find(
            [
                'attributes' => $regex
            ]
        );*/

    $cursor = $collection->find(
        [
            'attributes' => $regex
        ]
    );


    $products = array();
    $product = array();
    foreach ($cursor as $document) {

        $imageUrl = isset($document['image']) ? "https://online.moysklad.ru/app/download/" . $document['image'] : '';

        $description = '';
        if (isset($document['description'])) {
            $description = str_replace("'", "", $document['description']);
            $description = preg_replace('/[^\p{L}\p{N}]/u', ' ', $description);
        }

        $name = '';
        if (isset($document['name'])) {
            $name = str_replace("'", "", $document['name']);
            $name = preg_replace('/[^\p{L}\p{N}]/u', ' ', $name);
        }


        $product['id'] = $document['code'];
        $product['name'] = $name;
        $product['description'] = $description;
        $product['article'] = $document['article'] ?? '';
        $product['picture'] = $imageUrl;
        $product['attributes'] = parseAttributes($document['attributes']);

        $productCategory = $product['attributes']['category'] ?? '';
        $category = $categories[$productCategory] ?? '';
        $product['vendor'] = $product['attributes']['vendor'] ?? '';
        $product['price'] = $product['attributes']['price'] ?? '';
        $product['categoryId'] = $category;
        $product['weight'] = $product['attributes']['weight'] ?? '';
        $product['length'] = $product['attributes']['length'] ?? '';
        $product['width'] = $product['attributes']['width'] ?? '';
        $product['height'] = $product['attributes']['height'] ?? '';
        $products[] = $product;
    }

    return $products;

}

function loopShop($tmallIndex, $categories)
{
    $categoriesXML = '';

    foreach ($categories as $key => $category) {
        $categoriesXML .= '<category id="' . $category . '">' . $key . '</category>';
    }


    $offers = getProducts($tmallIndex, $categories);
    $offerBodyXML = '';

    foreach ($offers as $key => $offer) {
        $offerXML = "
            <offer id='" . $offer['id'] . "' group_id='" . $offer['article'] . "'>
                <price>" . $offer['price'] . "</price>
                <height>" . $offer['height'] . "</height>
                <length>" . $offer['length'] . "</length>
                <width>" . $offer['width'] . "</width>
                <weight>" . $offer['weight'] . "</weight>
                <quantity>1</quantity>
                <categoryId>" . $offer['categoryId'] . "</categoryId>
                <vendor>" . $offer['vendor'] . "</vendor>
                <picture>" . $offer['picture'] . "</picture>
                <name>" . $offer['name'] . "</name>
                <description>" . $offer['description'] . "</description>
                <param name='Артикул'>" . $offer['article'] . "</param>
            </offer>";
        $offerBodyXML .= $offerXML;
    }

    $offersHeaderXML = '<?xml version="1.0" encoding="UTF-8"?><yml_catalog date="' . date('Y-m-d H:i') . '"  >
    <shop>
        <categories>
            ' . $categoriesXML . '
        </categories>
        <offers>';

    $offersFooterXML = '</offers></shop></yml_catalog>';

    $offersXML = $offersHeaderXML . $offerBodyXML . $offersFooterXML;

    return $offersXML;

}


foreach ($tmallIndexes as $shop => $index) {

    $offersXML = '';
    $dom = '';

//    var_dump($tmallIndexes[$shop], $categoriesAll[$shop]) ;
    $offersXML = loopShop($tmallIndexes[$shop], $categoriesAll[$shop]);

//    echo $offersXML;
    $dom = new DOMDocument;
    $dom->preserveWhiteSpace = FALSE;
    libxml_use_internal_errors(true);


    if (!$dom->loadXML($offersXML)) {
        $errors = libxml_get_errors();
        echo $shop . PHP_EOL;
        var_dump($errors);
        telegram($shop . " ОШИБКА создания YML!", '-391758030');

        libxml_clear_errors();
    } else {
        $dom->save("xml/aliexpress_yml_$shop.xml");
    }


}



/*$categoriesXML = '';

foreach ($categories["iRobot"] as $key => $category) {
    $categoriesXML .= '<category id="' . $category . '">' . $key . '</category>';
}


$offers = getProducts('0bbcde02-81f4-11e9-9109-f8fc0004deca', $categories["iRobot"]);
$offerBodyXML = '';

foreach ($offers as $key => $offer) {
    $offerXML = "
            <offer id='" . $offer['id'] . "' group_id='" . $offer['article'] . "'>
                <price>" . $offer['price'] . "</price>
                <height>" . $offer['height'] . "</height>
                <length>" . $offer['length'] . "</length>
                <width>" . $offer['width'] . "</width>
                <weight>" . $offer['weight'] . "</weight>
                <quantity>1</quantity>
                <categoryId>" . $offer['categoryId'] . "</categoryId>
                <vendor>" . $offer['vendor'] . "</vendor>
                <picture>" . $offer['picture'] . "</picture>
                <name>" . $offer['name'] . "</name>
                <description>" . $offer['description'] . "</description>
                <param name='Артикул'>" . $offer['article'] . "</param>
            </offer>";
    $offerBodyXML .= $offerXML;
}

$offersHeaderXML = '<?xml version="1.0" encoding="UTF-8"?><yml_catalog date="' . date('Y-m-d H:i') . '"  >
    <shop>
        <categories>
            ' . $categoriesXML . '
        </categories>
        <offers>';

$offersFooterXML = '</offers></shop></yml_catalog>';

$offersXML = $offersHeaderXML . $offerBodyXML . $offersFooterXML;
//echo $offersXML;

$dom = new DOMDocument;
$dom->preserveWhiteSpace = FALSE;
$dom->loadXML($offersXML);

$dom->save('xml/aliexpress_yml_iRobot.xml');*/