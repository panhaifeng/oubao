<?php
function theme_widget_qf_hightad(&$setting,&$smarty){	
 $data = array();
	foreach($setting['had'] as $k){
		 $data[] = $k;
	}
	return $data;
}
?>

