<?php
function theme_widget_footer(&$setting,&$render){
	foreach ($setting['floor'] as $key => &$value) {
		if($_COOKIE['LANG'] == 'en_US'){
            $value['links'] = $value['linksEn'];
            if ( in_array($value['title'], array('账户信息','支付/配送方式','购物条款','邮费链接')) ) {
            	unset($setting['floor'][$key]);
            }
        }
	}
	return $setting;
}
?>
