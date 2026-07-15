<?php
namespace XiaoPHP\systools\toolsbox;
class AesTool
{

    public static function encode($input, $key, $type = 'AES-128-ECB', $iv = '')
    {
        $length = self::_getKeyLength($type);
        $key    = self::_sha1prng($key, $length);
        $data   = openssl_encrypt($input, $type, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($data);
    }


    public static function decode($sStr, $sKey, $type = 'AES-128-ECB', $iv = '')
    {
        $length   = self::_getKeyLength($type);
        $sKey     = self::_sha1prng($sKey, $length);
        $decrypted = openssl_decrypt(base64_decode($sStr), $type, $sKey, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }


    private static function _sha1prng($key, $length = 16)
    {
        $result = '';
        $current = $key;
        while (strlen($result) < $length) {
            $current = openssl_digest($current, 'sha1', true);
            $result .= $current;
        }
        return substr($result, 0, $length);
    }


    private static function _getKeyLength($type)
    {
        if (preg_match('/\b(\d+)\b/', $type, $matches)) {
            $bits = (int)$matches[1];
            return $bits / 8;
        }
        return 16; // fallback to 128-bit
    }
}