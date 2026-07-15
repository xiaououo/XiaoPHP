<?php
namespace XiaoPHP\systools\toolsbox;
class RSATool
{
    function encode($string, $pubKey)
    {
        $pubKey =
            "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        $res = openssl_pkey_get_public($pubKey);
        if (!$res) {
            return false;
        }

        openssl_public_encrypt(
            $string,
            $encrypt,
            $res,
            OPENSSL_PKCS1_OAEP_PADDING
        );
        return base64_encode($encrypt);
    }

    function decode($secret, $privateKey)
    {
        $res = openssl_pkey_get_private($privateKey);
        if (!$res) {
            return false;
        }

        openssl_private_decrypt(
            base64_decode($secret),
            $oldData,
            $res,
            OPENSSL_PKCS1_OAEP_PADDING
        );
        return $oldData;
    }
}
