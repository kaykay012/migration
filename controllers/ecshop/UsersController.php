<?php

namespace kaykay012\migration\controllers\ecshop;

use Yii;
use yii\console\Controller;

/**
 * 迁移 users 表
 *
 * @author kai cui <kaykay012@sina.cn>
 */
class UsersController extends Controller
{
    public $numPerPage = 10;
    public $dbID = 'ecshop';
    
    public function actionImport($pageNum = 1)
    {
        $pageNum = $pageNum-1;
        if($pageNum < 0){
            $pageNum = 0;
        }
        
        $query = new \yii\db\Query();
        
        $query2 = clone $query;
        
        $query->select(['*']);
        $query->from('ecs_users');
        $query->orderBy('user_id');
        $query->offset($pageNum * $this->numPerPage);
        $query->limit($this->numPerPage);
        $rows = $query->all(Yii::$app->get($this->dbID));
        
        // 地区
        $regions = $query2->select(['code', 'region_id'])->from('ecs_region')->indexBy('region_id')->column(Yii::$app->get($this->dbID));
//        print_r($regions);         
//        exit;
        
        foreach($rows as $row){
            $model = new \kaykay012\migration\models\Customer();
            $model->scenario = 'custom';            
            
            $model->password_hash = $row['password'];
            $model->email = $row['email'];
            $model->firstname = $row['alias'];
            $model->lastname = $row['lastname'];
            $model->status = 1;
            $model->is_subscribed = 1;
            $model->created_at = $row['reg_time'];
            $model->updated_at = $row['reg_time'];
            $model->birth_date = strtotime($row['birthday']);
            $model->ecs_user_id = $row['user_id'];
            $model->pay_points = $row['pay_points'];
            $model->rank_points = $row['rank_points'];            
            $model->referer = $row['referer'];
            $model->country = $regions[$row['country']];
            $model->ec_salt = $row['ec_salt'];
            $model->is_validated = $row['is_validated'];
                    
            $model->save();
        }
    }
    
    
    // 得到个数
    public function actionCount()
    {
        $query = new \yii\db\Query();
        $query->from('ecs_users');
        $count = $query->count('*',Yii::$app->get($this->dbID));
        
        echo $count ;
    }
    // 得到页数
    public function actionPagenum()
    {
        $query = new \yii\db\Query();
        $query->from('ecs_users');
        $count = $query->count('*',Yii::$app->get($this->dbID));
        echo ceil($count / $this->numPerPage);
    }
}
