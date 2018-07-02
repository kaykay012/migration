<?php

namespace kaykay012\migration\models;

use Yii;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string $password_hash 密码
 * @property string $password_reset_token 密码token
 * @property string $email 邮箱
 * @property string $firstname
 * @property string $lastname
 * @property int $is_subscribed 1代表订阅，2代表不订阅邮件
 * @property string $auth_key
 * @property int $status 状态
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property string $password 密码
 * @property string $access_token
 * @property string $birth_date 出生日期
 * @property int $favorite_product_count 用户收藏的产品的总数
 * @property string $type 默认为default，如果是第三方登录，譬如google账号登录注册，那么这里的值为google
 * @property int $access_token_created_at 创建token的时间
 * @property int $allowance 限制次数访问
 * @property int $allowance_updated_at
 * @property int $ecs_user_id ecshop users user_id
 * @property string $ec_salt ecshop salt
 * @property int $pay_points 支付积分
 * @property int $rank_points 等级积分
 * @property string $country 所属国家
 * @property string $referer 注册来源
 */
class Customer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_subscribed', 'status', 'created_at', 'updated_at', 'favorite_product_count', 'access_token_created_at', 'allowance', 'allowance_updated_at', 'ecs_user_id', 'pay_points', 'rank_points'], 'integer'],
            [['birth_date'], 'safe'],
            [['password_hash', 'referer'], 'string', 'max' => 80],
            [['password_reset_token', 'email', 'auth_key', 'access_token'], 'string', 'max' => 60],
            [['firstname', 'lastname', 'country'], 'string', 'max' => 100],
            [['password'], 'string', 'max' => 50],
            [['type'], 'string', 'max' => 35],
            [['ec_salt'], 'string', 'max' => 10],
            [['access_token'], 'unique'],
            
            ['ecs_user_id', 'unique', 'on'=>'custom'],
            ['email', 'unique', 'on'=>'custom'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'password_hash' => '密码',
            'password_reset_token' => '密码token',
            'email' => '邮箱',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'is_subscribed' => '1代表订阅，2代表不订阅邮件',
            'auth_key' => 'Auth Key',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'password' => '密码',
            'access_token' => 'Access Token',
            'birth_date' => '出生日期',
            'favorite_product_count' => '用户收藏的产品的总数',
            'type' => '默认为default，如果是第三方登录，譬如google账号登录注册，那么这里的值为google',
            'access_token_created_at' => '创建token的时间',
            'allowance' => '限制次数访问',
            'allowance_updated_at' => 'Allowance Updated At',
            'ecs_user_id' => 'ecshop users user_id',
            'ec_salt' => 'ecshop salt',
            'pay_points' => '支付积分',
            'rank_points' => '等级积分',
            'country' => '所属国家',
            'referer' => '注册来源',
        ];
    }
}
