<?php
function theme_widget_gaojidingzhitwo(&$setting,&$smarty){
	// dump2file($setting,'gaojidingzhitwo');
	//单个商品的信息
    $goodsFilter['goods_id'] = explode(",", $setting["goods_select_dan"]);
    $goodsinfo = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
    //dump2file($_return,'goods_select_dan');
    $setting['goods_dan'] = array_values($goodsinfo['goodsRows']);
    $setting['goods_dan'] = $setting['goods_dan'][0];
    $setting['goods_select_linkobj_dan'] = json_decode($setting['goods_select_linkobj_dan'],true);
    $setting['goods_select_linkobj_dan'] = $setting['goods_select_linkobj_dan'][0];
    //列表中切换的商品信息
    $goodsFilter['goods_id'] = explode(",", $setting["goods_select_duo"]);
    $goodsFilter['goodsNum'] = count($goodsFilter['goods_id']);
    $goodsinfo = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
    $setting['goods_duo'] = array_values($goodsinfo['goodsRows']);
	// dump2file($setting,'gaojidingzhitwo');
    return $setting;
}
?>

