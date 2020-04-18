<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.12.2019
 * Time: 9:26
 */


ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8;');


require_once '../integration/vendor/autoload.php';
require_once '../integration/ali_express/taobao/TopSdk.php';


define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

$order = '5003438318331748';
$sessionKey = '50002300d289mlqwnqAebm1lqueSpe9ECuAKVtDg1b6f0174jvgyezVnDsmJ4OkbLKm';
$result_success = getonlinelogisticsservicelistbyorderid ($order, $sessionKey);



function getonlinelogisticsservicelistbyorderid($order, $sessionKey)
{


    $c = new TopClient;
//    $c->gatewayUrl = "http://140.205.164.4/top/router/rest";
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;
    $req = new AliexpressLogisticsRedefiningGetonlinelogisticsservicelistbyorderidRequest;
    $req->setGoodsWidth("1");
    $req->setGoodsHeight("1");
    $req->setGoodsWeight("1.5");
    $req->setGoodsLength("1");
    $req->setOrderId($order);
    $resp = $c->execute($req, $sessionKey);
    var_dump($resp);


    $resp = json_encode((array)$resp);
    $resp = json_decode($resp, true);
    foreach ($resp['result_list']['result'] as $result){
        echo $result['logistics_service_name'];
    }




    $result_success = $resp->result_success ?? false;

    return $result_success;
}

function createwarehouseorder($order,$sessionKey)
{
    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;

    $req = new AliexpressLogisticsCreatewarehouseorderRequest;
    $address_d_t_os = new Addressdtos;
    $sender = new AeopWlDeclareAddressDto;

    /**
     * CONSTANTS BEGIN
     */
    $sender->phone = "74954812282 доб 121";
    $sender->fax = "";
    $sender->member_type = "";
    $sender->trademanage_id = "";
    $sender->street = "Павелецкая";
    $sender->country = "RU";
    $sender->city = "Москва";
    $sender->county = "";
    $sender->email = "info@avaks.org";
    $sender->address_id = -1;
    $sender->name = "ООО Незабудка";
    $sender->province = "Москва";
    $sender->street_address = "RU, Москва, Павелецкая ул, дом 2, стр 21, оф 237";
    $sender->mobile = "74954812282";
    $sender->post_code = "115114";

    $address_d_t_os->sender = $sender;

    $pickup = new AeopWlDeclareAddressDto;
    $pickup->phone = "74954812282 доб 121";
    $pickup->fax = "";
    $pickup->member_type = "";
    $pickup->trademanage_id = "";
    $pickup->street = "Павелецкая";
    $pickup->country = "RU";
    $pickup->city = "Москва";
    $pickup->county = "";
    $pickup->email = "info@avaks.org";
    $pickup->address_id = -1;
    $pickup->name = "ООО Незабудка";
    $pickup->province = "Москва";
    $pickup->street_address = "Россия, Москва, Павелецкая ул, дом 2, стр 21, оф 237";
    $pickup->mobile = "74954812282";
    $pickup->post_code = "115114";

    $address_d_t_os->pickup = $pickup;

    $refund = new AeopWlDeclareAddressDto;
    $refund->phone = "74954812282 доб 121";
    $refund->fax = "";
    $refund->member_type = "";
    $refund->trademanage_id = "";
    $refund->street = "Павелецкая";
    $refund->country = "RU";
    $refund->city = "Москва";
    $refund->county = "";
    $refund->email = "info@avaks.org";
    $refund->address_id = -1;
    $refund->name = "ООО Незабудка";
    $refund->province = "Москва";
    $refund->street_address = "Россия, Москва, Павелецкая ул, дом 2, стр 21, оф 237";
    $refund->mobile = "74954812282";
    $refund->post_code = "115114";

    $address_d_t_os->refund = $refund;


    /**
     * CONSTANTS END
     */

    $receiver = new AeopWlDeclareAddressDto;
    $receiver->phone = "89857222970";
    $receiver->fax = "";
    $receiver->member_type = "";
    $receiver->trademanage_id = "";
    $receiver->street = "Пресненская набережная";
    $receiver->post_code = "123112";
    $receiver->country = "RU";
    $receiver->city = "Moscow";
    $receiver->county = "";
    $receiver->email = "";
    $receiver->address_id = -1;
    $receiver->name = "Дмитрий Зубков";
    $receiver->province = "Moscow";
    $receiver->street_address = "Пресненская набережная Дом 10 блок Ц (башня на набережной), Москва, Москва, Russian Federation";
    $receiver->mobile = "9857222970";

    $address_d_t_os->receiver = $receiver;

    $req->setAddressDTOs(json_encode($address_d_t_os,JSON_UNESCAPED_UNICODE));
    $declare_product_d_t_os = new AeopWlDeclareProductForTopDto;
/*    $declare_product_d_t_os->aneroid_markup = false;
    $declare_product_d_t_os->breakable = false;
    $declare_product_d_t_os->category_cn_desc = "";
    $declare_product_d_t_os->category_en_desc = "";
    $declare_product_d_t_os->contains_battery = false;
    $declare_product_d_t_os->hs_code = null;
    $declare_product_d_t_os->only_battery = false;
    $declare_product_d_t_os->product_declare_amount = "1.3";
    $declare_product_d_t_os->product_id = "4000229833868";
    $declare_product_d_t_os->product_num = "2";
    $declare_product_d_t_os->product_weight = "1.5";
    $declare_product_d_t_os->sc_item_code = "";
    $declare_product_d_t_os->sc_item_id = "4000229833868";
    $declare_product_d_t_os->sc_item_name = "";
    $declare_product_d_t_os->sku_code = "4607947683912";
    $declare_product_d_t_os->sku_value = ""; */

    $declare_product_d_t_os->aneroid_markup = false;
    $declare_product_d_t_os->breakable = false;
    $declare_product_d_t_os->category_cn_desc = "连衣裙";
    $declare_product_d_t_os->category_en_desc = "dress";
    $declare_product_d_t_os->contains_battery = false;
    $declare_product_d_t_os->hs_code = "62044300";
    $declare_product_d_t_os->only_battery = false;
    $declare_product_d_t_os->product_declare_amount = "39.84";
    $declare_product_d_t_os->product_id = "4000229833868";
    $declare_product_d_t_os->product_num = 1;
    $declare_product_d_t_os->product_weight = 3;
    $declare_product_d_t_os->sc_item_code = "scItem code";
    $declare_product_d_t_os->sc_item_id = 0;
    $declare_product_d_t_os->sc_item_name = "scItem name";
    $declare_product_d_t_os->sku_code = "sku code";
    $declare_product_d_t_os->sku_value = "sku value";

    /*
     * C:\wamp64\bin\php\php7.3.5\php.exe C:\Users\User\Documents\aliexpr.avaks.org\test\newMethodsCainiao.php
object(stdClass)#12 (3) {
  ["result"]=>
  object(stdClass)#11 (6) {
    ["error_code"]=>
    int(1)
    ["out_order_id"]=>
    int(162847969542)
    ["success"]=>
    bool(true)
    ["trade_order_from"]=>
    string(2) "AE"
    ["trade_order_id"]=>
    int(5002320926031748)
    ["warehouse_order_id"]=>
    int(5489667979)
  }
  ["result_success"]=>
  bool(true)
  ["request_id"]=>
  string(12) "6iq57svmg8ws"
}

Process finished with exit code 0
*/

    $req->setDeclareProductDTOs(json_encode($declare_product_d_t_os,JSON_UNESCAPED_UNICODE));

//    logistics_service_name from getonlinelogisticsservicelistbyorderid
    $req->setDomesticLogisticsCompany("");
//  logistics_service_id from getonlinelogisticsservicelistbyorderid
    $req->setDomesticLogisticsCompanyId("-1");
//????
    $req->setDomesticTrackingNo("0");
// CPAM_WLB_FPXSZ; CPAM_WLB_CPHSH; CPAM_WLB_ZTOBJ; HRB_WLB_ZTOGZ; HRB_WLB_ZTOSH ??????? from getonlinelogisticsservicelistbyorderid
    $req->setWarehouseCarrierService("AE_RU_MP_OVERSIZE_PH3_13452545");

    $req->setPackageNum("");
    $req->setTradeOrderFrom("AE");
    $req->setTradeOrderId($order);
    $req->setUndeliverableDecision("0");
    $req->setInvoiceNumber("");
    $resp = $c->execute($req, $sessionKey);
    var_dump($resp);

    // LP NUMBER LP00165391200165
    /*["out_order_id"]=>
    int(165391200165)*/
}

//createwarehouseorder($order,$sessionKey);