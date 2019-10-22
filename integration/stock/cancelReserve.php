<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 22.10.2019
 * Time: 9:25
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("error_log", "php-error.log");


// Telegram err logs integration
require_once '../class/telegram.php';

require_once '../vendor/autoload.php';

use Avaks\MS\Orders;
use Avaks\MS\OrderMS;

$ordersMS = new Orders();
$getWaitPaymentResp = $ordersMS->getWaitPayment();
//var_dump($getWaitPaymentResp);


/*  add 3 hour shift from GMT to RU */
$offsetNow = 3 * 60 * 60;
$now = strtotime(gmdate("Y-m-d H:i:s")) + $offsetNow;
/*  add 10 hour shift */
$offset = 24 * 60 * 60;


foreach ($getWaitPaymentResp as $orderInWaitPayment) {
    $daysNotPaid = (strtotime($orderInWaitPayment['moment']) + $offset) - $now;
    $daysNotPaid = $daysNotPaid / 60 / 60 * (-1);

    $reset = false;

    if ($daysNotPaid >= 24) {
        $positions = json_decode($orderInWaitPayment['positions'], true);

        $ordersMS = new OrderMS($orderInWaitPayment['id']);
        /*clear reservation for each product*/
        foreach ($positions as $position) {
            if ($position['reserve'] > 0) {
                $postdata = '{
                  "reserve": 0
                }';
                $resetReserveResp = $ordersMS->updatePositions($postdata, $position['id']);
                if (strpos($resetReserveResp, 'обработка-ошибок') > 0 || $resetReserveResp == '') {
                    telegram("resetReserveResp error found " . $result['mailNo'], '-320614744');
                    error_log($resetReserveResp, 3, "resetReserveResp.log");
                }
                $reset = true;
            }
        }
        if ($reset == true) {
            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . $orderInWaitPayment['name'] . PHP_EOL, 3, "orderInWaitPayment.log");
        }
    }
}