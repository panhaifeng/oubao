<?php
function theme_widget_quxiaohuaxing(&$setting,&$render){
    //列表中切换的商品信息
    $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
    $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
    $goodsFilter['goodsNum']  = count($goodsFilter['goods_id']);
    $flag = 1;
    $goodsinfo = b2c_widgets::load('Goods')->getGoodsList($goodsFilter,$platform='site',$flag);
    $goods_duo = array_values($goodsinfo['goodsRows']);
    $count = count($goods_duo);
    //每2个分为一组
    $fen = $count/2;
    $j = 0;
    for ($i=0; $i <$fen ; $i++) {
		$new = array_slice($goods_duo, $j,2);
		$j=$j+2;
		$newA[$i] = $new;
	}
	$setting['goods_duo'] = $newA;
    return $setting;
}
?>

