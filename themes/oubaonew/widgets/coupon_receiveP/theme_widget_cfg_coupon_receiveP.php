<?php

    function theme_widget_cfg_coupon_receiveP(){
        $data['coupons'] = b2c_widgets::load('Coupons')->getReceiveCoupons();
        // dump($data);die;
        return $data;
    }
?>
