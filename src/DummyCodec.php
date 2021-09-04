<?php

namespace DBStorage\Codec;

final class DummyCodec implements CodecInterface
{
    private static $_instance;

    public static function instance()
    {
        if (static::$_instance === null) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }
    
    public function encode($value)
    {
        return $value;
    }

    public function decode($value)
    {
        return $value;
    }
}
