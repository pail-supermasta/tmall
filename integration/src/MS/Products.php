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

    public function getMsStock($product_id, $stores = array())
    {


        $avaliable_stock = 0;

        foreach ($stores as $key => $store) {
            $query = "SELECT (`stock` - `reserve`) as `avaliable_stock` FROM `ms_stock` WHERE `product_id` = '$product_id' AND `store_id` = '" . $store . "' ORDER BY `datetime` DESC LIMIT 1";

            $row = AvaksSQL::selectAllAssoc($query);
            $avaliable_stock = $avaliable_stock + ($row[0]['avaliable_stock'] ?? 0);
        }


        return $avaliable_stock;
    }
}
