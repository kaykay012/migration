<?php

namespace kaykay012\migration\controllers\ecshop;

use Yii;
use yii\console\Controller;

/**
 * 迁移 category 表 [顶级分类]
 *
 * @author kai cui <kaykay012@sina.cn>
 */
class CategoryController extends Controller
{
    public $numPerPage = 10;
    public $dbID = 'ecshop';
    
    /**
     * @param type $pageNum 第几页
     * @param type $parentId [顶级分类 $parentId=0] [二级分类 $parentId=2]
     */
    public function actionImport($pageNum = 1, $parentId=0)
    {
        $pageNum = $pageNum-1;
        if($pageNum < 0){
            $pageNum = 0;
        }
        
        $query = new \yii\db\Query();
        
        $query->select(['*']);
        $query->from('ecs_category');
        $query->orderBy('cat_id');
        
        if($parentId>0){
            $query->where(['>', 'parent_id', 0]);
        }else{
            $query->where(['parent_id'=>0]);
        }

//        $query->limit(1);
        $rows = $query->all(Yii::$app->get($this->dbID));
        
//        print_r($rows);
//        exit;
        
        $categories = '{"name":{"name_en":"ccc","name_fr":"","name_de":"","name_es":"","name_ru":"","name_pt":"","name_zh":"","name_it":""},"status":1,"menu_show":1,"url_key":"","filter_product_attr_selected":"","filter_product_attr_unselected":"","description":{"description_en":"","description_fr":"","description_de":"","description_es":"","description_ru":"","description_pt":"","description_zh":"","description_it":""},"menu_custom":{"menu_custom_en":"","menu_custom_fr":"","menu_custom_de":"","menu_custom_es":"","menu_custom_ru":"","menu_custom_pt":"","menu_custom_zh":"","menu_custom_it":""},"title":{"title_en":"","title_fr":"","title_de":"","title_es":"","title_ru":"","title_pt":"","title_zh":"","title_it":""},"meta_keywords":{"meta_keywords_en":"","meta_keywords_fr":"","meta_keywords_de":"","meta_keywords_es":"","meta_keywords_ru":"","meta_keywords_pt":"","meta_keywords_zh":"","meta_keywords_it":""},"meta_description":{"meta_description_en":"","meta_description_fr":"","meta_description_de":"","meta_description_es":"","meta_description_ru":"","meta_description_pt":"","meta_description_zh":"","meta_description_it":""},"parent_id":"0"}';
        $categories_arr = json_decode($categories, true);
        
//        print_r($categories_arr);         
//        exit;
        
        foreach($rows as $cate)
        {       
            //是否已经存在
            $model = new \fecshop\models\mongodb\Category();
            if($model->findOne(['ecs_cat_id'=>$cate['cat_id']])){
                continue;
            }
            
            $categories_arr['name']['name_en'] = $cate['cat_name'];
            $categories_arr['meta_keywords']['meta_keywords_en'] = $cate['keywords'];
            $categories_arr['description']['description_en'] = $cate['cat_desc'];
            $categories_arr['ecs_cat_id'] = $cate['cat_id'];
//                   print_r($cate); 
//                   exit;
            $originUrlKey = 'catalog/category/index';
            if($cate['parent_id']>0){
                $parentIdArr = $this->category_id([$cate['parent_id']]);
                $categories_arr['parent_id'] = $parentIdArr[0];
            }
//            print_r($categories_arr);
//            exit;
            
            Yii::$service->category->save($categories_arr, $originUrlKey);
        }
    }
    
    // 得到个数
    public function actionCount($parentId=0)
    {
        $query = new \yii\db\Query();
        $query->from('ecs_category');
        if($parentId>0){
            $query->where(['>', 'parent_id', 0]);
        }else{
            $query->where(['parent_id'=>0]);
        }
        $count = $query->count('*',Yii::$app->get($this->dbID));
        
        echo $count ;
    }
    // 得到页数
    public function actionPagenum($parentId=0)
    {
        $query = new \yii\db\Query();
        $query->from('ecs_category');
        if($parentId>0){
            $query->where(['>', 'parent_id', 0]);
        }else{
            $query->where(['parent_id'=>0]);
        }
        $count = $query->count('*',Yii::$app->get($this->dbID));
        echo ceil($count / $this->numPerPage);
    }
    
    private function category($goods_id, $cat_id)
    {
        $query = new \yii\db\Query();
        $query2 = clone $query;
        
        $goods_cats = $query->select(['cat_id'])->where(['goods_id'=>$goods_id])->from('ecs_goods_cat')->all(Yii::$app->get($this->dbID));
//        var_dump($goods_cats);
//        exit;
        if(!empty($goods_cats)){
            $goods_cats = \yii\helpers\ArrayHelper::getColumn($goods_cats, 'cat_id');
            $goods_cats[] = $cat_id;
            return $this->category_id($goods_cats);
        }
        
        //上级分类
        $goods_cats[] = $cat_id;
        $pid = $query2->select(['parent_id'])->where(['cat_id'=>$cat_id])->from('ecs_category')->scalar(Yii::$app->get($this->dbID));
//        var_dump($pid);
//        exit;
        if($pid>0){
            $goods_cats[] = $pid;
        }
//        print_r($goods_cats);exit;
        return $this->category_id($goods_cats);
    }
    
    private function category_id($cat_ids)
    {
        $model = new \fecshop\models\mongodb\Category();
        
        $res = $model->find()->select(['_id'])->where(['in', 'ecs_cat_id', $cat_ids])->asArray()->all();
//        print_r($res);
//        exit;
        $res = \yii\helpers\ArrayHelper::toArray($res);
        $res = \yii\helpers\ArrayHelper::getColumn($res, '_id');
        $res = \yii\helpers\ArrayHelper::getColumn($res, 'oid');
        
//        print_r($res);
//        exit;
        return $res ? $res : [];
    }
}
