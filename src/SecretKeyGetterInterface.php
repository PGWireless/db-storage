<?php

namespace DBStorage\Codec;

interface SecretKeyGetterInterface
{
    public function getSecretKey($name);
}
