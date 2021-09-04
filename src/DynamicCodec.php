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
        return ($this->_encoder)($value);
    }

    public function decode($value)
    {
        return ($this->_decoder)($value);
    }
}
