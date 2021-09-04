<?php

namespace DBStorage\Codec;

interface StaticCodecInterface
{
    public static function encode($key, $value);

    public static function decode($key, $value);
}
