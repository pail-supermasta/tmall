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
//        $filter = ['_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'];
        //рц3 + mpnff
        $filter = ['_store' => [
            '$in' => [
                'f80cdf08-29a0-11e6-7a69-971100124ae8',
                '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'
            ]
        ]];
        $stockCursor = $collection->report_stock_all->find($filter);
        $stockMS = array();
        foreach ($stockCursor as $stock) {
            $available = 0;

            if (!isset($stock['reserve']) && $stock['isBundle'] == 1) {
                $available += $stock['stock'];
            } else {
                $available += $stock['stock'] - $stock['reserve'];
            }
            if ($available <= 0) {
                $available += 0;
            }
            if (isset($stockMS[$stock['_product']])) {
                $stockMS[$stock['_product']]['available'] += $available;
            } else {
                $stockMS[$stock['_product']] = array('available' => $available, 'updated' => $stock['updated']);
            }

        }

        return $stockMS;
    }
}

/*require_once '../../vendor/autoload.php';
$stocks = new Stocks();

var_dump($stocks->getAll());*/