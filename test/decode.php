<?php

use DBStorage\Codec\FixedSecretKey;
use DBStorage\Codec\ProjectConfig;

require '../vendor/autoload.php';


$projectConfig = new ProjectConfig('usercenter', new FixedSecretKey('pub_key_123'));

$projectConfig->setCollection('user', $projectConfig->makeCollectionConfig(
    $projectConfig->makeFields([
        'email'         => 2,
        'mobile'        => 2,
        'address'       => 1,
        'expand.mobile' => 2,
    ])
));

$data = [
    'email' => 'mKLACDjckMcc2U12tXMtJA==',
    'mobile' => ['WpktXfvZiIOKSnq/TyR3bA==', 'mgs691fybGiD5ReUxl+rtw=='],
    'address' => '四川成都',
    'expand' => [
        [
            'name' => 'name_1',
            'mobile' => 'WpktXfvZiIOKSnq/TyR3bA==',
        ],
        [
            'name' => 'name_2',
            'mobile' => 'mgs691fybGiD5ReUxl+rtw==',
        ]
    ]
];

$res = $projectConfig->getCodec('user')->decode($data);
print_r($res);
