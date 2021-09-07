<?php

use DBStorage\Codec\FieldConfig;
use DBStorage\Codec\FixedSecretKey;
use DBStorage\Codec\ProjectConfig;

require '../vendor/autoload.php';


$projectConfig = new ProjectConfig('usercenter', new FixedSecretKey('pub_key_123'));

$projectConfig->setCollection('user', $projectConfig->makeCollectionConfig(
    $projectConfig->makeFields([
        'email'     => FieldConfig::SECURITY,
        'mobile'    => 'security',
        'address'   => 'hash',
        'expand.id' => 'security',
        'field_1'   => ['security', '', '', [function ($key, $value) {
            return $value . '|' . $value;
        }, function ($key, $value) {
            return explode('|', $value, 2)[0];
        }]],
    ])
));

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

$userCodec = $projectConfig->getCodec('user');

$res = $userCodec->encode($where);
print_r($res);
