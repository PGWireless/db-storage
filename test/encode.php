<?php

use DBStorage\Codec\FieldConfig;
use DBStorage\Codec\ProjectConfig;
use DBStorage\Codec\MongoCollectionConfig;
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
    'email'     => newFieldConfig(FieldConfig::SECURITY),
    'mobile'    => newFieldConfig(FieldConfig::SECURITY),
    'address'   => newFieldConfig(FieldConfig::HASH),
    'expand.id' => newFieldConfig(FieldConfig::SECURITY),
]);

$projectConfig->setCollection('user', $userCollection);

$where = [
    'id'      => 123,
    'email'   => 'abc@qq.com',
    'mobile'  => '',
    'address' => '',
    '$and'    => [
        ['email'  => 'abc@qq.com'],
        ['mobile' => ['$in' => ['12', '34', '56']]],
        ['name'   => 'Leon'],
        ['$or'    => [
            ['nickname' => '/ai/i'],
            ['age'      => ['$gt' => 5]],
            ['address'  => '四川成都'],
            ['mobile'   => ['$eq' => '13012345678']],
            ['expand.id' => ['$in' => [2, 3]]],
        ]],
    ],
];

$res = $projectConfig->getCodec('user')->encode($where);
print_r($res);
