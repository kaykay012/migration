#!/bin/sh
Cur_Dir=$(cd `dirname $0`; pwd)
# get data all count.
count=`$Cur_Dir/../../../../yii migration/ecshop/users/count`
pagenum=`$Cur_Dir/../../../../yii migration/ecshop/users/pagenum`

echo "There are $count datas to process"
echo "There are $pagenum pages to process"
echo "##############ALL BEGINING###############";
for (( i=1; i<=$pagenum; i++ ))
do
   $Cur_Dir/../../../../yii migration/ecshop/users/import $i
   echo "Page $i done"
done

###### 1.Running Section End

echo "##############ALL COMPLETE###############";

