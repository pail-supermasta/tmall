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

$order = '5002320926031748';
$sessionKey = '500023000082eXbwToAg1af40ea9SRlzFmEOwoSCA3mTGhQaxQcZcjShLnX7vRkTL8r';
//$result_success = getonlinelogisticsservicelistbyorderid ($order, $sessionKey);
//var_dump($result_success);


function getonlinelogisticsservicelistbyorderid($order, $sessionKey)
{


    $c = new TopClient;
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
    $sender->street_address = "Россия, Москва, Павелецкая ул, дом 2, стр 21, оф 237";
    $sender->mobile = "74954812282";
    $sender->post_code = "115114";

    $address_d_t_os->sender = $sender;

    $pickup = new AeopWlDeclareAddressDto;
    $pickup->phone = "74954812282 доб 121";
    $pickup->fax = "";
    $pickup->member_type = "";
    $pickup->trademanage_id = "";
    $pickup->street = "Павелецкая";
    $pickup->country = "Россия";
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
    $refund->country = "Россия";
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



    $receiver = new AeopWlDeclareAddressDto;
    $receiver->phone = "89165866131";
    $receiver->fax = "";
    $receiver->member_type = "";
    $receiver->trademanage_id = "";
    $receiver->street = "улица Академика Виноградова";
    $receiver->post_code = "117133";
    $receiver->country = "Россия";
    $receiver->city = "Moscow";
    $receiver->county = "";
    $receiver->email = "";
    $receiver->address_id = -1;
    $receiver->name = "Северина Елена Наумовна";
    $receiver->province = "Moscow";
    $receiver->street_address = "улица Академика Виноградова д.10.кор.2 кв114, Moscow";
    $receiver->mobile = "9165866131";

    $address_d_t_os->receiver = $receiver;

    $req->setAddressDTOs(json_encode($address_d_t_os));
    $declare_product_d_t_os = new AeopWlDeclareProductForTopDto;
    $declare_product_d_t_os->aneroid_markup = false;
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
    $declare_product_d_t_os->sku_value = "";

    $req->setDeclareProductDTOs(json_encode($declare_product_d_t_os));

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
}

createwarehouseorder($order,$sessionKey);