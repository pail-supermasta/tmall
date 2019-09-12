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
        $products = AvaksSQL::selectProductsArray($query);
        return $products;

    }
}
