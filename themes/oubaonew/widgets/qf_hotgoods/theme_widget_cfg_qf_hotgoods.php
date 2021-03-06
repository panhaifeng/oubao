<?php
function theme_widget_cfg_qf_hotgoods(){
	$data['goods_order_by'] = b2c_widgets::load('Goods')->getGoodsOrderBy();
    /*$data['top_comment'] = b2c_widgets::load('Comment')->getTopComment();*/    //通过数据接口取数据
	 
	
	 $data['goods_comment'] = b2c_widgets::load('Comment')->getTopComment();    //通过数据接口取数据
    return $data;
}
?>
