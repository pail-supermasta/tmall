<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30.08.2019
 * Time: 16:19
 */

namespace Avaks\Cainiao;


class Cainiao
{

    public $linkUrl;
    private $appSecret; // The key corresponding to APPKEY
    public $cpCode;

    public function __construct()
    {
//        $this->linkUrl = 'https://prelink.cainiao.com/gateway/link.do';
        $this->appSecret = 'DKXWrit6e16htW008PFwW7tN5Y8758qq';
        $this->cpCode = 'UTV0a1NLakt5dE9DdzZOdEt1elhnblRnMURQaExvS0w4RVZEVHMyM2o2eTRqUjdiOEdxalpTVjhRN0ZBQldVZA==';

    }

    /**
     * @param $postData
     * @return mixed
     */
    private function curlCall($postData)
    {


//        $linkUrl = 'https://prelink.cainiao.com/gateway/link.do';
        $linkUrl = 'https://link.cainiao.com/gateway/link.do';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $linkUrl);

        // For debugging

        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

//        $post_data = 'msg_type=' . $msgType . '&logistic_provider_id=' . urlencode($cpCode) . '&to_code=' . $toCode . '&data_digest=' . urlencode($digest) . '&logistics_interface=' . $content;

        echo "Post body is: \n" . $postData . "\n";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_POST, 1);

        echo "Start to run...\n";
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        print_r("\n" . $info['request_header']);

        curl_close($ch);

        //echo "Finished, result data is: \n" . json_encode($output);
//        var_dump($output);
        return $output;
    }


    public function CAINIAO_GLOBAL_OPEN_DISTRIBUTION_CONSIGN($content)
    {
        $digest = base64_encode(md5($content . $this->appSecret, true)); //Generate signature

        $toCode = ''; //The target TOCODE is called, some interfaces TOCODE can be filled out

        $msgType = 'CAINIAO_GLOBAL_OPEN_DISTRIBUTION_CONSIGN';

        $post_data = 'msg_type=' . $msgType . '&logistic_provider_id=' . urlencode($this->cpCode) . '&to_code=' . $toCode . '&data_digest=' . urlencode($digest) . '&logistics_interface=' . $content;

        $res = $this->curlCall($post_data);
        return $res;
    }


    public function MAILNO_QUERY_SERVICE($content)
    {
        $digest = base64_encode(md5($content . $this->appSecret, true)); //Generate signature

        $toCode = ''; //The target TOCODE is called, some interfaces TOCODE can be filled out

        $msgType = 'MAILNO_QUERY_SERVICE';

        $post_data = 'msg_type=' . $msgType . '&logistic_provider_id=' . urlencode($this->cpCode) . '&to_code=' . $toCode . '&data_digest=' . urlencode($digest) . '&logistics_interface=' . $content;

        $res = $this->curlCall($post_data);
        return $res;

    }


}


