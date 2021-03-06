<?php
function theme_widget_goodslunbo(&$setting,&$render){
    //列表中切换的商品信息
    $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
    $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
    $goodsFilter['goodsNum'] = count($goodsFilter['goods_id']);
    $goodsinfo = b2c_widgets::load('Goods')->getGoodsList($goodsFilter,$platform='site');
    $setting['goodsRows'] = $goodsinfo['goodsRows'];
    return $setting;
}
?>
