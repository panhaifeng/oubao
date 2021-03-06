<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_task{

    /**
     * listener array
     */
    private $arr_listener = array(
        'b2c:orders:create' =>'b2c:stats_listener:get_orderinfo',
        'b2c:orders:payed'=>'b2c:stats_listener:get_payinfo',
        'b2c:orders:shipping'=>'b2c:stats_listener:get_deliveryinfo',
        'b2c:orders:returned'=>'b2c:stats_listener:get_deliveryinfo',
        'b2c:member_account:register'=>'b2c:stats_listener:get_memberinfo',
        'b2c:member_account:login'=>'b2c:stats_listener:get_logmember',
        'b2c:member_advance:changeadvance'=>'b2c:stats_listener:get_money',
    );

    /**
     * modifiers array
     */
    private $arr_lister_keys = array(
        'b2c:orders:create' =>'ORDERINFO',
        'b2c:orders:payed'=>'PAYINFO',
        'b2c:orders:shipping'=>'SHIPINFO',
        'b2c:orders:returned'=>'SHIPINFO',
        'b2c:member_account:register'=>'MEMBERINFO',
        'b2c:member_account:login'=>'LOGININFO',
        'b2c:member_advance:changeadvance'=>'DESPOITINFO',
    );

    function install_options(){
        return array();
    }

    function post_update( $dbver ){
        $obj_vcat = app::get('b2c')->model('goods_virtual_cat');

        $oSearch = app::get('b2c')->model('search');

        $vcatList = $obj_vcat->getList('virtual_cat_id,filter');
        if( $vcatList ){
            foreach( $vcatList as $k => $v ){
                $filters=$obj_vcat->_mkFilter($v['filter']);
                $v['url']=app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_gallery','args'=>array(implode(",",$filters['cat_id']),$oSearch->encode($filters),0,'','',$v['virtual_cat_id']) ));
                $obj_vcat->save( $v );
            }
        }

        if($dbver['dbver'] < 5.1){
            $comment_goods_point_model = app::get('b2c')->model('comment_goods_point');
            $total = $comment_goods_point_model->count();
            $page = ceil($total/1000);
            $i = 1;
            $offset = 0;
            for($i;$i<=$page;$i++){
                $offset = ($i-1)*1000;
                $data = $comment_goods_point_model->getList('point_id,addon',array(),$offset,1000);
                $status = 'false';
                foreach($data as $row){
                    $addon = unserialize($row['addon']);
                    $status = $addon['display'] ? $addon['display'] : 'false';
                    $comment_goods_point_model->update(array('display'=>$status),array('point_id'=>$row['point_id']));
                }
                logger::info('UPDATE sdb_b2c_comment_goods_point numbers '.$offset .'......ok' );
            }
        }
        //b2c 5.1?????????comment_goods_point?????????addon?????????display?????????????????????????????????
        $shell = kernel::single('base_shell_webproxy');
        if($dbver['dbver'] < 5.2){
            $shell->exec_command('install apiactionlog');

        }
        if($dbver['dbver'] < 5.4){
            if(!app::get('b2c')->getConf('comment.verifyCode')){
                app::get('b2c')->setConf('comment.verifyCode','on');
            }
        }
        if($dbver['dbver'] < 5.5){
            $shell->exec_command('dev:lang reset');
        }
		if($dbver['dbver'] < 5.6){
			$tag = app::get('desktop')->model('tag')->getList('tag_id',array('tag_filter|has'=>'b2c_promotion_solutions_topercent'));
			if(!$tag){
				$data['tag_filter'] = 'b2c_promotion_solutions_bypercent,b2c_promotion_solutions_goods_bypercent,b2c_promotion_solutions_topercent';
				$filter['tag_name'] = "??????";
				$filter['tag_type'] = "promotion";
				$filter['app_id'] = "b2c";
				app::get('desktop')->model('tag')->update($data,$filter);
			}
            $shell->exec_command('dev:lang reset');
        }
        if($dbver['dbver'] < 5.7)
        {
            app::get('b2c')->setConf('messenger.actions.account-signup-mobile','b2c_messenger_sms');
        }

        // if(app::get('archive')->define('version') < 0.2){
        //     $shell->exec_command('install archive');
        // }
    }

    function post_install($options)
    {
        pam_account::register_account_type('b2c','member',app::get('b2c')->_('??????????????????'));
        logger::info('Register b2c meta');
        $obj_goods = app::get('b2c')->model('goods');
        $obj_brand = app::get('b2c')->model('brand');
        $obj_goodscat = app::get('b2c')->model('goods_cat');
        $col = array(
          'adjunct'=>
            array (
                  'type' => 'text',
                  'required' => false,
                  'label' => app::get('b2c')->_('????????????'),
                  'width' => 110,
                  'editable' => false,
            ),
        );
        $obj_goods->meta_register($col);

        $col = array(
            'seo_info' => array(
                    'type' => 'serialize',
                  'label' => app::get('b2c')->_('seo??????'),
                  'width' => 110,
                  'editable' => false,
             ),
        );
        $obj_goods->meta_register($col);
        $obj_brand->meta_register($col);
        $obj_goodscat->meta_register($col);
        //????????????????????????url???????????????????????????
//        $obj_vcat = app::get('b2c')->model('goods_virtual_cat');
//        $col = array(
//            'url' => array(
//                'type' => 'varchar(200)',
//                'label' => app::get('b2c')->_('url'),
//                'width' => 110,
//                'editable' => false
//            )
//        );
//        $obj_vcat->meta_register( $col );

        logger::info('Initial b2c');
        kernel::single('base_initial', 'b2c')->init();

        logger::info('Init b2c member');
        $attr_model = app::get('b2c')->model('member_attr')->init();

        $logo = app::get('b2c')->getConf('site.logo');
        $obj_image = app::get('image')->model('image');
        $app_dir = app::get('b2c')->app_dir;
        $obj_image->store($app_dir.'/initial/site_logo.png',$logo);
        $obj_image->store( $app_dir.'/initial/default_images/spec_def.bmp',app::get('b2c')->getConf('spec.default.pic') );

        // set listener and modifier
        $app_b2c = app::get('b2c');
        $all = $app_b2c->getConf('system.event_listener');
        if ($this->arr_listener)
        {
            foreach($this->arr_listener as $k=>$v){
                $k = strtolower($k);
                $v = strtolower($v);
                if (!isset($all[$k]))
                    $all[$k] = array();
                $all[$k][$v] = $v;
            }
        }
        $app_b2c->setConf('system.event_listener',$all);

        $all = $app_b2c->getConf('system.event_listener_key');
        if ($this->arr_lister_keys)
        {
            foreach($this->arr_lister_keys as $k=>$v)
            {
                $k = strtolower($k);
                $v = strtolower($v);
                if (!isset($all[$k]))
                    $all[$k] = array();
                $all[$k][$v] = $v;
            }
        }
        $app_b2c->setConf('system.event_listener_key',$all);

        // ??????node_id...
        /*if (!base_shopnode::node_id('b2c') && base_certificate::certi_id())
        {
            base_shopnode::active('b2c');
        }*/

        //Application
        $rows = app::get('base')->model('apps')->getList('app_id',array('installed'=>1));
		foreach($rows as $r){
			if($r['app_id'] == 'base')  continue;
			$args[] = $r['app_id'];
		}
        app::get('b2c')->setConf('comment.verifyCode','on');

		foreach ((array)$args as $app)
		{
			$this->xml_update($app);
		}
    }

    function post_uninstall(){
        pam_account::unregister_account_type('member');

        // set listener and modifier
        $app_b2c = app::get('b2c');
        $all = $app_b2c->getConf('system.event_listener');
        $len = strlen($ident)+1;
        foreach($all as $k=>$m){
            if ($all[$k][$m] == $this->arr_listener[$k])
            {
                unset($all[$k][$m]);
            }
        }
        $app_b2c->setConf('system.event_listener',$all);

        $all = $app_b2c->getConf('system.event_listener_key');
        foreach($all as $k=>$m){
            if ($all[$k][$m] == $this->arr_lister_keys[$k])
            {
                unset($all[$k][$m]);
            }
        }
        $app_b2c->setConf('system.event_listener_key',$all);

        // ??????node_id...
        /*if (base_shopnode::node_id('b2c'))
        {
            base_shopnode::delete_node_id('b2c');
        }*/
    }

	function get_node_id(){
		return false;
	}


   /**
	* xml?????????????????????
	* @param object $app app????????????
	*/
	private function xml_update($app)
	{
		if (!$app) return;

		$detector = kernel::single('b2c_application_apiv');
		foreach($detector->detect($app) as $name=>$item){
			$item->install();
		}

	}


}

