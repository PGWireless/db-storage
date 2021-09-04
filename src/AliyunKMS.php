<?php

namespace DBStorage\Codec;

class AliyunKMSService implements SecretKeyGetterInterface
{
    public function getSecretKey($name)
    {
        return $name;
    }
}
