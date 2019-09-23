<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.09.2019
 * Time: 16:33
 */

namespace Avaks\AE;


class Product
{
    public $id;
    public $skuCode;

    public function __construct($productID, $skuCode)
    {
        $this->id = $productID;
        $this->skuCode = $skuCode;
    }


    public function setStock($stock, $login)
    {

        $sessionKey = $login['sessionKey'];
        $getByIdres = $this->getById($sessionKey);
        $skuList = $getByIdres['aeop_ae_product_s_k_us']['aeop_ae_product_sku'];
        if (isset($skuList[1]['sku_code'])) {
            foreach ($skuList as $skuItem) {
                if ($skuItem['sku_code'] == $this->skuCode) {
                    $result['old_stock'] = $skuItem['ipm_sku_stock'];
                    $sku_id = $skuItem['id'];
                    if ($result['old_stock'] == $stock) {
                        $result['new_stock'] = false;
                    } else {
                        $c = new \TopClient;
                        $c->appkey = APPKEY;
                        $c->secretKey = SECRET;
                        $req = new \AliexpressPostproductRedefiningEditsingleskustockRequest;
                        $req->setProductId("$this->id");
                        $req->setSkuId("$sku_id");
                        $req->setIpmSkuStock("$stock");
                        $resp= $c->execute($req, $sessionKey);
                        $res = json_encode((array)$resp);
                        $result['new_stock'] = json_decode($res, true)['result']['modify_count'];
                    }
                }
            }
        } else {

            $skuItem = $skuList;
            $result['old_stock'] = $skuItem['ipm_sku_stock'];
            $sku_id = $skuItem['id'];
            if ($result['old_stock'] == $stock) {
                $result['new_stock'] = false;
            } else {
                $c = new \TopClient;
                $c->appkey = APPKEY;
                $c->secretKey = SECRET;
                $req = new \AliexpressPostproductRedefiningEditsingleskustockRequest;
                $req->setProductId("$this->id");
                $req->setSkuId("$sku_id");
                $req->setIpmSkuStock("$stock");
                $resp= $c->execute($req, $sessionKey);
                $res = json_encode((array)$resp);
                $result['new_stock'] = json_decode($res, true)['result']['modify_count'];
            }
        }
        return $result ?? false;
    }

// Берём из Алиэкспресс информацию о продукте, по ID товара в Алиэкспресс
    public function getById($sessionKey)
    {
        $c = new \TopClient();
        $c->appkey = APPKEY;
        $c->secretKey = SECRET;
        $req = new \AliexpressPostproductRedefiningFindaeproductbyidRequest;
        $req->setProductId("$this->id");
        $resp = $c->execute($req, $sessionKey);
        $res = json_encode((array)$resp);
        return json_decode($res, true)['result'] ?? false;
    }
}
