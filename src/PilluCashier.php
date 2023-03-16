<?php

namespace Pillu;

class PilluCashier
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
            'https://testtrade.sunpaykly.com.tw/v1/cashier' :
            'https://trade.sunpaykly.com.tw/v1/cashier';
    }

    /**
     * 傳送交易，回傳可提交的 HTML Form
     * 如果 Submit 為 true，會 display none 表單，並且自動 JS 送出
     * 如果 Submit 為 false，會顯示表單，方便 debug
     * @return string $html 
     */
    public function doCheckout(PilluOrder $order, $submit = true)
    {
        $d = new \DateTime();
        $fff = floor($d->format('u') / 1000);

        $tradetime = $order->tradetime ? : $fff . date("s") . date("i") . date("H") . date("d") . date("m") . date("Y");
        $merchantid = $order->merchantid ? : $this->merchantid;

        $head = [];
        $head['merchantid']       = $merchantid; //商戶編號
        $head['tradetime']       = $tradetime;   //交易發起時間，格式為fffssmmHHddMMYYYY
        ksort($head); //進行升序
        
        // body
        $body = [];
        $body['merchantid']  = $merchantid; //商戶編號
        $body['tradetime']   = $tradetime;  //交易發起時間，格式為fffssmmHHddMMYYYY
        $body['amount']      = $order->amount;       //訂單消費金額：正整數，不可帶入小數點及小數或任何符號
        $body['installment'] = $order->installment;         //消費者選擇的分期數：0：不分期1：1 期3：3 期請參照「取得可分期期數」所回傳的值「number」填入
        $body['fee']         = $order->fee;         //分期手續費%數請參照「取得可分期期數」所回傳的值「fee」填入如不分期，請填入 0
        $body['totalamount'] = $order->amount;       //總金額：正整數，不可帶入小數點及小數或任何符號
        $body['tradeid'] = $order->tradeid;        //商戶訂單號
        $body['memo'] = $order->memo;             //備註
        
        //進行升序
        ksort($body);
        
        //將head和body串成json
        $params['body'] = $body;
        $params['head'] = $head;
        $json = json_encode($params, 320); //不對網址和中文進行編碼
        
        $url_encode = urlencode($json);                         //進行urlencode
        $rsamsg = PilluUtils::rsa_encrypt($url_encode, $this->public_key); //進行RSA分段加密
        $content = hash("sha256", $url_encode . $this->sha2key);

        $url = $this->getEndpoint();

        $html = "
            <html>
            <head>
                <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
            </head>
            <body>
                <form id=\"form\" action=\"{$url}\" method=\"post\">
                    <table style=\"border:3px #cccccc solid; width:80%\" cellpadding=\"10\" border='1'>
                        <thead>
                            <tr>
                                <th>參數名稱</th>
                                <th>說明</th>
                                <th>參數值</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>merchantid</td>
                                <td>商戶編號</td>
                                <td><input style=\"width:100%\" type=\"text\" name=\"merchantid\" value=\"{$merchantid}\"></td>
                            </tr>
                            <tr>
                                <td>tradetime</td>
                                <td>交易時間</td>
                                <td><input style=\"width:100%\" type=\"text\" name=\"tradetime\" value=\"$tradetime\"></td>
                            </tr>
                            <tr>
                                <td>rsamsg</td>
                                <td>RSA 加密内容</td>
                                <td><input style=\"width:100%\" type=\"text\" name=\"rsamsg\" value=\"$rsamsg\"></td>
                            </tr>
                            <tr>
                                <td>content</td>
                                <td>交易檢查碼</td>
                                <td><input style=\"width:100%\" type=\"text\" name=\"content\" value=\"$content\"></td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <button type=\"submit\">送出</button>
                </form>
        ";

        if ($submit) {
            $html .= "
                <style>
                    #form { display: none; }
                </style>
                <script>
                    document.getElementById('form').submit();
                </script>
            ";
        }

        $html .= "
            </body>
            </html>
        ";

        return $html;
    }
}