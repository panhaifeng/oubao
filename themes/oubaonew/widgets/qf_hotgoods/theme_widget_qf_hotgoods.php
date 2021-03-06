<?php
function theme_widget_qf_hotgoods(&$setting,&$render){
    $_return = false;
    switch ($setting['selector']) {
    case 'filter':
        parse_str($setting['goods_filter'],$goodsFilter);
        $goodsFilter['goodsOrderBy'] = $setting['goods_order_by'];
		$goodsFilter['goodsComment'] = $setting['goods_comment'];
        $goodsFilter['goodsNum'] = $setting['limit'];
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
		
        break;
    case 'select':
        $goodsFilter['goods_id'] = explode(",", $setting["goods_select"]);
        $goodsFilter['goodsNum'] = $setting['limit'];
		
        $_return = b2c_widgets::load('Goods')->getGoodsList($goodsFilter);
        foreach (json_decode($setting['goods_select_linkobj'],1) as $obj) {
            if($_return['goodsRows'][$obj['id']]){
                if(trim($obj['pic'])!=""){
                    $_return['goodsRows'][$obj['id']]['goodsPicL'] =
                        $_return['goodsRows'][$obj['id']]['goodsPicM'] =
                        $_return['goodsRows'][$obj['id']]['goodsPicS'] = $obj['pic'];
                }
                if(trim($obj['nice'])!=""){
                    $_return['goodsRows'][$obj['id']]['goodsName'] = $obj['nice'];
					
                }
            }
        }
        break;
    }
    $gids = array_keys($_return['goodsRows']);
    if(!$gids || count($gids) < 1) return $_return;
    $pointModel = app::get('b2c')->model('comment_goods_point');
	 //print_r($pointModel);exit;
    $goods_point_status = app::get('b2c')->getConf('goods.point.status');
    $point_status = $goods_point_status ? $goods_point_status: 'on';
    if($point_status == 'on' && $setting['show_star'] == 'true'){
        $sdf_point = $pointModel->get_single_point_arr($gids);
    }else{
        $setting['show_star'] = 'false';
    }
    foreach ($gids as $gid) {
        $_return['goodsRows'][$gid]['star'] = $sdf_point[$gid]['avg_num'];
    }
	//print_r($_return);exit;
    return $_return;
}
?>
