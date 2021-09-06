<?php

namespace DBStorage\Codec;


class StaticSecurityCodec implements StaticCodecInterface
{
    const DEFAULT_ALGO = 'AES-128-CBC';
    
    public static function encode($key, $value, $algo = self::DEFAULT_ALGO, $iv = SecurityCodec::DEFAULT_IV)
    {
        if ($iv === SecurityCodec::DEFAULT_IV) {
            $iv = base64_decode($iv);
        }

        return openssl_encrypt($value, $algo, $key, 0, $iv);
    }

    public static function decode($key, $value, $algo = self::DEFAULT_ALGO, $iv = SecurityCodec::DEFAULT_IV)
    {
        if ($iv === SecurityCodec::DEFAULT_IV) {
            $iv = base64_decode($iv);
        }

        return openssl_decrypt($value, $algo, $key, 0, $iv);
    }
}
