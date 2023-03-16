<?php

namespace Pillu;

use Exception;

class PilluCallback
{
    protected string $merchantid;
    protected string $public_key;
    protected string $sha2key;

    public function __construct(
        string $merchantid,
        string $public_key,
        string $sha2key,
    ) {
        $this->merchantid = $merchantid;
        $this->public_key = $public_key;
        $this->sha2key = $sha2key;
    }

    /**
     * 解析皮路回傳的 payload
     * @param mixed $payload 
     * @return mixed 
     * @throws Exception 
     */
    public function check($payload)
    {
        $rsamg = $payload->rsamsg;
        $decoded = PilluUtils::rsa_decrypt($rsamg, $this->public_key);
        
        // 比較 Hash
        $content = hash("sha256", $decoded . $this->sha2key);
        if ($payload->content != $content) {
            throw new \Exception('SHA256 Not Match');
        }

        $data = json_decode(urldecode($decoded));

        // 檢查商戶編號
        $merchantid = $data->body->merchantid;

        if ($merchantid != $this->merchantid) {
            throw new \Exception('Merchant ID Not Match');
        }

        return $data;
    }
}