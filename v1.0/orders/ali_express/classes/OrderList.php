<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 11.07.2019
 * Time: 14:20
 */

namespace Orders;


class OrderList
{
    private $data;

    public function __construct($result){
        $this->data = $result;
    }
    public function expose() {
        return get_object_vars($this);
    }
}