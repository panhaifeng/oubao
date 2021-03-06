<?php
function theme_widget_member($setting,&$smarty){
   
}
function instance_loginplug($data){
    $path = APP_DIR.'/'.$data['app_id'].'/passport.'.$data['app_id'].'.php';
    if(file_exists($path)){
        require_once($path);
        $classname = 'passport_'.$data['plugin_ident'];
        $object = new $classname;
        return $object;
    }else{
        return false;
    }
}
?>
