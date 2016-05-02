<?php

include_once __DIR__.DIRECTORY_SEPARATOR."common.php";

function url($path,$params=array()){
    $tmp=explode("?", $path,2);
    $_path=$tmp[0];
    $_str=isset($tmp[1])?$tmp[1]:"";
    $_params=array();
    if($_str!=""){
        parse_str($_str,$_params);
    }
    $_params=array_merge($_params,$params);
    
    $router=\Rare\Application::getInstance()->getRouter();
    $url=$router->generateUrl($_path,$_params);
    if($url){
        return public_path($url);
    }
    return public_path($path).($_params?"?".http_build_query($_params):"");
}

/**
 * 输出web 相对根目录的地址
 * @param string $uri
 */
function public_path($uri){
    $app=\Rare\Application::getInstance();
    $info=$app->getPathInfo();
    return $info["web_root"].ltrim($uri,"/");
}



function rare_render($file,$vars=array()){
    return \Rare\Util::render($file, $vars);
}