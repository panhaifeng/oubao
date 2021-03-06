<?php
function theme_widget_xinpinsudi(&$setting,&$render){
    // dump2file($setting,'xinpinsudi');
    //列表中切换的商品信息
    $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
    $goodsFilter['goodsNum'] = $count = count($goodsFilter['goods_id']);
    $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
    $goodsinfo = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
    $goods_duo = array_values($goodsinfo['goodsRows']);
    //每2个分为一组
    $fen = $count/2;
    $j = 0;
    for ($i=0; $i <$fen ; $i++) {
		$new = array_slice($goods_duo, $j,2);
		$j=$j+2;
		$newA[$i] = $new;
	}
	$setting['goods_duo'] = $newA;
	// dump2file($setting,'xinpinsudi');
    return $setting;
}
?>
