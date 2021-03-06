<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_goods_category(&$setting, &$render) {
    $cat_mdl     = app::get('b2c')->model('goods_cat');
    $cat_list    = $cat_mdl->getMap(-1, 0, $isChakanSeka);
    $ret['data'] = $cat_list;
    //将$_returnData['data'][xx]['lv2']按照cat_title排序,和上一个不同的增加一个标记
    //输出时根据标记决定是否输出div <div class="cat_title">标题标题</div>
    //2018-2-26 by jeff
    foreach ($ret['data'] as $k => &$v) {
        if ($_COOKIE['LANG'] == 'en_US' && $v['cat_name'] == '一键定制衬衣') {
            unset($ret['data'][$k]);
        }
        if ($v['is_hidden']) {
            unset($ret['data'][$k]);
        }
        $lv2 = $v['items'];
        $tag = '';
        foreach ($lv2 as &$vv) {
            if ($vv['cat_title'] == '') {
                $vv['cat_title'] = $v['cat_name'];
            }
            $vv['cat_title'] = str_replace('|', '', $vv['cat_title']);
            if ($tag != $vv['cat_title']) {
                $vv['echoTitle'] = true;
                $tag             = $vv['cat_title'];
            }
        }
        $v['items'] = $lv2;
        if ($v['is_teshu'] && !$isChakanSeka) {
            $v['items']      = array();
            $v['items']['0'] = array(
                'cat_id'    => 1,
                'cat_title' => '此女装时尚色卡推荐部分有设置权限，如需要请联系业务员或者客服申请开通，谢谢',
                'echoTitle' => 1,
            );
        }
    }
    return $ret;
}
/**
 * 将一个二维数组按照指定列进行排序，类似 SQL 语句中的 ORDER BY
 *
 * @param array $rowset
 * @param array $args
 */
function array_sortby_multifields($rowset, $args) {
    $sortArray = array();
    $sortRule  = '';
    foreach ($args as $sortField => $sortDir) {
        foreach ($rowset as $offset => $row) {
            $sortArray[$sortField][$offset] = $row[$sortField];
        }
        $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
    }
    if (empty($sortArray) || empty($sortRule)) {return $rowset;}
    eval('array_multisort(' . $sortRule . '$rowset);');
    return $rowset;
}

function _ex_vertical_getAllChildAttr($arr, $pid, $attribute = 'cat_id') {
    foreach ($arr as $item) {
        if (in_array($pid, explode(',', $item['cat_path']))) {
            $_return[] = $item[$attribute];
        }
    }
    return $_return;
}

function _ex_vertical_getLinkBrandIds($typeids) {

    $sql = 'SELECT brand_id FROM ' . kernel::database()->prefix . 'b2c_type_brand WHERE type_id  in(' . implode(',', array_unique($typeids)) . ')';

    $res = app::get('b2c')->model('brand')->db->select($sql);

    foreach ($res as $key => $value) {
        $_return[] = $value['brand_id'];
    }

    if ($_return) {
        $_return = array_unique($_return);
    }
    return $_return;

}

function _ex_vertical_getSales() {

    $goods_sales_mdl  = app::get('b2c')->model('sales_rule_goods');
    $goods_sales_list = $goods_sales_mdl->getList('*');

    return $goods_sales_list;
}

?>
