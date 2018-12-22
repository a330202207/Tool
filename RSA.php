<?php
/**
 * @purpose: RSA加密/解密(默认为base64字符串)
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/23
 * @version: 1.0
 * 如密钥长度为 1024 bit，则加密时数据需小于128字节，加上PKCS1Padding本身的11字节信息，所以明文需小于117字节
 *
 */

/**
 * @notes: 转成标格式
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/23
 * @param $key
 * @return string
 * @version: 1.0
 */
function setPublicKeyFormat($key)
{
    $pem = chunk_split($key, 64, "\n");
    $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
    return $pem;
}

/**
 * @notes: 加密
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/23
 * @param $content
 * @param $publicKey
 * @return string
 * @version: 1.0
 */
function encrypt($content, $publicKey)
{
//    $publicKey = openssl_pkey_get_public($publicKey);//绝对路径读取
    openssl_public_encrypt($content, $dataEncrypt, $publicKey,OPENSSL_PKCS1_PADDING);
    return base64_encode($dataEncrypt);
}

/**
 * @notes: 解密
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/23
 * @param $content
 * @param $privateKey
 * @return bool
 * @version: 1.0
 */
function decrypt($content, $privateKey)
{
//    $privateKey = openssl_pkey_get_private($privateKey);//绝对路径
    $data = base64_decode($content);
    $result = openssl_private_decrypt($data, $dataDecrypt, $privateKey, OPENSSL_PKCS1_PADDING);
    return $result ?? false;
}

/**
 * @notes: 超长加密（由于秘钥有长度限制比如1024，2048约长表示接受的加密数据越多，否则会有超长加密不成功的问题）
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/8/27
 * @param 2018/12/23
 * @param $publicKey
 * @return mixed
 * 117表示长度，加密的时候可以从这开始分割加密（解密的时候从128开始解密即可）
 */
function encrypt128byte($content, $publicKey)
{
//    $publicKey = openssl_pkey_get_public($publicKey);//绝对路径读取
    $result = '';
    $data = str_split($content, 117);

    foreach ($data as $block) {
        openssl_public_encrypt($block, $dataEncrypt, $publicKey,OPENSSL_PKCS1_PADDING);
        $result .= $dataEncrypt;
    }
    return base64_encode($result);
}

/**
 * @notes:  超长私钥解密（128开始截取解密）
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/23
 * @param $content
 * @param $privateKey
 * @return bool|string
 * @version: 1.0
 */
function decrypt128byte($content, $privateKey)
{
//    $privateKey = openssl_pkey_get_private($privateKey);//绝对路径
    $data = base64_decode($content);
    $result = '';
    $data = str_split(($data), 128);
    foreach ($data as $block) {
        openssl_private_decrypt($block, $dataDecrypt, $privateKey, OPENSSL_PKCS1_PADDING);
        $result .= $dataDecrypt;
    }

    return $result ?? false;
}

/**
 * @notes: sha1WithRSA 签名
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/23
 * @param $toSign
 * @param $privateKey
 * @return string
 * @version: 1.0
 */
function getSha1WithRSASign($toSign, $privateKey)
{
    $key = openssl_get_privatekey($privateKey);
    openssl_sign($toSign, $signature, $key, OPENSSL_ALGO_SHA1);
    openssl_free_key($key);
    $sign = base64_encode($signature);
    return $sign;
}

/**
 * @notes: 校验 sha1WithRSA 签名
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/12/23
 * @param $data
 * @param $sign
 * @param $pubKey
 * @return bool
 * @version: 1.0
 */
function verifySha1WithRSASign($data, $sign, $pubKey)
{
    $sign = base64_decode($sign);
    $key = openssl_pkey_get_public($pubKey);
    $result = openssl_verify($data, $sign, $key, OPENSSL_ALGO_SHA1);
    return $result ?? false;
}