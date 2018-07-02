<?php

namespace kaykay012\migration\helper;

use Yii; 

/**
 * Description of CUser
 *
 * @author kai cui <kaykay012@sina.cn>
 */
class CUser extends \fec\helpers\CUser{
    
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

}
