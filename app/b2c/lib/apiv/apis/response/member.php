<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c member interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_member
{

    //初始化会员信息
    public function init($params, &$service){
        $membersModel = app::get('b2c')->model('members');

        //默认分页码为1,分页大小为20
        $params['page_no'] = intval($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = intval($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $limit  = intval($params['page_size']);
        $offset = $page_no * $limit;

        //返回总数
        $rows = $membersModel->count();
        $data['item_total'] = $rows;

        $membersData = $membersModel->getList('*',array(),$offset,$limit);
        $data['list'] = $this->format_member_data($membersData);
        return $data;
    }

    public function get_member_filter($params, &$service)
    {
        $filter = array();
        if( isset($params['member_lv_ids']) && $params['member_lv_ids'] != null )
        {
            $member_lv_ids = json_decode($params['member_lv_ids']);
            $filter['member_lv_id|in'] = $member_lv_ids;
        }

        if( isset($params['member_regtime_begin']) && $params['member_lv_ids'] != null )
        {
            $member_regtime_begin = $params['member_regtime_begin'];
        }else{
            $member_regtime_begin = 0;
        }

        if( isset($params['member_regtime_end']) && $params['member_regtime_end'] != null )
        {
            $member_regtime_end = $params['member_regtime_end'];
        }else{
            $member_regtime_end = time();
        }
        $filter['regtime|between'] = array($member_regtime_begin, $member_regtime_end);

        $membersModel = app::get('b2c')->model('members');

        $membersData = $membersModel->getList('*', $filter);
        return $this->format_member_data($membersData);
    }

    /**
     *获取到会员等级列表
     */
    public function get_member_lv_list($params,&$service){
        $memberLvModel = app::get('b2c')->model('member_lv');
        $memberLvData = $memberLvModel->getList('*');
        $data = array();
        foreach( (array)$memberLvData as $k=>$row){
            $data[$k]['member_lv_id'] = intval($row['member_lv_id']);
            $data[$k]['name']         = $row['name'];
            $data[$k]['default_lv']   = ($row['default_lv'] == '1') ? 'true' : 'false';
        }
        return $data;
    }

    private function format_member_data($membersData)
    {
        $userPassport = kernel::single('b2c_user_passport');
        $list = array();
        foreach( (array)$membersData as $k=>$row ){
            //获取到用户名，手机号, 邮箱
            $pam_colunms = $userPassport->userObject->get_pam_data('*',$row['member_id']);

            //获取到注册项数据
            $attrData = $userPassport->get_signup_attr($row['member_id']);
            $attr = array();
            foreach( (array)$attrData  as $attr_k=>$attr_colunm){
                $attr[$attr_k]['attr_name'] = $attr_colunm['attr_name'];
                $attr[$attr_k]['attr_column'] = $attr_colunm['attr_column'];
                $attr[$attr_k]['attr_value'] = $attr_colunm['attr_value'];
            }
            $list[$k]['member_id'] = intval($row['member_id']);
            $list[$k]['member_lv_id'] = intval($row['member_lv_id']);
            $list[$k]['login_name'] = $pam_colunms['local'];
            $list[$k]['mobile'] = $pam_colunms['mobile'];
            $list[$k]['email'] = $pam_colunms['email'];
            $list[$k]['reg_ip'] = $row['reg_ip'];
            $list[$k]['regtime'] = $row['regtime'];
            $list[$k]['attr'] = $attr;
            $list[$k]['last_modify'] = '';
        }
        return $list;
    }
}
