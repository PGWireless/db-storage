<?php

namespace DBStorage\Codec;

class StaticHashCodec implements StaticCodecInterface
{
    const DEFAULT_ALGO = 'sha256';
    
    public static function encode($key, $value, $algo = self::DEFAULT_ALGO)
    {
        return hash_hmac($algo, $value, $key);
    }

    public static function decode($key, $value)
    {
        return $value;
    }
}
