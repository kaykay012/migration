#!/bin/sh
Cur_Dir=$(cd `dirname $0`; pwd)

count=`$Cur_Dir/../../../../yii migration/ecshop/category/count`
pagenum=`$Cur_Dir/../../../../yii migration/ecshop/category/pagenum`

echo "Category first begin..."
echo "There are $count datas to process"
echo "There are $pagenum pages to process"
echo "##############ALL BEGINING###############";
for (( i=1; i<=$pagenum; i++ ))
do
   $Cur_Dir/../../../../yii migration/ecshop/category/import $i
   echo "Page $i done"
done
echo "Category first end..."

count2=`$Cur_Dir/../../../../yii migration/ecshop/category/count 2`
pagenum2=`$Cur_Dir/../../../../yii migration/ecshop/category/pagenum 2`

echo "Category second begin..."
echo "There are $count2 datas to process"
echo "There are $pagenum2 pages to process"
echo "##############ALL BEGINING###############";
for (( x=1; x<=$pagenum2; x++ ))
do
   $Cur_Dir/../../../../yii migration/ecshop/category/import $x 2
   echo "Page $x done"
done
echo "Category second end..."

###### 1.Running Section End

echo "##############ALL COMPLETE###############";

