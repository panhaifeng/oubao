<?php
function theme_widget_cmt_qf_hotgoods(){
 
	 
	
	 $data['goods_comment'] = b2c_widgets::load('Comment')->getTopComment();    //通过数据接口取数据
    return $data;
}
?>
