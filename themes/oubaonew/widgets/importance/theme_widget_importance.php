<?php
function theme_widget_importance(&$setting,&$smarty){
	//排序
	$flag1 = utils::_array_column($setting['had'], 'orderBy');
	array_multisort($flag1,SORT_ASC,$setting['had']);
}
?>


