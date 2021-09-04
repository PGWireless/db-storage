<?php

namespace DBStorage\Codec;

class FixedSecretKey implements SecretKeyGetterInterface
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /** @inheritDoc */
    public function getSecretKey($name)
    {
        return $this->key;
    }
}
