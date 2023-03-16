<?php

namespace Pillu;

class PilluUtils
{
    //RSA公鑰加密(進行授權時)
    public static function rsa_encrypt($str, $public_key)
    {
        $data_array = str_split($str, 117);
        $res = "";
        foreach ($data_array as $value) {
            $encrypted = null;
            openssl_public_encrypt($value, $encrypted, $public_key); //公鑰加密
            $res .= $encrypted;
        }
        return base64_encode($res);
    }

    //RSA公鑰解密(回調時)
    public static function rsa_decrypt($rsamsg, $public_key)
    {
        $rsamsg = str_replace(array('-', '_'), array('+', '/'), $rsamsg);  //需要轉一些符號才能解
        $data = str_split(base64_decode($rsamsg), 128);     //先編碼再切
        $decrypted_data = '';

        foreach ($data as $key) {
            openssl_public_decrypt($key, $decrypted, $public_key);
            $decrypted_data .= $decrypted;
        }
        return $decrypted_data;
    }

    //RSA私鑰加密
    public static function rsa_pri_encrypt($str, $private_key)
    {
        $data_array = str_split($str, 117);
        $res = "";
        foreach ($data_array as $value) {
            $encrypted = null;
            openssl_private_encrypt($value, $encrypted, $private_key);
            $res .= $encrypted;
        }
        return base64_encode($res);
    }

    //RSA私鑰解密
    public static function rsa_pri_decrypt($rsamsg, $private_key)
    {
        $rsamsg = str_replace(array('-', '_'), array('+', '/'), $rsamsg);  //需要轉一些符號才能解
        $data = str_split(base64_decode($rsamsg), 128);     //先編碼再切
        $decrypted_data = '';

        foreach ($data as $key) {
            openssl_private_decrypt($key, $decrypted, $private_key);
            $decrypted_data .= $decrypted;
        }
        return $decrypted_data;
    }
}