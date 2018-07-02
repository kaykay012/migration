<?php
return [
    /**
     * 下面是第三方扩展库包的配置方式
     */
    // 这个是扩展extensions的总开关，true代表打开
    'enable' => true,
    // 各个入口的配置
    'app' => [
        // 1.公用层
        'console' => [
            // 在公用层的开关，设置成false后，公用层的配置将失效
            'enable' => true,
            // 公用层的具体配置下载下面
            'config' => [
                'components' => [
                    // Mysql部分的配置
                    'ecshop' => [
                        'class' => 'yii\db\Connection',
                        'dsn' => 'mysql:host=localhost;dbname=waimao',
                        'username' => 'root',
                        'password' => 'root',
                        'charset' => 'utf8',
                    ],
                ],
                'services' => [
                    'product' => [
                        'class' => 'kaykay012\migration\services\Product',
                    ],
                ],
                'modules' => [
                    'migration' => [
                        'class' => 'kaykay012\migration\Module',
                    ],
                ],
            ]
        ],
    ],
];
