<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$setting['author']='lsc@eqinfo';
$setting['version']='v1.0.0';
$setting['name']='优惠券领取';
$setting['catalog'] = '营销活动';
$setting['description'] = '根据需要来展示可领取的优惠券';
$setting['usual'] = '0';
$setting['stime'] ='2017-1';
$setting['userinfo'] = '';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                      );
$setting['showtype']=2;
$setting['coupon_left']=10;
?>
