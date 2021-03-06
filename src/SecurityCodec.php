<?php

namespace DBStorage\Codec;

class SecurityCodec implements CodecInterface
{
    const DEFAULT_IV = 'IaJnDsgUunjASLN5';

    private $key;
    private $iv;

    public function __construct($key, $iv = self::DEFAULT_IV)
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    public function encode($value)
    {
        return openssl_encrypt($value, 'AES-128-CBC', $this->key, 0, $this->iv);
    }

    public function decode($value)
    {
        return openssl_decrypt($value, 'AES-128-CBC', $this->key, 0, $this->iv);
    }
}
