<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class dev_serverinfo{

    var $allow_change_db = false;
    var $maxLevel = 6;
    function get_path_info() {
        $path_info = '';
        if (isset($_SERVER['PATH_INFO'])) {
            $path_info = $_SERVER['PATH_INFO'];
        }elseif(isset($_SERVER['ORIG_PATH_INFO'])){
            $path_info = $_SERVER['ORIG_PATH_INFO'];
            $script_name = self::get_script_name();
            if(substr($script_name, -1, 1) == '/'){
                $path_info = $path_info . '/';
            }
        }
        if($path_info){
        	$path_info = "/".ltrim($path_info,"/");
    	}
        return $path_info;
    }
    function run(){
        $return=array();

        $totalScore = 0;
        $allow_install = true;
        foreach(get_class_methods($this) as $func){
            if(substr($func,0,5)=='test_'){
                $score = 0;
                $result = $this->$func($score);
                if($result['items']){
                    $group[$result['group']]['type'] = $result['type'];
                    $group[$result['group']]['items'] = array_merge($group[$result['group']['items']]?$group[$result['group']['items']]:array(),$result['items']);
                    if($allow_install && isset($result['allow_install'])){
                        $allow_install = $result['allow_install'];
                    }
                    if($result['key']){
                        $return[$result['key']] = &$group[$result['group']]['items'];
                    }
                }
                $totalScore += $score;
            }
        }

        $score = floor($totalScore/100)+1;
        $rank = min($score,$this->maxLevel+1);
        $level = array('E','D','C','B','A','S');

        $return['data']=$group;
        $return['score']=$totalScore;
        $return['level']=$level[$rank-1];
        $return['rank'] = $rank;
        $return['allow_install'] = $allow_install;
        $return['path_info'] = $path_info;

        return $return;
    }

    function test_basic($score){
        $items['????????????']=PHP_OS;
        $items['???????????????']=$_SERVER["SERVER_SOFTWARE"];

        $runMode = null;

        $runMode = php_sapi_name();
        switch($runMode){
        case 'cgi-fcgi':
            $score+=50;
            break;
        }

        $safemodeStr = '<span style="color:red">'.app::get('dev')->_('(????????????)').'</span>';
        if($runMode){
            if(ini_get('safe_mode')){
                $runMode.='&nbsp;';
            }
            $items['php????????????']=$runMode;
        }elseif(ini_get('safe_mode')){
            $items['php????????????']=$safemodeStr;
        }

        return array('group'=>app::get('dev')->_('?????????????????????'),'key'=>'basic','items'=>$items);
    }

    function test_php(&$score){
        $items['php??????']=PHP_VERSION;
        if(is_callable('file_put_contents')){
            $score += 40;
        }
        if(is_callable('str_ireplace')){
            $score += 20;
        }
        if(is_callable('ftp_chmod')){
            $score += 10;
        }
        if(is_callable('http_build_query')){
            $score += 20;
        }

        $items['?????????????????????????????????&nbsp;memory_limit']=ini_get("memory_limit");
        $items['POST???????????????&nbsp;post_max_size']=ini_get("post_max_size");
        $items['????????????????????????&nbsp;upload_max_filesize']=ini_get("upload_max_filesize");
        $items['????????????????????????&nbsp;max_execution_time']=ini_get("max_execution_time");
        $disableFunc = get_cfg_var("disable_functions");
        $items['??????????????????&nbsp;disable_functions']=$disableFunc?$disableFunc:app::get('dev')->_('???');
        return array('group'=>app::get('dev')->_('php????????????'),'items'=>$items);
    }

    function test_server_req(&$score){


        $rst = version_compare(PHP_VERSION,'5.0','>=');
        $items['PHP5??????'] = array(
            'value'=>PHP_VERSION,
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }

        $rst = !get_cfg_var('zend.ze1_compatibility_mode');
        $items['zend.ze1_compatibility_mode ??????'] = array(
            'value'=>$rst?'Off':'On',
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }

        $tmpfname = tempnam(TMP_DIR, "foo");
        $handle = fopen($tmpfname, "w");
        $rst = flock($handle,LOCK_EX);
        fclose($handle);
        $items['???????????????(flock)'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }

        $rst = function_exists('xml_parse_into_struct');
        $items['php????????????xml??????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }

        $rst = function_exists('mysql_connect') && function_exists('mysql_get_server_info');
        $items['MySQL???????????????'] = array(
            'value'=>$rst?mysql_get_client_info():app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if(!$rst){
            $allow_install = false;
        }else{
            $rst = false;
            if(defined('DB_HOST')){
                if(defined('DB_PASSWORD')){
                    $rs = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
                }elseif(defined('DB_USER')){
                    $rs = mysql_connect(DB_HOST,DB_USER);
                }else{
                    $rs = mysql_connect(DB_HOST);
                }
                $db_ver = mysql_get_server_info($rs);
            }elseif($db_ver = mysql_get_server_info()){
                define('DB_HOST','');
            }else{
                $sock = get_cfg_var('mysql.default_socket');
                if(PHP_OS!='WINNT' && file_exists($sock) && is_writable($sock)){
                    define('DB_HOST',$sock);
                }else{
                    $host = ini_get('mysql.default_host');
                    $port = ini_get('mysql.default_port');
                    if(!$host)$host = '127.0.0.1';
                    if(!$port)$port = 3306;
                    define('DB_HOST',$host.':'.$port);
                }
            }
            if(!$db_ver){
                if(substr(DB_HOST,0,1)=='/'){
                    $fp = @fsockopen("unix://".DB_HOST);
                }else{
                    if($p = strrpos(DB_HOST,':')){
                        $port = substr(DB_HOST,$p+1);
                        $host = substr(DB_HOST,0,$p);
                    }else{
                        $port = 3306;
                        $host = DB_HOST;
                    }
                    $fp = @fsockopen("tcp://".$host, $port, $errno, $errstr,2);
                }
                if (!$fp){
                    $db_ver = app::get('dev')->_('????????????');
                } else {
                    fwrite($fp, "\n");
                    $db_ver = fread($fp, 20);
                    fclose($fp);
                    if(preg_match('/([2-8]\.[0-9\.]+)/',$db_ver,$match)){
                        $db_ver = $match[1];
                        $rst = version_compare($db_ver,'4.0','>=');
                    }else{
                        $db_ver = app::get('dev')->_('????????????');
                    }
                }
            }else{
                $rst = version_compare($db_ver,'4.1','>=');
            }

            $this->db_ver = $db_ver;

            $mysql_key = app::get('dev')->_('?????????Mysql 4.1??????').'&nbsp;<i style="color:#060">'.DB_HOST.'</i>';
            if($this->allow_change_db){
                $mysql_key.='<form method="get" action="" style="margin:0;padding:0"><table><tr><td><label for="db_host">'.app::get('dev')->_('MySQL??????').'</label></td><td>&nbsp;</td></tr><tr><td><input id="db_host" value="'.DB_HOST.'" name="db_host" style="width:100px;" type="text" /></td><td><input type="submit" value="'.app::get('dev')->_('??????').'"></td></tr></table></form>';
            }
            $items[$mysql_key] = array(
                'value'=>$db_ver,
                'result'=>$rst,
            );
            if(!$rst){
                $allow_install = false;
            }

         
        $pathinfo = $this->get_path_info();
        if(!$pathinfo){
                $allow_install = false;
            }
    	$items['pathinfo()?????????'] = array(
            'value'=>$pathinfo?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$pathinfo,
        );
        return array('group'=>app::get('dev')->_('????????????'),'items'=>$items,'type'=>'require','allow_install'=>$allow_install);
            
        }

        if(ini_get('safe_mode')){
            $rst = is_callable('ftp_connect');
            if(!$rst){
                $allow_install = false;
            }
            $items['????????????????????????,ftp????????????'] = array(
                'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
                'result'=>$rst,
            );
        }

        $rst = preg_match('/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/',gethostbyname('www.example.com'));
        $items['DNS????????????,????????????????????????????????????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????? (?????????????????????)'),
            'result'=>$rst,
        );

        return array('group'=>app::get('dev')->_('????????????'),'key'=>'require','items'=>$items,'type'=>'require','allow_install'=>$allow_install);
    }
    
    function test_php_req(&$score){

        $rst = PHP_OS!='WINNT';
        $items['unix/linux ??????'] = array(
            'value'=>PHP_OS,
            'result'=>$rst,
        );
        if($rst){
            $score+=30;
        }else{
            $this->maxLevel(5);
        }


        $rst = version_compare(PHP_VERSION,'5.2','>=');
        $items['php ??????5.2.0??????'] = array(
            'value'=>PHP_VERSION,
            'result'=>$rst,
        );
        if($rst){
            $score+=20;
        }else{
            $this->maxLevel(5);
        }

        $rst = version_compare($this->db_ver,'4.1.2','>=');
        $items['MySQL?????? 4.1.2 ??????'] = array(
            'value'=>$this->db_ver,
            'result'=>$rst,
        );
        if($rst){
            $score+=100;
        }else{
            $this->maxLevel(5);
        }

        $gdscore = 0;
        $gd_rst = array();
        if($rst = is_callable('gd_info')){
            $gdinfo = gd_info();
            if($gdinfo['FreeType Support']){
                $gd_rst[] = 'freetype';
                $gdscore+=15;
            }
            if($gdinfo['GIF Read Support']){
                $gd_rst[] = 'gif';
                $gdscore+=10;
            }
            if($gdinfo['JPG Support']){
                $gd_rst[] = 'jpg';
                $gdscore+=10;
            }
            if($gdinfo['PNG Support']){
                $gd_rst[] = 'png';
                $gdscore+=10;
            }
            if($gdinfo['WBMP Support']){
                $gd_rst[] = 'bmp';
                $gdscore+=5;
            }
        }
        $items['GD??????'] = array(
            'value'=>$rst?implode(',',$gd_rst):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if($rst){
            $score+=$gdscore;
        }else{
            $this->maxLevel(2);
        }


        if(isset($GLOBALS['system'])){

            $system = &$GLOBALS['system'];
            $url = parse_url($system->base_url());
            $code = substr(md5(time()),0,6);
            $content = $this->doHttpQuery($url['path']."/_test_rewrite=1&s=".$code."&a.html");
            $rst = strpos($content,'[*['.md5($code).']*]');
            $items['??????rewrite'] = array(
                'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
                'result'=>$rst,
                'key'=>'rewrite',
            );

            $content = $this->doHttpQuery($url['path']."/statics/head.jgz");
            $rst = preg_match('/Content\-Type:\s*text\/javascript/i',$content);
            $items['?????????jgz?????????text/javascript'] = array(
                'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
                'result'=>$rst,
                'key'=>'mimejgz',
            );


        }

        $rst = is_callable('gzcompress');
        $items['Zlib??????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if($rst){
            $score+=80;
        }else{
            $this->maxLevel(2);
        }

        $rst = is_callable('json_decode');
        $items['Json??????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if($rst){
            $score+=30;
        }else{
            $this->maxLevel(5);
        }

        $rst = is_callable('mb_internal_encoding');
        $items['mbstring??????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if($rst){
            $score+=25;
        }else{
            $this->maxLevel(5);
        }

        $rst = is_callable('fsockopen');
        $items['fsockopen??????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if($rst){
            $score+=50;
        }else{
            $this->maxLevel(5);
        }

        $rst = is_callable('iconv');
        $items['iconv??????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>$rst,
        );
        if($rst){
            $score+=25;
        }else{
            $this->maxLevel(5);
        }

        //    $rst = get_magic_quotes_gpc();
        //    $items['magic_quotes_gpc??????'] = array(
        //      'value'=>$rst?'??????':'?????????',
        //      'result'=>!$rst,
        //    );
        //    if($rst){
        //      $score+=20;
        //    }

        $rst = ini_get('register_globals');
        $items['register_globals??????'] = array(
            'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
            'result'=>!$rst,
        );
        if(!$rst){
            $score+=15;
        }else{
            $this->maxLevel(2);
        }

        //    $rst = ini_get('allow_url_fopen');
        //    $items['allow_url_fopen??????'] = array(
        //      'value'=>$rst?'??????':'?????????',
        //      'result'=>!$rst,
        //    );
        //    if(!$rst){
        //      $score+=40;
        //    }

        if(version_compare(PHP_VERSION,'5.2.0','>=')){
            $rst = ini_get('allow_url_include??????');
            $items['allow_url_include?????? (php5.2.0??????)'] = array(
                'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
                'result'=>!$rst,
            );
            if($rst){
                $score+=30;
            }else{
                $this->maxLevel(5);
            }
        }else{
            $rst = ini_get('allow_url_fopen');
            $items['allow_url_fopen?????? (????????????php5.2.0)'] = array(
                'value'=>$rst?app::get('dev')->_('??????'):app::get('dev')->_('?????????'),
                'result'=>!$rst,
            );
            if($rst){
                $score+=30;
            }else{
                $this->maxLevel(5);
            }
        }

        $rst=null;
        if($cache_apc = is_callable('apc_store')){
            $rst[] = 'APC';
        }
        if($cache_memcached = class_exists('Memcache')){
            $rst[] = 'Memcached';
        }
        $items['??????????????????(apc,memcached)'] = array(
            'value'=>$rst?implode(',',$rst):app::get('dev')->_('???'),
            'result'=>($cache_apc || $cache_memcached)
        );
        if($cache_apc || $cache_memcached){
            $score+=150;
        }else{
            $this->maxLevel(4);
        }
        return array('group'=>app::get('dev')->_('????????????'),'items'=>$items,'type'=>'require');
    }

    function maxLevel($level){
        $this->maxLevel = min($this->maxLevel,$level-1);
    }

    function doHttpQuery($uri){
            $fp = fsockopen(isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'], $_SERVER['SERVER_PORT'], $errno, $errstr, 2);
            if ($fp) {

                $out = "GET ".preg_replace('#/+#','/',$uri)." HTTP/1.1\r\n";
                $out .= "Host: {$_SERVER['HTTP_HOST']}\r\n";
                $out .= "Connection: Close\r\n\r\n";

                fwrite($fp, $out);
                while (!feof($fp) && strlen($content)<512) {
                    $content .= fgets($fp, 128);
                }
                fclose($fp);

                return $content;
            }else{
                return false;
            }
    }
   	
   

}