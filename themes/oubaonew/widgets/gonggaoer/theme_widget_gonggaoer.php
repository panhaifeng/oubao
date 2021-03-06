<?php
function theme_widget_gonggaoer(&$setting,&$smarty){
	// dump2file($setting,'gonggaoer');
	//排序
	$flag1 = utils::_array_column($setting['year'], 'year');
	array_multisort($flag1,SORT_DESC,$setting['year']);
	$data = $setting['year'];
	foreach ($data as $key => &$year) {
		$flag2 = utils::_array_column($year['shijian'], 'order');
		array_multisort($flag2,SORT_ASC,$year['shijian']);
	}
	// dump2file($data,'gonggaoer');
	return $data;
}
?>

