<?php

namespace DBStorage\Codec;

interface CodecInterface
{
    public function encode($value);

    public function decode($value);
}
