<?php

namespace kaykay012\migration\controllers\ecshop;

use Yii;
use yii\console\Controller;

/**
 * 迁移 Goods 表
 *
 * @author kai cui <kaykay012@sina.cn>
 */
class GoodsController extends Controller{
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
        $query->from('ecs_goods');
        $query->where(['is_delete'=>0]);
        $query->orderBy('goods_id');
        $query->offset($pageNum * $this->numPerPage);
        $query->limit($this->numPerPage);
        $rows = $query->all(Yii::$app->get($this->dbID));
        
//        print_r($rows);
//        exit;        
        
        if(!$rows){
            echo "Nothing."; 
        }
        
        $pjson = '{"name":{"name_en":"ggg","name_fr":"","name_de":"","name_es":"","name_ru":"","name_pt":"","name_zh":"","name_it":""},"spu":"","sku":"","long":0,"width":0,"high":0,"volume_weight":"0.00","weight":1.25,"score":0,"status":1,"new_product_from":0,"new_product_to":0,"url_key":"","qty":50,"package_number":0,"min_sales_qty":"","is_in_stock":1,"remark":"","cost_price":50,"price":25,"special_price":0,"special_from":0,"special_to":0,"tier_price":[{"qty":1,"price":25},{"qty":50,"price":20},{"qty":100,"price":15}],"meta_title":{"meta_title_en":"","meta_title_fr":"","meta_title_de":"","meta_title_es":"","meta_title_ru":"","meta_title_pt":"","meta_title_zh":"","meta_title_it":""},"meta_keywords":{"meta_keywords_en":"","meta_keywords_fr":"","meta_keywords_de":"","meta_keywords_es":"","meta_keywords_ru":"","meta_keywords_pt":"","meta_keywords_zh":"","meta_keywords_it":""},"meta_description":{"meta_description_en":"","meta_description_fr":"","meta_description_de":"","meta_description_es":"","meta_description_ru":"","meta_description_pt":"","meta_description_zh":"","meta_description_it":""},"short_description":{"short_description_en":"short desc","short_description_fr":"","short_description_de":"","short_description_es":"","short_description_ru":"","short_description_pt":"","short_description_zh":"","short_description_it":""},"description":{"description_en":"long desc","description_fr":"","description_de":"","description_es":"","description_ru":"","description_pt":"","description_zh":"","description_it":""},"relation_sku":"","buy_also_buy_sku":"","see_also_see_sku":"","attr_group":"default","custom_option":[],"category":["57bea0e3f656f275313bf56e","57b6abfff656f246653bf570","57bea0d3f656f2ec1f3bf56e"],"image":{"gallery":[{"image":"\/8\/vd\/8vdozd1kqd2p0l71530417530.jpg","label":"","sort_order":10,"is_thumbnails":"1","is_detail":"1"}],"main":{"image":"\/2\/h2\/2h2hcjdymng8s2l1530417070.jpg","label":"","sort_order":"","is_thumbnails":"1","is_detail":"1"}}}';
        
        $prow = json_decode($pjson, true);
        
//        print_r($prow);
//        exit;
        
        foreach($rows as $row)
        {
            //是否已经存在
            $model = new \fecshop\models\mongodb\Product();
            if($model->findOne(['ecs_goods_id'=>$row['goods_id']])){
                continue;
            }
            
            $prow['name']['name_en'] = $row['goods_name'];
            $spu = explode('-', $row['goods_sn']);
            $prow['spu'] = !empty($spu) ? $spu[0] : $row['goods_sn'];
            $prow['sku'] = $row['goods_sn'];
            $prow['weight'] = (double) $row['goods_weight'];
            $prow['qty'] = (int) $row['goods_number'];
            $prow['cost_price'] = (double) $row['market_price'];            
            $prow['remark'] = $row['seller_note'];
            $prow['status'] = $row['is_on_sale']==1 ? 1 : 2;
            
            //批发价格
            $prow['tier_price'] = $this->price($row['goods_id']);
            $prow['price'] = !empty($prow['tier_price']) ? (double) $prow['tier_price'][0]['price'] : (double) $row['shop_price'];
            
            $prow['short_description']['short_description_en'] = $row['goods_brief'];
            $prow['description']['description_en'] = empty($row['goods_desc']) ? ' ' : $row['goods_desc'];
            
            //分类
            $prow['category'] = $this->category($row['goods_id'], $row['cat_id']);
            
            //图片 主图
            $prow['image']['main']['image'] = '/' .$row['original_img'];
            //图片 相册
            $prow['image']['gallery'] = $this->gallery($row['goods_id']);
            
            // Wood Turning Tool Kits
            if($row['cat_id'] == 91){                
                //基本属性
                $prow['attr_group'] = 'turningToolKits_group';
                $prow = array_merge($prow, $this->attr($row['goods_id']));
            }
            
            // Pen Boxes
            if($row['cat_id'] == 71){      
                //价格属性[自定义属性]
                $prow['attr_group'] = 'penBag_group';
                $prow = array_merge($prow, $this->attr2($row));
            }
            
            $prow['ecs_goods_id'] = $row['goods_id'];
            
//            print_r($prow);
//            exit;
        
            Yii::$service->product->save($prow, 'catalog/product/index');
            $errors = Yii::$service->helper->errors->get();
            if($errors){
                print_r($errors);
            }
        }
        
    }
    
    // 得到个数
    public function actionCount()
    {
        $query = new \yii\db\Query();
        $query->from('ecs_goods');
        $query->where(['is_delete'=>0]);
        $count = $query->count('*',Yii::$app->get($this->dbID));
        
        echo $count ;
    }
    // 得到页数
    public function actionPagenum()
    {
        $query = new \yii\db\Query();
        $query->from('ecs_goods');
        $query->where(['is_delete'=>0]);
        $count = $query->count('*',Yii::$app->get($this->dbID));
        echo ceil($count / $this->numPerPage);
    }
    
    private function category($goods_id, $cat_id)
    {
        $query = new \yii\db\Query();
        $query2 = clone $query;

        //当前分类
        $goods_cats[] = $cat_id;
        
        //关联的其他分类
        $rows = $query->select(['cat_id'])->where(['goods_id'=>$goods_id])->from('ecs_goods_cat')->all(Yii::$app->get($this->dbID));
        if(!empty($rows)){
            $goods_cats = \yii\helpers\ArrayHelper::getColumn($rows, 'cat_id');
            $goods_cats[] = $cat_id;
        }
        
        //上级分类
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
    
    private function gallery($goods_id)
    {
        $query = new \yii\db\Query();
        $res = $query->select('CONCAT("/",`img_url`) AS `image`, "" AS `label`, convert(`img_desc`,UNSIGNED) AS `sort_order`, "1" AS `is_thumbnails`, "1" AS `is_detail`')
                ->where(['goods_id'=>$goods_id])
                ->from('ecs_goods_gallery')
                ->all(Yii::$app->get($this->dbID));
        
//        print_r($res);
//        exit;
        return $res ? $res : [];
    }
    
    private function price($goods_id)
    {
        $query = new \yii\db\Query();
        $res = $query->select('volume_number as qty, volume_price as price')
                ->where(['goods_id'=>$goods_id])
                ->from('ecs_volume_price')
                ->orderBy('qty asc')
                ->all(Yii::$app->get($this->dbID));
        
        return $res ? $res : [];
    }
    
    private function attr($goods_id)
    {
        $query = new \yii\db\Query();
        $query->select(['a.attr_value', 'b.attr_name']);
        $query->from('ecs_goods_attr as a');
        $query->leftJoin('ecs_attribute as b', 'a.attr_id =  b.attr_id');
        $query->where(['goods_id'=>$goods_id, 'attr_input_type'=>0]);
        $query->indexBy('attr_name');
        $rows = $query->column(Yii::$app->get($this->dbID));
        
        return $rows ?: [];
    }
    
    private function attr2($row)
    {
        $query = new \yii\db\Query();
        $query2 = clone $query;
        
        $query->select(['a.attr_value as attr_value', 'a.attr_value as attr_name']);
        $query->from('ecs_goods_attr as a');
        $query->leftJoin('ecs_attribute as b', 'a.attr_id =  b.attr_id');
        $query->where(['goods_id'=>$row['goods_id'], 'attr_input_type'=>1]);
        $query->indexBy('attr_name');
        $rows = $query->column(Yii::$app->get($this->dbID));
        
        $query2->select(['b.attr_name']);
        $query2->from('ecs_goods_attr as a');
        $query2->leftJoin('ecs_attribute as b', 'a.attr_id =  b.attr_id');
        $query2->where(['goods_id'=>$row['goods_id'], 'attr_input_type'=>1]);
        $attr_name = $query2->scalar(Yii::$app->get($this->dbID));
//        echo $goods_id;
//        print_r($rows);
        
        if(!$rows){
            return [];
        }
        
        foreach($rows as $key => $val){
            $arr[$attr_name] = $val;
            $arr['sku'] = $val;
            $arr['qty'] = 999;
            $arr['price'] = 0;
            $arr['image'] = '/' .$row['original_img'];
            
            $arr2[strtolower($key)] = $arr;
        }
        $newArr['custom_option'] = $arr2;
        
//        print_r($newArr);
//        exit;
        
        return $newArr;
    }
}
