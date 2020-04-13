<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 24.03.2020
 * Time: 11:19
 */


namespace Avaks\MS;

use Avaks\MS\MSSync;


class Stocks
{
    public $available;
    public $updated;
    public $found;

    public function getAll()
    {
        $collection = (new MSSync())->MSSync;
        $filter = ['_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'];
        $stockCursor = $collection->report_stock_all->find($filter);
        $stockMS = array();
        foreach ($stockCursor as $stock) {
            if (!isset($stock['reserve']) && $stock['isBundle'] == 1){
                $available = $stock['stock'];
            } else{
                $available = $stock['stock'] - $stock['reserve'];
            }
            if ($available <= 0) {
                $available = 0;
            }
            $stockMS[$stock['_product']] = array('available' => $available, 'updated' => $stock['updated']);
        }

        return $stockMS;
    }
}

/*require_once '../../vendor/autoload.php';
$stocks = new Stocks();

var_dump($stocks->getAll());*/