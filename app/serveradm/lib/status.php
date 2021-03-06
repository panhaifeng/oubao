<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class serveradm_status
{   
    private $_mysql_status = array(
                "Connections"=>"",
                "Created_tmp_disk_tables"=>"",
                "Created_tmp_files"=>"",
                "Created_tmp_table"=>"",
                //"Innodb_buffer_pool_pages_data"=>"",
                //"Innodb_buffer_pool_pages_dirty"=>"",
                //"Innodb_buffer_pool_pages_flushed"=>"",
                //"Innodb_buffer_pool_pages_free"=>"",
                //"Innodb_buffer_pool_pages_misc"=>"",
                //"Innodb_buffer_pool_pages_total"=>"",
                //"Innodb_buffer_pool_read_ahead_rnd"=>"",
                //"Innodb_buffer_pool_read_ahead_seq"=>"",
                //"Innodb_buffer_pool_read_requests"=>"",
                //"Innodb_buffer_pool_reads"=>"",
                //"Innodb_buffer_pool_wait_free"=>"",
                //"Innodb_buffer_pool_write_requests"=>"",
                //"Innodb_data_read"=>"",
                //"Innodb_data_reads"=>"",
                //"Innodb_data_writes"=>"",
                //"Innodb_data_written"=>"",
                //"Innodb_pages_read"=>"",
                //"Innodb_pages_written"=>"",
                //"Innodb_row_lock_current_waits"=>"",
                //"Innodb_row_lock_time"=>"",
                "Innodb_row_lock_time_avg"=>"",
                "Innodb_row_lock_time_max"=>"",
                //"Innodb_row_lock_waits"=>"",
                //"Key_blocks_used"=>"",
                //"Key_read_requests"=>"",
                //"Key_reads"=>"",
                //"Key_write_requests"=>"",
                //"Key_writes"=>"",
                //"Open_files"=>"",
                //"Open_table_definitions"=>"",
                //"Open_tables"=>"",
                //"Opened_files"=>"",
                //"Opened_table_definitions"=>"",
                //"Opened_tables"=>"",
                //"Qcache_free_blocks"=>"",
                //"Qcache_free_memory"=>"",
                //"Qcache_hits"=>"",
                //"Qcache_lowmem_prunes"=>"",
                //"Qcache_not_cached"=>"",
                //"Qcache_queries_in_cache"=>"",
                //"Qcache_total_blocks"=>"",
                "Select_full_join"=>"",
                "Select_full_range_join"=>"",
                //"Select_range"=>"",
                //"Select_range_check"=>"",
                "Slow_queries"=>"",
    );
    
    /**
     * ?????????????????????????????????????????????
     * @param object ??????????????????
     * @return null
     */
    public function __construct(&$app)
    {
        $this->app = $app;  
    }
    
    public function getCacheInfo()
    {
        if(!defined("CACHE_STORAGE")) return false;
        $aTmp = explode("_",CACHE_STORAGE);
        $msg = false;
        cachemgr::status($msg);
        $aResult = array(
                        "name"=>$aTmp[count($aTmp)-1],
                        "status"=>$msg,
                   );
        return $aResult;
    }
    
    public function getKVStorageInfo()
    {
        if(!defined("KVSTORE_STORAGE")) return false;
        $aTmp = explode("_",KVSTORE_STORAGE);
        $aResult = array(
                        "name"=>$aTmp[count($aTmp)-1],
                   );
        return $aResult;
    }
    
    public function getMysqlStatus()
    {
        $db = kernel::database();
        $aStatus = $db->select("show status");
        
        $aShowStaus = array_keys($this->_mysql_status);
        
        $aResult = array();
        // ?????????????????????
        foreach($aStatus as $row) 
        {
            if(in_array($row["Variable_name"],$aShowStaus)) $aResult[$row["Variable_name"]] = $row["Value"];
        }
        
        return $aResult;
    }
    
    public function getXHProfStatus()
    {
        if(!extension_loaded('xhprof')) return false;
        // ?????????
        return 'XHProf';
    }
    
    public function getServerInfo()
    {
        return array(
                    array(
                        'name'=>__("PHP??????"),
                        'value'=>PHP_VERSION,
                    ),
                    array(
                        'name'=>__("php????????????"),
                        'value'=>php_sapi_name().(ini_get('safe_mode')? "" : "&nbsp(".__("????????????").")" ),
                    ),
                    array(
                        'name'=>__("????????????"),
                        'value'=>PHP_OS,
                    ),
                    array(
                        'name'=>__("??????????????????"),
                        'value'=>$_SERVER['SERVER_SOFTWARE'],
                    ),
                    array(
                        'name'=>__("??????????????????"),
                        'value'=> get_cfg_var("upload_max_filesize")? get_cfg_var("upload_max_filesize") : __("?????????????????????"),
                    ),
                    array(
                        'name'=>__("??????????????????"),
                        'value'=> get_cfg_var("max_execution_time")? get_cfg_var("max_execution_time").__("???") : __("?????????"),
                    ),
                    array(
                        'name'=>__("???????????????"),
                        'value'=>  get_cfg_var("memory_limit")? get_cfg_var("memory_limit") :  __("???"),
                    ),
                    array(
                        'name'=>__("Optimizer Version"),
                        'value'=>  defined("OPTIMIZER_VERSION")? OPTIMIZER_VERSION :  __("???"),
                    ),
               );
    }
}