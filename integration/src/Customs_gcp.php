<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21.08.2019
 * Time: 15:03
 */

namespace Avaks;


use Picqer\Barcode\BarcodeGeneratorPNG;

class Customs_gcp
{


    public static function findUUID($stack)
    {
        $ID_REGEXP = '/[0-9a-z]{8}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{4}-[0-9a-z]{12}/';// Регулярка для UUID
        /*get state of order in MS*/
        preg_match($ID_REGEXP, $stack, $matches);

        if (sizeof($matches) == 1) {
            return $matches[0];
        } else {
            return false;
        }
    }

    public function generateCainiaoSticker(array $toPrint)
    {


        $generator = new BarcodeGeneratorPNG();
//        $mailNo = 'AEWH000657988RU4';
        $mailNo = $toPrint['mailNo'];


//        $deliveryAddress = '<div id="deliveryAddress" class="">309500, Старый Оскол, Белгородская область, дом 13 подъезд 2 квартира 46 северный микрорайон</div>';
        $deliveryAddress = '<div id="deliveryAddress" class="">' . $toPrint['fullAddress'] . '</div>';
//        $receiverName = '<div id="receiverName" class="">Каюмов Руслан Наилевич</div>';
        $receiverName = '<div id="receiverName" class="">' . $toPrint['receiverName'] . '</div>';
//        $receiverPhone = '<div id="receiverPhone" class="">9227829596,</div>';
        $receiverPhone = '<div id="receiverPhone" class="">' . str_replace(' ', '', $toPrint['receiverPhone']) . '</div>';
//        $otgruzkaDate = '<div id="otgruzkaDate" class="">2019-08-11</div>';
        $otgruzkaDate = '<div id="otgruzkaDate" class="">' . $toPrint['otgruzkaDate'] . '</div>';
        $mailNoHTML = '<div id="mailNo" class="">' . $mailNo . '</div>';


//        $LPNum = 'LP00141041126618';
        $LPNum = $toPrint['LPNum'];
//        $AEOrderId = '705317922239895';
        $AEOrderId = $toPrint['AEOrderId'];
//        $productDescriptions = array('HOBOT 298 Household Windows Cleaner Robot Window Cleaning Vacuum Cleaner Wiper Wet Dry Remote Control Electric Washing Glass', 'HOBOT 298 Household Windows Cleaner Robot Window Cleaning Vacuum Cleaner Wiper Wet Dry Remote Control Electric Washing Glass');
        $productDescriptions = $toPrint['productDescriptions'];


        $LPNumHTML = '<div id="LPNum" class="">' . $LPNum . '</div>';
        $AEOrderIdHTML = '<div id="AEOrderId" class="">' . $AEOrderId . '</div>';
        $AEOrderIdPageOneHTML = '<div id="AEOrderIdPageOne" class="">#' . $AEOrderId . '</div>';
        $productDescriptionHTML = '';
        foreach ($productDescriptions as $productDescription) {
            $productDescriptionHTML .= '<div style="margin-top: 20px;" class="productDescription">' . $productDescription . '</div>';
        }
        $productDescriptionsHTML = '<div class="productDescriptionsHTML">' . $productDescriptionHTML . '</div>';

        $mailNoHTML2 = '<div id="mailNo2" class="">' . $mailNo . '</div>';

        try {
            $mpdf = new \Mpdf\Mpdf(['margin_left' => '5',
                'margin_right' => '5',
                'margin_top' => '5',
                'margin_bottom' => '0',
                'margin_header' => '0',
                'margin_footer' => '0',
                'debug' => true]);
            $mpdf->showImageErrors = true;
        } catch (\Mpdf\MpdfException $e) {
        }

        try {

            $barRaw = $generator->getBarcode($mailNo, $generator::TYPE_CODE_128);
            $barcode = '<img id="barcodeTarget" src="data:image/png;base64,' . base64_encode($barRaw) . '">';
        } catch (\Picqer\Barcode\Exceptions\BarcodeException $e) {
        }

        $dynamicHTML = $deliveryAddress . $receiverName . $receiverPhone . $mailNoHTML . $otgruzkaDate . $AEOrderIdPageOneHTML;

        $html = '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <meta http-equiv="X-UA-Compatible" content="ie=edge">
                    <title>Document</title>
                    <style>
                    @page { sheet-size:  3425px 2728px}
                    /*.w0{
                        width: 70mm;
                    }
                    .h0{
                        height: 100mm;
                    }*/
                    body{
                        margin: 0;
                    }
                    .stickerWrap {
                     position: absolute;
                        height: 2728px;
                        top: 5px;
                        left: 5px;
                        z-index: 0;
                    }
                    .page{
                         position: relative;
                    }
                
                    #barcodeTarget {
                        
                         width: 292px;
                        height: 77px;
                        transform: translate(-60px, -220px) rotate(90deg);
                       
                    }
                
                    #deliveryAddress,
                    #receiverName,
                    #receiverPhone {
                        width: 284px;
                        white-space: pre-wrap;
                        white-space: -moz-pre-wrap;
                        white-space: -o-pre-wrap;
                        word-wrap: break-word;
                        font-family: Arial;
                        overflow: hidden;
                        position: absolute;
                    }
                    #deliveryAddress{
                        
                        font-size: 11px;
                        top: 96px;
                        left: 212px;
                        height: 43px;
                        rotate: 90;
                       
                    }
                    #receiverName{
                        
                        top: 96px;
                        left: 162px;
                        height: 43px;
                        font-size: 15px;
                        letter-spacing: -1px;
                        font-weight: 600;
                        rotate: 90;
                        
                    }
                    #receiverPhone{

                       
                       top: 96px;
                       left: 135px;
                        height: 43px;
                        font-size: 13px;
                        letter-spacing: 0;
                        font-weight: 600;
                        rotate: 90;
                        
                    }
                    #otgruzkaDate{
                    
                        position: absolute;
                        
                        top: 70px;
                        left: 22px;
                        font-size: 11px;
                        letter-spacing: 0;
                        rotate: 90;
                        
                    }
                    #AEOrderIdPageOne{
                   
                        position: absolute;
                                      
                        top: 162px;
                        left: 22px;
                        font-size: 10px;
                        letter-spacing: 0;
                        rotate: 90;     
                    }
                    
                    #mailNo{
                       
                        position: absolute;
                        top: 116px;
                        height: 43px;
                        font-size: 13px;
                        rotate: 90;
                       
                    }
                    
                    #LPNum,.productDescription,#mailNo2 {
                        font-size: 12px;
                    }
                    
                    #AEOrderId{
                        font-size: 20.0028px;
                    }
                    .productDescriptionsHTML{
                        margin: 16px 0 0 60px;
                    }
                   
                
                    </style>
                </head>
                <body>
                <!-- image path to images search from the file that calls Customs.php-->
                <img class="stickerWrap" src="http://aliexpr.avaks.org/integration/images/cainiaoSticker/template-1.png" alt="">
                        
                
                </body>
                </html>';



        try {
            $mpdf->WriteHTML($html);
        } catch (\Mpdf\MpdfException $e) {
        }


        try {
            $pdfCode = $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
        } catch (\Mpdf\MpdfException $e) {
            error_log(date("Y-m-d H:i:s", strtotime(gmdate("Y-m-d H:i:s")) + 3 * 60 * 60) . " " . $AEOrderId . " " . $e . PHP_EOL, 3, "printStickerAEImage.log");
            die();
        }

        return $pdfCode;
    }

}

