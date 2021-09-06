<?php

namespace DBStorage\Codec;

final class DynamicCodec implements CodecInterface
{
    private $_encoder;
    private $_decoder;

    public function __construct(callable $encoder, callable $decoder)
    {
        $this->_encoder = $encoder;
        $this->_decoder = $decoder;
    }

    public function encode($value)
    {
        return call_user_func($this->_encoder, $value);
    }

    public function decode($value)
    {
        return call_user_func($this->_decoder, $value);
    }
}
