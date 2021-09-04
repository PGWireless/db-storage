<?php

namespace DBStorage\Codec;

interface CodecWithParamsInterface extends CodecInterface
{
    public function encode($value, array $params = []);
    
    public function decode($value, array $params = []);
}
