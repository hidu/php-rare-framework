<?php
/**
 * 字符串是否以指定值结尾
 * @param string $str
 * @param string $subStr
 */
function rare_strEndWith($str,$subStr){
    return strcmp(substr($str, -(strlen($subStr))),$subStr)==0;
}
//
/**
 * 字符串是否以指定值开始
 * @param string $str
 * @param string $subStr
 */
function rare_strStartWith($str,$subStr){
    return strcmp(substr($str, 0,(strlen($subStr))),$subStr)==0;
}

/**
 * 是否是https
 * @return boolean
 */
function rare_isHttps(){
    return ( (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1)) ||
            (isset($_SERVER['HTTP_SSL_HTTPS']) && (strtolower($_SERVER['HTTP_SSL_HTTPS']) == 'on' || $_SERVER['HTTP_SSL_HTTPS'] == 1)) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
            );
}
/**
 *是否是ajax 请求
 */
function rare_isAjax(){
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}

/**
 * 路径地址拼接
 * 
 * #echo  rare_pathJoin(__DIR__,"abc","def");
 * /user/work/abc/def
 * 
 * #echo  rare_pathJoin(__DIR__,array("abc","def"));
 * /user/work/abc/def
 */
function rare_pathJoin(){
    $arr=array();
    $numargs = func_num_args();
    for($i=0;$i<$numargs;$i++){
        $v=func_get_arg($i);
        if(is_array($v)){
            $arr=array_merge($arr,array_values($v));
        }else{
            $arr[]=$v;
        }
    }
    return implode(DIRECTORY_SEPARATOR, $arr);
}

function rare_checkDir($dir){
    
}

function rare_rmDir($path){
    if(!file_exists($path)){
        return true;
    }
    $path=realpath($path);
    if(strlen($path)<8){
        return false;
    }
    foreach (glob($dir) as $file) {
        if (is_dir($file)) {
            rare_rmDir($file);
            rmdir($file);
        } else {
            unlink($file);
        }
    }
    return rmdir($path);  
}

function rare_app_config($name,$default=null){
   return \Rare\Config::get($name,$default);
}


function rare_array_get($arr,$key,$default=null){
    $keyArr=explode("/",trim($key,"/"));
    $val=$arr;
    foreach ($keyArr as $k){
        if(is_array($val) && array_key_exists($k, $val)){
            $val=$val[$k];
        }else{
            return $default;
        }
    }
    return $val;
}


function rare_print($var){
    echo "<pre>\n";
    print_r($var);
    echo "</pre>\n";
}