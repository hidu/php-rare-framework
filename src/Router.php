<?php
namespace Rare;
use Rare\Cache\Cache;
class Router{
    protected $config=array();
    /**
     * 
     * @param array $config
     * @param Cache $cache
     */
    public function __construct($config,Cache $cache){
        $cacheKey="route_config";
        $data=$cache->get($cacheKey);
        $sign=md5(serialize($config));
        if(empty($data) || $data["sign"]!=$sign ){
            $data=array(
                "sign"=>$sign,
                "config"=>$this->preProcessConfig($config),
            );
            $cache->set($cacheKey, $data);
        }
        $this->config=$data["config"];
    }
    
    /**
     * @param string $path
     */
    public  function parseUriPath($path){
        preg_match("/\.(.*)$/", $path,$suffixMatches);
        $suffix=isset($suffixMatches[1])?$suffixMatches[1]:null;
        
        foreach ($this->config as $actionName=>$subConfs){
            foreach ($subConfs as $item){
                if(!preg_match_all("#^".$item['path_reg']."$#",$path,$matches,PREG_SET_ORDER)){
                    continue;
                }
                //若在路由中定义了 后缀，则 访问地址的后缀必须和定义的一致
                if(!empty($item['suffix']) && $item['suffix']!=$suffix && ！){
                    continue;
                }
                
                array_shift($matches[0]);
                
                $param=array();
                foreach ($item['_params'] as $k=>$v){
                    $param[strtr($k,array("{"=>'',"}"=>''))]=urldecode(array_shift($matches[0]));
                }
                //自定义函数验证路由
                $fn=empty($item['fn'])?null:$item['fn'];
                if(!empty($fn) && is_callable($fn) && !$fn($path,$actionName,$param)){
                    continue;
                }
                
                foreach ($param as $_k=>$_v){
                    $_GET[$_k]=$_REQUEST[$_k]=$_v;
                }
                return array("path"=>$actionName,"path_arr"=>explode("/", $actionName),"param"=>$param);
            }
        }
        return false;
    }
    
    protected   function preProcessConfig($config){
        foreach ($config as $actionFullName=>$items){
            $tmp=explode("/", $actionFullName);
            if(is_string($items)){
                //处理这种格式 $router["index"]="index";
                $items=array(array('path'=>$items));
            }
            /**
             *预处理配置文件：
             * 处理为如下多维数组
             * $router['index/index'][]=array();
             */
            foreach ($items as $k=>$item){
                //$router['index']=array("path"=>"index");
                if(!is_numeric($k)){
                    $_tmp=$items;
                    $items=array($_tmp);
                    break;
                }
                //for $router['index/index']=array("index-{id}","index-{id}-{title}");
                if(is_string($item)){
                    $items[$k]=array("path"=>$item);
                }
            }
            
            foreach ($items as $k=>$item){
                $path=$item['path'];
                preg_match_all("/\{\w*\}/", $path,$matches);
                
                $matches=$matches[0];
                $paramsMatch=array();
                $param=isset($item['param'])?$item['param']:array();
                foreach ($matches as $match){
                    $p=strtr($match,array("{"=>'',"}"=>''));
                    if(!isset($param[$p])){
                        $param[$p]=".+";
                    }
                    $paramsMatch['{'.$p."}"]="(".$param[$p].")";
                }
                 
                $_path=$path;
                $items[$k]['param']=$param;
                $items[$k]['path_param']=$_path;
                $items[$k]['path_reg']=strtr($_path,$paramsMatch);
                $items[$k]['_params']=$paramsMatch;
            }
            $config[$actionFullName]=$items;
        }
        return $config;
    }
}