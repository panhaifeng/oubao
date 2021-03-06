<?php
function theme_widget_cfg_qf_artlist($app){
	$data = app::get('content')->model('article_nodes')->getList();
	//print_r($nodeItemdd);exit;
    return $data;
}
?>
