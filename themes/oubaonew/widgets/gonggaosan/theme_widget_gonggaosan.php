<?php
function theme_widget_gonggaosan(&$setting,&$smarty){
	// dump2file($setting,'gonggaosan');
	if ($setting["goods_select"]) {
		$goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
	    $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
	    $goodsinfo = b2c_widgets::load('Goods')->getGoodsList($goodsFilter,$platform='site');
	    foreach ($goodsinfo['goodsRows'] as $key => $value) {
	    	$setting['goodsinfo']= $value;
	    }
	}
	//二维数组排序
    $sort = utils::_array_column($setting['info'],'sort');
    array_multisort($sort,$setting['info'],SORT_ASC);
    return $setting;
}
?>


