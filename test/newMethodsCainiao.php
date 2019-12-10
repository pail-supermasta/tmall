<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10.12.2019
 * Time: 9:26
 */


ini_set('display_errors', 1);

header('Content-Type: application/json');


require_once '../integration/vendor/autoload.php';
require_once '../integration/ali_express/taobao/TopSdk.php';




define('APPKEY', '27862248');
define('SECRET', 'ca6916e55a087b3561b5077fc8b83ee6');

$order = '8007699766613905';
$sessionKey = '500023000082eXbwToAg1af40ea9SRlzFmEOwoSCA3mTGhQaxQcZcjShLnX7vRkTL8r';
//$result_success = getonlinelogisticsservicelistbyorderid ($order, $sessionKey);
//
//var_dump($result_success);
function getonlinelogisticsservicelistbyorderid ($order, $sessionKey)
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
    $req->setOrderId("8007699766613905");
    $resp = $c->execute($req, $sessionKey);
    var_dump($resp);
    $result_success = $resp->result_success ?? false;

    return $result_success;
}

function createwarehouseorder ($sessionKey){
    $c = new TopClient;
    $c->format = 'json';
    $c->appkey = APPKEY;
    $c->secretKey = SECRET;

    $req = new AliexpressLogisticsCreatewarehouseorderRequest;
    $address_d_t_os = new Addressdtos;
    $sender = new AeopWlDeclareAddressDto;

    $sender->phone="74954812282 доб 121";
    $sender->fax="";
    $sender->member_type="";
    $sender->trademanage_id="";
    $sender->street="Павелецкая";
    $sender->country="RU";
    $sender->city="Москва";
    $sender->county="county";
    $sender->email="info@avaks.org";
    $sender->address_id="";
    $sender->name="ООО Незабудка";
    $sender->province="Москва";
    $sender->street_address="Россия, Москва, Павелецкая ул, дом 2, стр 21, оф 237";
    $sender->mobile="74954812282";
    $sender->post_code="115114";

    $address_d_t_os->sender = $sender;

    $pickup = new AeopWlDeclareAddressDto;
    $pickup->phone="098-234234";
    $pickup->fax="234234234";
    $pickup->member_type="类型";
    $pickup->trademanage_id="cn234234234";
    $pickup->street="street";
    $pickup->country="RU";
    $pickup->city="Moscow";
    $pickup->county="county";
    $pickup->email="alibaba@alibaba.com";
    $pickup->address_id="1000";
    $pickup->name="Linda";
    $pickup->province="Moscow";
    $pickup->street_address="street address";
    $pickup->mobile="18766234324";
    $pickup->post_code="056202";

    $address_d_t_os->pickup = $pickup;

    $receiver = new AeopWlDeclareAddressDto;
    $receiver->phone="098-234234";
    $receiver->fax="234234234";
    $receiver->member_type="类型";
    $receiver->trademanage_id="cn234234234";
    $receiver->street="street";
    $receiver->post_code="056202";
    $receiver->country="RU";
    $receiver->city="Moscow";
    $receiver->county="county";
    $receiver->email="alibaba@alibaba.com";
    $receiver->address_id="1000";
    $receiver->name="Linda";
    $receiver->province="Moscow";
    $receiver->street_address="street address";
    $receiver->mobile="18766234324";

    $address_d_t_os->receiver = $receiver;

    $refund = new AeopWlDeclareAddressDto;
    $refund->phone="098-234234";
    $refund->fax="234234234";
    $refund->member_type="类型";
    $refund->trademanage_id="cn234234234";
    $refund->street="street";
    $refund->country="RU";
    $refund->city="Moscow";
    $refund->county="county";
    $refund->email="alibaba@alibaba.com";
    $refund->address_id="1000";
    $refund->name="Linda";
    $refund->province="Moscow";
    $refund->street_address="street address";
    $refund->mobile="18766234324";
    $refund->post_code="056202";

    $address_d_t_os->refund = $refund;
    $req->setAddressDTOs(json_encode($address_d_t_os));

    $declare_product_d_t_os = new AeopWlDeclareProductForTopDto;
    $declare_product_d_t_os->aneroid_markup="false";
    $declare_product_d_t_os->breakable="false";
    $declare_product_d_t_os->category_cn_desc="连衣裙";
    $declare_product_d_t_os->category_en_desc="dress";
    $declare_product_d_t_os->contains_battery="false";
    $declare_product_d_t_os->hs_code="77234";
    $declare_product_d_t_os->only_battery="false";
    $declare_product_d_t_os->product_declare_amount="1.3";
    $declare_product_d_t_os->product_id="1000";
    $declare_product_d_t_os->product_num="2";
    $declare_product_d_t_os->product_weight="1.5";
    $declare_product_d_t_os->sc_item_code="scItem code";
    $declare_product_d_t_os->sc_item_id="1000";
    $declare_product_d_t_os->sc_item_name="scItem name";
    $declare_product_d_t_os->sku_code="sku code";
    $declare_product_d_t_os->sku_value="sku value";

    $req->setDeclareProductDTOs(json_encode($declare_product_d_t_os));
    $req->setDomesticLogisticsCompany("tiantiankuaidi");
    $req->setDomesticLogisticsCompanyId("505");
    $req->setDomesticTrackingNo("L12345899");
    $req->setPackageNum("1");
    $req->setTradeOrderFrom("ESCROW");
    $req->setTradeOrderId("66715700375804");
    $req->setUndeliverableDecision("0");
    $req->setWarehouseCarrierService("CPAM_WLB_FPXSZ;CPAM_WLB_CPHSH;CPAM_WLB_ZTOBJ;HRB_WLB_ZTOGZ;HRB_WLB_ZTOSH");
//    $req->setInvoiceNumber("38577123");
    $resp = $c->execute($req, $sessionKey);
    var_dump($resp);
}
createwarehouseorder($sessionKey);