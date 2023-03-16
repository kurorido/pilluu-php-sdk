<?php

namespace Pillu;

class PilluCheckOrder
{
    protected string $merchantid;
    protected string $public_key;
    protected string $sha2key;
    protected bool $testing = false;

    public function __construct(
        string $merchantid,
        string $public_key,
        string $sha2key,
        bool $testing = false,
    ) {
        $this->merchantid = $merchantid;
        $this->public_key = $public_key;
        $this->sha2key = $sha2key;
        $this->testing = $testing;
    }

    protected function getEndpoint()
    {
        return $this->testing ?
            'https://testtrade.sunpaykly.com.tw/v1/query/checkorder' :
            'https://trade.sunpaykly.com.tw/v1/query/checkorder';
    }

    /**
     * 回傳的資料格式不需另外解碼，內容意義可參考紅陽文件，會包含 ['code', 'msg', 'result] 三個欄位
     * @param string $tradetime 
     * @param string $price 
     * @param string $orderNo 
     * @return mixed 
     */
    public function doCheck(string $tradetime, string $price, string $orderNo)
    {
        // head
        $head = [];
        $head['merchantid']       = $this->merchantid; //商戶編號
        $head['tradetime']       = $tradetime;   //交易發起時間，格式為fffssmmHHddMMYYYY
        ksort($head); //進行升序
        
        // body
        $body = [];
        $body['merchantid']  = $this->merchantid; //商戶編號
        $body['tradetime']   = $tradetime;  //交易發起時間，格式為fffssmmHHddMMYYYY
        $body['amount']      = $price;       //訂單消費金額：正整數，不可帶入小數點及小數或任何符號
        $body['tradeid'] = $orderNo;        //商戶訂單號
        
        // 進行升序
        ksort($body);

        $params['body'] = $body;
        $params['head'] = $head;
        $json = json_encode($params, 320); //不對網址和中文進行編碼

        $url_encode = urlencode($json);                         //進行urlencode
        $rsamsg = PilluUtils::rsa_encrypt($url_encode, $this->public_key); //進行RSA分段加密
        $content = hash("sha256", $url_encode . $this->sha2key);

        $payload = [
            'merchantid' => $this->merchantid,
            'tradetime' => $tradetime,
            'rsamsg' => $rsamsg,
            'content' => $content
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getEndpoint());
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($ch);
        $response = json_decode($output);

        return $response;
    }
}