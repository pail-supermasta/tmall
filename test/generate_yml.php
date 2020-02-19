<?php


require_once "../integration/vendor/autoload.php";

use MongoDB\BSON\Regex;
use MongoDB\Client;

date_default_timezone_set('Europe/Moscow');


$tmallIndexes = [
//    'novinkiooo'=>'e8a40577-77b9-11e9-912f-f3d40003d45d', //32998880655
//    'bestgoods'=>'0bbcd3e6-81f4-11e9-9109-f8fc0004dec8',// 4000330839485
//    'Morphy Richards'=>'0bbcd991-81f4-11e9-9109-f8fc0004dec9', // 33017680130
    'iRobot' => '0bbcde02-81f4-11e9-9109-f8fc0004deca', //33022490020
//    'Noerden'=>'0bbce15c-81f4-11e9-9109-f8fc0004decb' //33018957808
];

$categories = [
    'Роботы-мойщики полов' => '516759225',
    'Роботы-пылесосы' => '516759223',
    'Запчасти и аксессуары' => '516755970'
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

    $regex = new Regex($tmallIndex);


    $collection = (new Client('mongodb://admin:kmNqjyDM8o7U@62.109.5.225:27017/mysales?authSource=admin&readPreference=primary&appname=MongoDB%20Compass&ssl=false'))->mysales->products;


    $cursor = $collection->find(
        [
            'attributes' => $regex
        ]
    );


    $products = array();
    $product = array();
    foreach ($cursor as $document) {

        $imageUrl = isset($document['image']) ? "https://online.moysklad.ru/app/download/" . $document['image'] : '';

        $product['id'] = $document['code'];
        $product['name'] = $document['name'];
        $product['description'] = $document['description'] ?? '';
        $product['article'] = $document['article'] ?? '';
        $product['picture'] = $imageUrl;
        $product['attributes'] = parseAttributes($document['attributes']);

        $category = $categories[$product['attributes']['category']] ?? '';
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

$categoriesXML = '';

foreach ($categories as $key => $category) {
    $categoriesXML .= '<category id="' . $category . '">' . $key . '</category>';
}


$offers = getProducts('0bbcde02-81f4-11e9-9109-f8fc0004deca', $categories);
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

$dom->save('products/xml/aliexpress_yml_iRobot.xml');

