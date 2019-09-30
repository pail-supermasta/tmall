<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.08.2019
 * Time: 13:07
 */

namespace Avaks\MS;


class CurlMoiSklad
{


    public static function curlMS($link, $data = false, $type = false, $display = false)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_URL, 'https://online.moysklad.ru/api/remap/1.1/' . $link);
        curl_setopt($curl, CURLOPT_USERPWD, 'робот_next@техтрэнд:Next0913');


        if ($type == 'put') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        } else {
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        }


        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            if ($display == true) {
                echo "Post body is: \n" . $data . "\n";
            }

        }

        $headers = array(
            0 => "Content-Type: application/json",
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);


        $result = curl_exec($curl);
        $info = curl_getinfo($curl);

        if ($display == true) {
            print_r("\n" . $info['request_header']);
        }



        $curl_errno = curl_errno($curl);
        curl_close($curl);

        if ($curl_errno == 0) {
            return $result;
        } else {
            return $curl_errno;
        }

    }
}

