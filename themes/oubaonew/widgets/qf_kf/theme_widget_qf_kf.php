<?php 
function theme_widget_qf_kf(&$setting,&$render){
	 $data = array();
  if('site_ctl_default' == get_class($render)){
			   $data['isindex'] = true;
  }
  $data['page'] = app::get('site')->router()->get_query_info('module');
		if(is_array($setting['floor'])){
			foreach($setting['floor'] as $value){
				$data['floor'][] = $value;
			}
		}
  return $data;
}
?>