<?php

use DBStorage\Codec\SecurityCodec;
use DBStorage\Codec\StaticSecurityCodec;

require '../vendor/autoload.php';

$key = 'pubKey';
$value = 'abc';


$codec = new SecurityCodec($key);

$data = $codec->encode($value);

if ($codec->decode($data) !== $value) {
    echo 'failed', PHP_EOL;
    exit(1);
}
if (StaticSecurityCodec::decode($key, $data) !== $value) {
    echo 'static decode failed', PHP_EOL;
    exit(1);
}

echo 'ok', PHP_EOL;
exit(0);
