<?php
function theme_widget_qf_artlist($setting,&$render){
		 //查找分类的资料
   $node_id = (intval($setting['node_id'])>0)?intval($setting['node_id']):11;
   $limit = (intval($setting['limit'])>0)?intval($setting['limit']):10;   
   $o = b2c_widgets::load('Article')->getNodeArticles($node_id);
			$list = array();
			if ($setting['order_by'] == 'desc'){
   	  $list = array_reverse($o); //数据反转，取倒序排序
			}else{
				 $list = $o;
			}
			$result = array();
   $result['artlist'] = array_slice($list,0,$limit);//取前n条记录
			$result['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$node_id)); 
			
   //print_r($result);exit;
   return $result;
}
?>
