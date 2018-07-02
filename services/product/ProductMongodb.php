<?php

namespace kaykay012\migration\services\product;

use Yii;

/**
 * Description of ProductMongodb
 *
 * @author kai cui <kaykay012@sina.cn>
 */
class ProductMongodb extends \fecshop\services\product\ProductMongodb{
    
    /**
     * @property $one|array , 产品数据数组
     * @property $originUrlKey|string , 产品的原来的url key ，也就是在前端，分类的自定义url。
     * 保存产品（插入和更新），以及保存产品的自定义url
     * 如果提交的数据中定义了自定义url，则按照自定义url保存到urlkey中，如果没有自定义urlkey，则会使用name进行生成。
     */
    public function save($one, $originUrlKey = 'catalog/product/index')
    {
        if (!$this->initSave($one)) {
            return false;
        }
        $one['min_sales_qty'] = (int)$one['min_sales_qty'];
        $currentDateTime = \fec\helpers\CDate::getCurrentDateTime();
        $primaryVal = isset($one[$this->getPrimaryKey()]) ? $one[$this->getPrimaryKey()] : '';
        if ($primaryVal) {
            $model = $this->_productModel->findOne($primaryVal);
            if (!$model) {
                Yii::$service->helper->errors->add('Product '.$this->getPrimaryKey().' is not exist');

                return false;
            }

            //验证sku 是否重复
            $product_one = $this->_productModel->find()->asArray()->where([
                '<>', $this->getPrimaryKey(), (new \MongoDB\BSON\ObjectId($primaryVal)),
            ])->andWhere([
                'sku' => $one['sku'],
            ])->one();
            if ($product_one['sku']) {
                Yii::$service->helper->errors->add('Product Sku 已经存在，请使用其他的sku');

                return false;
            }
        } else {
            $model = new $this->_productModelName();
            $model->created_at = time();
            $model->created_user_id = \kaykay012\migration\helper\CUser::getCurrentUserId();
            $model->created_user_id = '';
            $primaryVal = new \MongoDB\BSON\ObjectId();
            $model->{$this->getPrimaryKey()} = $primaryVal;
            //验证sku 是否重复
            $product_one = $this->_productModel->find()->asArray()->where([
                'sku' => $one['sku'],
            ])->one();
            if ($product_one['sku']) {
                Yii::$service->helper->errors->add('Product Sku 已经存在，请使用其他的sku');

                return false;
            }
        }
        $model->updated_at = time();
        /*
         * 计算出来产品的最终价格。
         */
        $one['final_price'] = Yii::$service->product->price->getFinalPrice($one['price'], $one['special_price'], $one['special_from'], $one['special_to']);
        $one['score'] = (int) $one['score'];
        unset($one['_id']);
        /**
         * 保存产品
         */
        $saveStatus = Yii::$service->helper->ar->save($model, $one);
        /**
         * 如果 $one['custom_option'] 不为空，则计算出来库存总数，填写到qty
         */
        if (is_array($one['custom_option']) && !empty($one['custom_option'])) {
            $custom_option_qty = 0;
            foreach ($one['custom_option'] as $co_one) {
                $custom_option_qty += $co_one['qty'];
            }
            $model->qty = $custom_option_qty;
        }
        $saveStatus = Yii::$service->helper->ar->save($model, $one);
        /*
         * 自定义url部分
         */
        if ($originUrlKey) {
            $originUrl = $originUrlKey.'?'.$this->getPrimaryKey() .'='. $primaryVal;
            $originUrlKey = isset($one['url_key']) ? $one['url_key'] : '';
            $defaultLangTitle = Yii::$service->fecshoplang->getDefaultLangAttrVal($one['name'], 'name');
            $urlKey = Yii::$service->url->saveRewriteUrlKeyByStr($defaultLangTitle, $originUrl, $originUrlKey);
            $model->url_key = $urlKey;
            $model->save();
        }
        $product_id = $model->{$this->getPrimaryKey()};
        /**
         * 更新产品库存。
         */
        Yii::$service->product->stock->saveProductStock($product_id,$one);
        /**
         * 更新产品信息到搜索表。
         */
        Yii::$service->search->syncProductInfo([$product_id]);

        return $model;
    }
    
    /**
     *[
     *	'category_id' 	=> 1,
     *	'pageNum'		=> 2,
     *	'numPerPage'	=> 50,
     *	'orderBy'		=> 'name',
     *	'where'			=> [
     *		['>','price',11],
     *		['<','price',22],
     *	],
     *	'select'		=> ['xx','yy'],
     *	'group'			=> '$spu',
     * ]
     * 得到分类下的产品，在这里需要注意的是：
     * 1.同一个spu的产品，有很多sku，但是只显示score最高的产品，这个score可以通过脚本取订单的销量（最近一个月，或者
     *   最近三个月等等），或者自定义都可以。
     * 2.结果按照filter里面的orderBy排序
     * 3.由于使用的是mongodb的aggregate(管道)函数，因此，此函数有一定的限制，就是该函数
     *   处理后的结果不能大约32MB，因此，如果一个分类下面的产品几十万的时候可能就会出现问题，
     *   这种情况可以用专业的搜索引擎做聚合工具。
     *   不过，对于一般的用户来说，这个不会成为瓶颈问题，一般一个分类下的产品不会出现几十万的情况。
     * 4.最后就得到spu唯一的产品列表（多个spu相同，sku不同的产品，只要score最高的那个）.
     */
    public function getFrontCategoryProducts($filter)
    {
        $where = $filter['where'];
        if (empty($where)) {
            return [];
        }
        if (!isset($where['status'])) {
            $where['status'] = $this->getEnableStatus();
        }
        $orderBy = $filter['orderBy'];
        $pageNum = $filter['pageNum'];
        $numPerPage = $filter['numPerPage'];
        $select = $filter['select'];
        $group['_id'] = $filter['group'];
        $project = [];
        foreach ($select as $column) {
            $project[$column] = 1;
            $group[$column] = ['$first' => '$'.$column];
            
        }
        $group['product_id'] = ['$first' => '$product_id'];
        $langCode = Yii::$service->store->currentLangCode;
        
        $name_lang  = Yii::$service->fecshoplang->getLangAttrName('name',$langCode);
        $default_name_lang  = Yii::$service->fecshoplang->GetDefaultLangAttrName('name');
        $project['name'] = [
            $default_name_lang => 1,
            $name_lang => 1,
        ];
        $project['product_id'] = '$_id';
        $pipelines = [
            [
                '$match'    => $where,
            ],
            [
                '$sort' => [
                    'score' => -1,
                ],
            ],
            [
                '$project'    => $project,
            ],
//            [
//                '$group'    => $group,
//            ],
            [
                '$sort'    => $orderBy,
            ],
            [
                '$limit'    => Yii::$service->product->categoryAggregateMaxCount,
            ],
        ];
        // ['cursor' => ['batchSize' => 2]]
        $product_data = $this->_productModel->getCollection()->aggregate($pipelines);
        $product_total_count = count($product_data);
        $pageOffset = ($pageNum - 1) * $numPerPage;
        $products = array_slice($product_data, $pageOffset, $numPerPage);

        return [
            'coll' => $products,
            'count' => $product_total_count,
        ];
    }
}
