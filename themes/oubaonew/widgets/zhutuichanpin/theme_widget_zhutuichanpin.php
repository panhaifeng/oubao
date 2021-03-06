<?php
function theme_widget_zhutuichanpin(&$setting,&$render){
	$ifLogin = b2c_widgets::load('Goods')->ifLogin();
	$setting['ifLogin'] = $ifLogin;
    return $setting;
 }
?>

