<?php
function theme_widget_seka(&$setting,&$smarty){
	$data = array();
    foreach($setting['focus'] as $focus){
        $data[] = $focus;
    }
    //二维数组排序
    $sort = utils::_array_column($data,'order');
    array_multisort($sort,$data,SORT_ASC);
    return $data;
}
?>
