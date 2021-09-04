<?php

namespace DBStorage\Codec;

class StaticSecurityCodec implements StaticCodecInterface
{
    const DEFAULT_ALGO = 'AES-128-CBC';
    const DEFAULT_IV   = 'IaJnDsgUunjASLN5';
    
    public static function encode($key, $value, $algo = self::DEFAULT_ALGO, $iv = self::DEFAULT_IV)
    {
        return openssl_encrypt($value, $algo, $key, 0, $iv);
    }

    public static function decode($key, $value, $algo = self::DEFAULT_ALGO, $iv = self::DEFAULT_IV)
    {
        return openssl_decrypt($value, $algo, $key, 0, $iv);
    }
}
