<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 17.09.2019
 * Time: 16:28
 */


ini_set('display_errors', 1);


//header('Content-Type: application/json');


require_once '../integration/vendor/autoload.php';


$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
$mailNo = 'AEWH0000657988RU4';

// Page 1 variables

$deliveryAddress = '<div id="deliveryAddress" class="">309500, Старый Оскол, Белгородская область, дом 13 подъезд 2 квартира 46 северный микрорайон</div>';
$receiverName = '<div id="receiverName" class="">Каюмов Руслан Наилевич</div>';
$receiverPhone = '<div id="receiverPhone" class="">9227829596,</div>';
$otgruzkaDate = '<div id="otgruzkaDate" class="">2019-08-11</div>';
$mailNoHTML = '<div id="mailNo" class="">' . $mailNo . '</div>';


// Page 2 variables

$LPNum = 'LP00141041126618';
$AEOrderId = '705317922239895';
$productDescriptions = array('HOBOT 298 Household Windows Cleaner Robot Window Cleaning Vacuum Cleaner Wiper Wet Dry Remote Control Electric Washing Glass HOBOT 298 Household Windows Cleaner Robot Window Cleaning Vacuum Cleaner Wiper Wet Dry Remote Control Electric Washing Glass HOBOT 298 Household Windows Cleaner Robot Window Cleaning Vacuum Cleaner Wiper Wet Dry Remote Control Electric Washing Glass', 'HOBOT 298 Household Windows Cleaner Robot Window Cleaning Vacuum Cleaner Wiper Wet Dry Remote Control Electric Washing Glass');

$LPNumHTML = '<div id="LPNum" class="">' . $LPNum . '</div>';
$AEOrderIdHTML = '<div id="AEOrderId" class="">' . $AEOrderId . '</div>';
$productDescriptionHTML = '<div class="productDescription">' . $productDescriptions[0] . '</div>';
$mailNoHTML2 = '<div id="mailNo2" class="">' . $mailNo . '</div>';

try {
    $mpdf = new \Mpdf\Mpdf(['margin_left' => '0',
        'margin_right' => '0',
        'margin_top' => '0',
        'margin_bottom' => '0',
        'margin_header' => '0',
        'margin_footer' => '0']);
} catch (\Mpdf\MpdfException $e) {
}

try {

    $barRaw = $generator->getBarcode($mailNo, $generator::TYPE_CODE_128);
    $barcode = '<img id="barcodeTarget" src="data:image/png;base64,' . base64_encode($barRaw) . '">';
    $barcode2 = '<img id="barcodeTarget2" src="data:image/png;base64,' . base64_encode($barRaw) . '">';
} catch (\Picqer\Barcode\Exceptions\BarcodeException $e) {
}

$dynamicHTML = $deliveryAddress . $receiverName . $receiverPhone . $mailNoHTML . $otgruzkaDate;

$dynamicHTML2 = '<div id="dynamicHTML2" class="">' . $LPNumHTML . $mailNoHTML2 . $AEOrderIdHTML . $productDescriptionHTML . '</div>';

// Write some HTML code:

//$html = file_get_contents('index.html');
$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
    .w0{
        width: 794px;
    }
    .h0{
        height: 1123px;
    }
    body{
        margin: 0;
    }
    .stickerWrap {
     position: absolute;
        height: 360px;
        top: 0;
        left: 0;
        z-index: 0;
    }
    .page{
         position: relative;
    }
/*    .page *{
         position: absolute;
    }*/
    #barcodeTarget {
        
       
        /*top: 142px;
         left: -62px;*/
         width: 292px;
        height: 77px;
        
        transform: translate(-60px, -220px) rotate(90deg);
       
    }
    #barcodeTarget2 {
        
        width: 177px;
        height: 46px;
       
       transform: translate(-195px,  -125.70px);
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
        top: 76px;
        left: 192px;
        height: 43px;
        rotate: 90;
        
       
    }
    #receiverName{
        
        /*top: 195px;
        left: 18px;*/ 
        top: 76px;
        left: 142px;
        height: 43px;
        font-size: 15px;
        letter-spacing: -1px;
        font-weight: 600;
        rotate: 90;
        
    }
    #receiverPhone{
        
        /*top: 196px;
        left: -9px;*/
       top: 76px;
       left: 115px;
        height: 43px;
        font-size: 13px;
        letter-spacing: 0;
        font-weight: 600;
        rotate: 90;
        
    }
    #otgruzkaDate{
    
        position: absolute;
        /*top: 74px;
        left: -14px;*/
        top: 50px;
        left: 2px;
        font-size: 11px;
        letter-spacing: 0;
        rotate: 90;
        
    }
    #mailNo{
       
        position: absolute;
        /*top: 161px;
        left: -40px;*/
        top: 116px;
        height: 43px;
        font-size: 13px;
        rotate: 90;
       
    }
    
    #dynamicHTML2{
        
        width: 300px;
        overflow: hidden;
       
        font-family: sans-serif;
         float: left;margin: -154.7px 0 0 5px;
    }
    
    #LPNum,.productDescription,#mailNo2 {
        font-size: 12px;
    }
    
    #AEOrderId{
        font-size: 20.0028px;
    }
   

    </style>
</head>
<body>
<img class="stickerWrap" src="template-1.png" alt="">
        <div class="page w0 h0">' . $barcode . '</div>
        <!--<div class="page2 w0 h0"><img src="template-1.png" alt=""></div>-->
       
    
        ' . $dynamicHTML . '


    
<div style="" class="two w0 h0">
   
        <img style="height: 174.70px;" class="" src="template-2.png" alt="">
         ' . $barcode2 . $dynamicHTML2 . '
         
         
         




         
        
</div>


</body>
</html>';


try {
//    $mpdf->WriteHTML('<h1>Hello World</h1><br><p>My first PDF with mPDF</p>');
    $mpdf->WriteHTML($html);
} catch (\Mpdf\MpdfException $e) {
}

// Output a PDF file directly to the browser
try {
//    $mpdf->Output();
    $mpdf->Output('pdf/Hello1.pdf', \Mpdf\Output\Destination::FILE);
//    $pdfCode = $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
} catch (\Mpdf\MpdfException $e) {
    var_dump($e);
}

/*$content = base64_encode($pdfCode);
echo '<img id="" src="data:image/png;base64,' . $content . '">';*/





