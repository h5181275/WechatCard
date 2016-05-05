<?php

require(__DIR__ . '/bootstrap.php');
$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',    
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Uv_a_RGIEUHcAYVbaQYpIyCghOsJoIrt',
            // 'enableCookieValidation' => true,
            'enableCsrfValidation' => false,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        // 'redis' => [
        //     'class' => 'yii\redis\Connection',
        //     'hostname'=>'47da6ce583da45f1.m.cnhza.kvstore.aliyuncs.com',
        //     'port'=>'6379',
        //     'database'=>0,
        //     'password'=>'47da6ce583da45f1:Trioisobar2015'
        // ],
        // 'cache' => [
        //     'class' => 'yii\redis\Cache',
        // ],
        // 'session' => [
        //     'class' => 'yii\redis\Session',
        // ],
        
        
        
        'user' => [
            'identityClass' => 'app\models\User',
            // 'identityClass' => 'app\models\Txweibo',
            // 'identityClass' => 'app\models\Sinaweibo',
            
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
            'transport' => [
                                'class' => 'Swift_SmtpTransport',
                                'host' => '10.76.208.7',
                                'username' => '',
                                'password' => '',
                                'port' => '25',
                                'encryption' => 'tls',
                            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            //'suffix'=>'.html',
            'rules' => [
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ]
    ],
    'modules' => [
        'system' => [
            'class' => 'app\modules\system\Module',            
        ],
    ],
    'params' => $params,
    'language' => 'zh-CN',
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug']['class'] = 'yii\debug\Module';
    $config['modules']['debug']['allowedIPs'] = ['180.168.26.106','10.76.212.8', '127.0.0.1', '::1'];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
