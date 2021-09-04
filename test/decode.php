<?php

use DBStorage\Codec\FieldConfig;
use DBStorage\Codec\ProjectConfig;
use DBStorage\Codec\SecretKeyGetterInterface;

require '../vendor/autoload.php';

class SecretKey implements SecretKeyGetterInterface
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getSecretKey($name)
    {
        return $this->key;
    }
}

function newFieldConfig($type) {
    $c = new FieldConfig('pub_key_123');
    $c->codecType = $type;
    $c->init();
    return $c;
}

$secretKey = new SecretKey('pub_key_123');
$projectConfig = new ProjectConfig('usercenter', $secretKey);

$userCollection = $projectConfig->makeCollectionConfig([
    'email'         => newFieldConfig(FieldConfig::SECURITY),
    'mobile'        => newFieldConfig(FieldConfig::SECURITY),
    'address'       => newFieldConfig(FieldConfig::HASH),
    'expand.mobile' => newFieldConfig(FieldConfig::SECURITY),
]);

$projectConfig->setCollection('user', $userCollection);


$data = [
    'email' => 'wSe1O7BPMT0o7vNW34bKFQ==',
    'mobile' => ['yWoBTT8yA8Rj0MlbXJ6MIQ==', 'zY629ZwrxpB2jJp8eVi81g=='],
    'address' => '四川成都',
    'expand' => [
        [
            'name' => 'name_1',
            'mobile' => 'yWoBTT8yA8Rj0MlbXJ6MIQ==',
        ],
        [
            'name' => 'name_2',
            'mobile' => 'zY629ZwrxpB2jJp8eVi81g==',
        ]
    ]
];

$res = $projectConfig->getCodec('user')->decode($data);
print_r($res);
