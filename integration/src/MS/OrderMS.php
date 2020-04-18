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
    public $lpNumber;
    public $trackNum;

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

    public function updateOrder($postdata)
    {
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        return $res;
    }

    public function setTrackNum($mailNo)
    {

        $put_data = array();
        $attribute = array();

        $attribute['id'] = '8a500683-10fc-11ea-0a80-0533000590c8';
        $attribute['value'] = $mailNo;
        $put_data['attributes'][] = $attribute;


        $postdata = json_encode($put_data);

        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->trackNum = $mailNo;
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

    public function setStatus()
    {

        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/'.$this->state.'",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');

        return $res;
    }

    public function setStateProcessManually()
    {
        $postdata = '{
            "state": {
                "meta": {
                    "href": "https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/552a994e-2905-11e7-7a31-d0fd002c3df2",
                    "type": "state",
                    "mediaType": "application/json"
                }
            }
        }';
        $res = '';
        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        $this->state = '552a994e-2905-11e7-7a31-d0fd002c3df2';

        return $res;
    }



    public function setLP($LP)
    {
        $this->lpNumber = $LP;
        $postdata = '{"externalCode": "'.$this->lpNumber.'"}';

        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id, $postdata, 'put');
        return $res;

    }

    public function updatePositions($postdata,$positionID)
    {

        $res = CurlMoiSklad::curlMS('/entity/customerorder/' . $this->id.'/positions/'.$positionID, $postdata, 'put');
        return $res;

    }
}
