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

$deliveryAddress = '<div id="deliveryAddress" class="">309500, Старый Оскол, Белгородская область, дом 13 подъезд 2 квартира 46 северный микрорайон</div>';
$receiverName = '<div id="receiverName" class="">Каюмов Руслан Наилевич</div>';
$receiverPhone = '<div id="receiverPhone" class="">9227829596,</div>';
$otgruzkaDate = '<div id="otgruzkaDate" class="">2019-08-11</div>';
$mailNoHTML = '<div id="mailNo" class="">' . $mailNo . '</div>';

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

    $barcode = '<img id="barcodeTarget" src="data:image/png;base64,' . base64_encode($generator->getBarcode($mailNo, $generator::TYPE_CODE_128)) . '">';
} catch (\Picqer\Barcode\Exceptions\BarcodeException $e) {
}

$dynamicHTML = $deliveryAddress . $receiverName . $receiverPhone . $mailNoHTML . $otgruzkaDate;

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
        
       position:absolute;
        /*top: 142px;
         left: -62px;*/
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
   

    </style>
</head>
<body>
<img class="stickerWrap" src="template-1.png" alt="">
        <div class="page w0 h0">' . $barcode . '</div>
        <!--<div class="page2 w0 h0"><img src="template-1.png" alt=""></div>-->
       
    
        ' . $dynamicHTML . '


    



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





