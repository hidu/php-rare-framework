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