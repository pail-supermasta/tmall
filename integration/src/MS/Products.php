<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 13:16
 */

namespace Avaks\MS;

use Avaks\Customs;
use Avaks\SQL\AvaksSQL;

class Products
{
    public $orderPositionsRaw;
    public $MSTmallFieldName;


    public function __construct($orderPositions = false)
    {
        $this->orderPositionsRaw = $orderPositions;
    }

    public function getOrderProducts()
    {
        /*parse raw json and get product id*/

        $positions = json_decode($this->orderPositionsRaw, true);

        $products = array();
        foreach ((array)$positions as $position) {
            /*search by id in ms_product*/

            $position = Customs::findUUID($position['assortment']['meta']['href']);
            $products[] = AvaksSQL::selectProductById($position);
        }
        return $products;
    }

    public function getTmallProducts($field_id)
    {

        $query = "SELECT * FROM `ms_product` WHERE `attributes` LIKE '%{$field_id}%'";
        $products = AvaksSQL::selectAllAssoc($query);
        return $products;

    }

    public function findWithFieldTmall($login)
    {
        $searchStack = array(
            'NezabudkaMR@yandex.ru' => 'TMall ID (Morphy Richards)',
            'bestgoodsstore@yandex.ru' => 'TMall ID (Best Goods)',
            'NezabudkaiRobot@yandex.ru' => 'TMall ID (iRobot)',
            'NezabudkaND@yandex.ru' => 'TMall ID (Noerden)',
            'novinkiooo@yandex.ru' => 'TMall ID'

        );
        $collection = (new MSSync())->MSSync;

        $filter = ['_attributes.' . $searchStack[$login] => ['$exists' => true]];
        $productCursor = $collection->product->find($filter)->toArray();
        $this->MSTmallFieldName = $searchStack[$login];
        return $productCursor;
    }

    public function getMsStock($product_id, $stores = array())
    {


        $avaliable_stock = 0;
        $product_name = false;

        foreach ($stores as $key => $store) {
            $query = "SELECT (`stock` - `reserve`) as `avaliable_stock`, product_name FROM `ms_stock_now` WHERE `product_id` = '$product_id' AND `store_id` = '" . $store . "'";

            $row = AvaksSQL::selectAllAssoc($query);
            $avaliable_stock = $avaliable_stock + ($row[0]['avaliable_stock'] ?? 0);
            $product_name = $row[0]['product_name'] ?? false;
        }


        return array($avaliable_stock, $product_name);
    }
}
