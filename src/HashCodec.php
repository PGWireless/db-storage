<?php

namespace DBStorage\Codec;

class HashCodec implements CodecInterface
{
    public $algo;
    
    private $_key;

    public function __construct($key, $algo = 'sha256')
    {
        $this->_key = $key;
        $this->algo = $algo;
    }
    
    public function encode($value)
    {
        return hash_hmac($this->algo, $value, $this->_key);
    }

    public function decode($value)
    {
        return $value;
    }
}
