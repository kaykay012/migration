ecshop 数据迁移到 fecshop
================
涉及到的表:
users, 
category, 
goods, goods_cat, goods_galery, volume_price
orders, order_info,

安装
-------

```
composer require --prefer-dist kaykay012/migration 
```

or 在根目录的`composer.json`中添加

```
"kaykay012/migration": "*"
```

然后执行

```
composer update
```

配置
-----

1.配置文件复制

将`vendor\kaykay012\migration\config\fecshop_migration.php` 复制到
`@common\config\fecshop_third_extensions\fecshop_migration.php`(需要创建该文件)

该文件是扩展的配置文件，通过上面的操作，加入到fecshop的插件配置中.

2.可能会遇到的错误
```php
Exception 'yii\base\UnknownPropertyException' with message 'Getting unknown property: yii\console\Application::user'
in F:\phpStudy\PHPTutorial\WWW\fecshop\vendor\yiisoft\yii2\base\Component.php:154
```
解决办法:
修改文件 `vendor\fancyecommerce\fec\helpers\CUser.php`
```php
    # 3.得到当前用户的id
    public static function getCurrentUserId() {
        if (!isset(Yii::$app->user)) {
            return '';
        }
        if ($identity = Yii::$app->user->identity) {
            if (isset($identity['id']) && !empty($identity['id'])) {
                return $identity['id'];
            }
        }
        return '';
    }
```