<?php
function theme_widget_qf_widefocus(&$setting,&$smarty){
	$data = array();
	//排序
	$flag1 = utils::_array_column($setting['focus'], 'orderBy');
	array_multisort($flag1,SORT_ASC,$setting['focus']);
    foreach($setting['focus'] as $focus){
        $data[] = $focus;
    }
	//print_r($data);exit;
    return $data;
}
?>


