<?php
/*基础配置项*/
$setting['author']='易奇设计团队';
$setting['version']='v1.0';
$setting['name']='商品排行挂件';
$setting['order']=0;
$setting['stime']='2013-07';
$setting['catalog']='商品相关';
$setting['description'] = '本挂件用于商排行调用，并且可以选择产品缩略图的常用规格';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
	'default.html'=>app::get('b2c')->_('默认')
);
/*初始化配置项*/
$setting['selector'] = 'filter';
$setting['title'] = '一周销量排行';
$setting['showing'] = '3';
?>
