<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

define('PLUGIN_DIR_LIB', ROOT_DIR.'/app/b2c/lib');
class b2c_mdl_member_messenger {

    var $plugin_type = 'dir';
    var $plugin_name = 'messenger';
    var $prefix = 'messenger.';
    var $db;
    function __construct(&$app){
        $this->app = $app;
        $this->db = kernel::database();
    }

    function getList($filter=array(), $ifMethods=true,$withDesc=false){
        $services = kernel::servicelist('b2c_messenger');
        $service = array();
        foreach($services as $key=>$v){
            $service[$key] = (array)$v;
            $service[$key]['methods'] = get_class_methods($v);
        }
        return $service;
    }

    function &_load($sender){
        if(!$this->_sender[$sender]){
            $obj = $this->load($sender);
            $this->_sender[$sender] = &$obj;
            if(method_exists($obj,'getOptions')||method_exists($obj,'getoptions'))
                $obj->config = $this->getOptions($sender,true);
            if(method_exists($obj,'outgoingConfig')||method_exists($obj,'outgoingconfig'))
                $obj->outgoingOptions = $this->outgoingConfig($sender,true);
        }else{
            $obj = $this->_sender[$sender];
        }
        return $obj;
    }

    function _ready(&$obj){
        if(!$obj->_isReady){
            if(method_exists($obj,'ready')) $obj->ready($obj->config);
            if(method_exists($obj,'finish')){
                if(!$this->_finishCall){
                    register_shutdown_function(array(&$this,'_finish'));
                    $this->_finishCall=array();
                }
                $this->_finishCall[] = &$obj;
            }
            $obj->_isReady = true;
        }
    }

    function _send($sendMethod,$tmpl_name,$target,$data,$type,$sendType='notice'){
        $sender = $this->_load($sendMethod);
        $this->_ready($sender);
        if(!$this->_systmpl){
            $this->_systmpl = $this->app->model('member_systmpl');
        }
        $content = $this->_systmpl->fetch($tmpl_name,$data);
        $tile = $this->loadTitle($type,$sendMethod,'',$data);
        $service = kernel::service("b2c.messenger.fireEvent_content");
        if(is_object($service))
        {
            if(method_exists($service,'get_content'))
                $content = $service->get_content($content);
                $tile = $service->get_content($tile);
        }
        if($tile=='') $tile = app::get('site')->getConf('site.name');
        $sender->config['shopname'] = app::get('site')->getConf('site.name');
        $sender->config['specialChannel'] = true;
        $sender->send($target,$tile,$content,$sender->config);
        return ($ret || !is_bool($ret));
    }

    ##????????????????????????????????? /email,ID,phone

    function get_send_type($sdfpath='member_id',$data,$member_id,$tmpl_name=null){
        $arr_colunms = kernel::single('b2c_user_object')->get_pam_data('*',$member_id);
        $obj_member = $this->app->model('members');
        $sdf = $obj_member->getList('addon',array('member_id'=>$member_id)) ;
        $sdf['addon'] = unserialize($sdf[0]['addon']);
        if('addon/def_addr/mobile' == $sdfpath){
            // ???????????????
            $target = $arr_colunms['mobile'];

            //?????????????????????????????????
            if($data['delivery']['ship_mobile']){
                $target = $data['delivery']['ship_mobile'];
            }
        }else{
            $target = $member_id;
        }

        if('contact/email' == $sdfpath){
            $target = $arr_colunms['email'];
        }

        foreach(kernel::servicelist("message_contact") as $k=>$service)
        {
          if(is_object($service))
          {
            if(method_exists($service,"get_contact"))
            {
              $service->get_contact($member_id,$target,$tmpl_name,$sdfpath);
            }
          }
        }
        return $target;
    }

    function _finish(){
        foreach($this->_finishCall as $obj){
            $obj->finish($obj->config);
        }
    }


    /**
     * actionSend
     *
     * @param mixed $type ??????
     * @param mixed $contectInfo  ????????????
     * @param mixed $member_id ??????id
     * @param mixed $data ??????
     * @access public
     * @return void
     */
    function actionSend($type,$data,$member_id=null,$email=null){
        $actions = $this->actions();
        $senders = $this->getSenders($type); //email/msbox/sms
        $level = $actions[$type]['level'];
        $desc = $actions[$type]['label'];
        $sendType = $actions[$type]['sendType'];
        foreach($senders as $sender){
            $tmpl_name = 'messenger:'.$sender.'/'.$type;
            if(!$email && $sender){
                $target = $this->get_send_type(kernel::single($sender)->sdfpath,$data,$member_id,$tmpl_name);
            }else{
                $target = $email;
            }

            if($sender && $target){
                if($level < 10){ //??????
                    $this->addQueue($sender,$tmpl_name,$target,$data,$type,$sendType);
                }else{ //???????????? print

                    $this->_send($sender,$tmpl_name,$target,$data,$type,$sendType);
                }
            }
        }
    }

    // ??????email,sms,msbox??????
    function addQueue($sender,$tmpl_name,$target,$data,$type,$sendType){
        $queue_params = array(
                'sendMethod' => $sender,
                'tmpl_name' => $tmpl_name,
                'target' => $target,
                'data' => $data,
                'type' => $type,
                'sendType' => $sendType ?  $sendType : 'notice'
        );
      $this->queue_send($queue_params);//??????????????????????????????
     #   system_queue::instance()->publish('b2c_tasks_sendmessenger', 'b2c_tasks_sendmessenger', $queue_params);
    }

    // ????????????email,sms,msbox
    function queue_send($params){
        $sendMethod = $params['sendMethod'];
        $tmpl_name = $params['tmpl_name'];
        $target = $params['target'];
        $data = $params['data'];
        $type = $params['type'];
        $sendtype = $params['sendType'];

        $sender = $this->_load($sendMethod);
        $this->_ready($sender);
        if(!$this->_systmpl){
            $this->_systmpl = $this->app->model('member_systmpl');
        }
        $content = $this->_systmpl->fetch($tmpl_name,$data);
        $tile = $this->loadTitle($type,$sendMethod,'',$data);
        $service = kernel::service("b2c.messenger.fireEvent_content");
        if(is_object($service))
        {
            if(method_exists($service,'get_content'))
                $content = $service->get_content($content);
                $tile = $service->get_content($tile);
        }
        if($tile=='') $tile = app::get('site')->getConf('site.name');
        $sender->config['shopname'] = app::get('site')->getConf('site.name');
        $sender->config['sendType'] = $sendtype;
        $sender->config['specialChannel'] = false;
        return $sender->send($target,$tile,$content,$sender->config);
    }

    function getSenders($act){
        $ret = $this->app->getConf('messenger.actions.'.$act);
        return explode(',',$ret);
    }

    function saveActions($actions){
        foreach($this->actions() as $act=>$info){
            if(!$actions[$act]){
                $actions[$act] = array();
            }
        }
        foreach($actions as $act=>$call){
            $this->app->setConf('messenger.actions.'.$act,implode(',',array_keys($call)));
        }
        return true;
    }

    /**
     * actions
     * ??????????????????????????????????????????????????????????????????????????????
     *
     * ?????????
     *            ??????-?????? => array(label=>?????? , level=>????????????)
     *
     * level ??????10?????????????????????10??????????????? ??????10?????????????????????????????????????????????????????????????????????????????????????????????
     * ??????????????????????????????????????????????????????send()????????????
     *
     * ???????????????????????????API??????sendtype??????????????????notice???????????????????????????
     * ???????????????????????????API??????sendtype??????????????????fan-out???????????????????????????
     * @access public
     * @return void
     */
    function actions(){
        $actions = array(
            'account-member'=>array('label'=>app::get('b2c')->_('????????????'),'level'=>11,'sendType'=>'notice','b2c_messenger_msgbox'=>'false','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$vcode}>&nbsp;&nbsp;&nbsp;&nbsp;'),
            'account-signup'=>array('label'=>app::get('b2c')->_('PC???????????????????????????'),'level'=>11,'sendType'=>'notice','b2c_messenger_msgbox'=>'false','b2c_messenger_email'=>'false','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$vcode}>&nbsp;&nbsp;&nbsp;&nbsp;'),
            'account-signup-mobile'=>array('label'=>app::get('b2c')->_('?????????????????????????????????'),'level'=>11,'sendType'=>'notice','b2c_messenger_msgbox'=>'false','b2c_messenger_email'=>'false','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$vcode}>&nbsp;&nbsp;&nbsp;&nbsp;'),
            'account-lostPw'=>array('label'=>app::get('b2c')->_('??????????????????'),'level'=>11,'sendType'=>'notice','b2c_messenger_msgbox'=>'false','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('?????????').'&nbsp;<{$vcode}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('??????').'&nbsp;<{$name}>'),
            'orders-shipping'=>array('label'=>app::get('b2c')->_('???????????????'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$delivery.money}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$delivery.delivery}><br>'.app::get('b2c')->_('????????????').'&nbsp;<{$ship_corp}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$ship_billno}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('???????????????').'&nbsp;<{$delivery.ship_name}><br>'.app::get('b2c')->_('???????????????').'&nbsp;<{$delivery.ship_addr}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('???????????????').'&nbsp;<{$delivery.ship_zip}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('???????????????').'&nbsp;<{$delivery.ship_tel}><br>'.app::get('b2c')->_('???????????????').'&nbsp;<{$delivery.ship_mobile}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('?????????').'Email&nbsp;<{$delivery.ship_email}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('?????????').'&nbsp;<{$delivery.op_name}><br>'.app::get('b2c')->_('??????').'&nbsp;<{$delivery.memo}>'),
            'orders-create'=>array('label'=>app::get('b2c')->_('???????????????'),'level'=>9,'b2c_messenger_sms'=>'false','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('??????').'&nbsp;<{$total_amount}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$shipping_id}><br>'.app::get('b2c')->_('???????????????').'&nbsp;<{$ship_mobile}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('???????????????').'&nbsp;<{$ship_tel}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('???????????????').'&nbsp;<{$ship_addr}><Br>'.app::get('b2c')->_('?????????').'Email&nbsp;<{$ship_email}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('???????????????').'&nbsp;<{$ship_zip}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('???????????????').'&nbsp;<{$ship_name}>'),
            'orders-payed'=>array('label'=>app::get('b2c')->_('???????????????'),'level'=>9,'b2c_messenger_sms'=>'false','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$order_id}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('?????????').'&nbsp;<{$pay_account}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$pay_time}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$money}>'),
            'orders-returned'=>array('label'=>app::get('b2c')->_('???????????????'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$order_id}>'),
            'orders-refund'=>array('label'=>app::get('b2c')->_('???????????????'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$order_id}>'),
            'goods-notify'=>array('label'=>app::get('b2c')->_('??????????????????'),'level'=>6,'sendType'=>'fan-out','varmap'=>app::get('b2c')->_('????????????').'&nbsp;<{$goods_name}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$username}>'),
            'goods-recommend'=>array('label'=>app::get('b2c')->_('????????????'),'level'=>9,'sendType'=>'notice','b2c_messenger_sms'=>'false','b2c_messenger_msgbox'=>'false','varmap'=>app::get('b2c')->_('????????????').'&nbsp;<{$goods_name}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$goods_brief}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$goods_url}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('?????????').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('??????').'&nbsp;<{$content}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('????????????').'&nbsp;<{$shopname}>'),
            /*             'goods-replay'=>array('label'=>'??????????????????','level'=>9), todo */
            'account-register'=>array('label'=>app::get('b2c')->_('???????????????'),'b2c_messenger_sms'=>'false','level'=>9,'varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;email&nbsp;<{$email}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('??????').'&nbsp;<{$passwd}>'),
            'account-chgpass'=>array('label'=>app::get('b2c')->_('?????????????????????'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('??????').'&nbsp;<{$passwd}>&nbsp;&nbsp;&nbsp;&nbsp;'.app::get('b2c')->_('?????????').'&nbsp;<{$uname}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;email&nbsp;<{$email}>'),
            /*             'comment-replay'=>array('label'=>'???????????????','level'=>9,'varmap'=>''), todo */
            /*             'indexorder-pay'=>array('label'=>'??????????????????','level'=>9), */
            /*             'comment-new'=>array('label'=>'????????????????????????','level'=>9), */
            'orders-cancel'=>array('label'=>app::get('b2c')->_('????????????'),'level'=>9,'sendType'=>'notice','varmap'=>app::get('b2c')->_('?????????').'&nbsp;<{$order_id}>'),
        );
        foreach(kernel::servicelist('firevent_type') as $service){
            if(is_object($service)){
                if(method_exists($service,'get_type')){
                    $data = $service->get_type();
                }
            }
			$actions = array_merge($actions,(array)$data);
        }

        return $actions;
    }


    function loadTmpl($action,$msg,$lang=''){
        $systmpl = $this->app->model('member_systmpl');
        return $systmpl->get('messenger:'.$msg.'/'.$action);
    }

    function loadTitle($action,$msg,$lang='',$data=""){

        $tmpArr=$data;
        $title = $this->app->getConf('messenger.title.'.$action.'.'.$msg);

        if($data!=""){
            preg_match_all('/<\{\$(\S+)\}>/iU', $title, $result);

            foreach($result[1] as $k => $v){
               $v=explode('.',$v);
               $data=$tmpArr;

               foreach($v as $key => $val){

                     $data=$data[$val];

                     if(is_array($data))
                     continue ;
                     else{

                         $title = str_replace($result[0][$k],$data,$title);

                     }

                 }
             }

         }

        return $title;
    }

    function saveContent($action,$msg,$data){
        $systmpl = $this->app->model('member_systmpl');
         $info = $this->getParams($msg);
        if($info['hasTitle']) $this->app->setConf('messenger.title.'.$action.'.'.$msg,$data['title']);
        return $systmpl->set('messenger:'.$msg.'/'.$action,$data['content']);
    }

    function &load($item){
        if (!$this->_plugin_obj[$item]) {
           if($obj = kernel::single($item))   return $obj;
           else return null;
        }
        return $this->_plugin_obj[$item];
    }

    function getOptions($item,$valueOnly = false){
        $obj = $this->load($item);
        if(method_exists($obj,'getOptions')||method_exists($obj,'getoptions')){
            $options = $obj->getOptions();      #print_r($options);exit;
            foreach($options as $key=>$value){
                $app = app::get('desktop');
                $v = $app->getConf('email.config.'.$key);
                if($valueOnly){
                    $options[$key] = (is_null($v))?$options[$key]:$v;
                }
                else{
                    $options[$key]['value'] = (is_null($v))?$options[$key]['value']:$v;
                }
            }
            return $options;
        }
    }

     function getParams($msg){
        $aData = $this->getList();
        return $aData[$msg];
    }
}

?>
