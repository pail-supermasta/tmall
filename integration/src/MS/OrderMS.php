<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 10:15
 */

namespace Avaks\MS;


class OrderMS
{

    public $id;
    public $name;
    public $positions;
    public $state;

    function __construct($id = null, $name = null, $positions = null)
    {
        $this->id = $id;
        $this->positions = $positions;
        $this->name = $name;
    }

    public function getById()
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id);
        return (json_decode($res, true));
    }

    public function getByName()
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/?filter=name=' . $this->name);
        return (json_decode($res, true))['rows'][0];
    }

    public function setSticker($postdata)
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        return $res;
    }

    public function setTrackNum($trackNum)
    {

        $put_data = array();
        $attribute = array();

        $attribute['id'] = 'a446677c-46b8-11e7-7a34-5acf0031d7b9';
        $attribute['value'] = (int)$trackNum;
        $put_data['attributes'][] = $attribute;


        $postdata = json_encode($put_data);

        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        return $res;
    }

    public function setToPack()
    {
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/327c02b4-75c5-11e5-7a40-e89700139937",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = '327c02b4-75c5-11e5-7a40-e89700139937';

        return $res;
    }
}

//require_once '../../vendor/autoload.php';
//$orderMS = new OrderMS('f861e845-d076-11e9-0a80-025b000db720');
///*$res = $orderMS->setTrackNum('1234545');
//var_dump($res);*/
//
//define('ID_REGEXP', '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/'); // Регулярка для UUID
//
//$orderMSDetails = $orderMS->getById();
//
///*получить Статус заказа в МС*/
//preg_match(ID_REGEXP, $orderMSDetails['state']['meta']['href'], $matches);
//$state_id = $matches[0];
//$orderMS->state = $state_id;
//var_dump($orderMS->state);
////$orderMS->setToPack();