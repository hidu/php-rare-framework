<?php
namespace Rare\Core;
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
     * @param string $path  eg user/list.json
     * @return mixd array-route match  false-no route
     */
    public  function parseUriPath($path){
        preg_match("/\.(.*)$/", $path,$suffixMatches);
        $suffix=isset($suffixMatches[1])?$suffixMatches[1]:null;
        
        foreach ($this->config as $item){
            //action 格式:  index | user/list@get  get 为方法名
            $actionName=$item["action"];
            if(!preg_match_all("#^".$item['_path_reg']."$#",$path,$matches,PREG_SET_ORDER)){
                continue;
            }
            //若在路由中定义了 后缀，则 访问地址的后缀必须和定义的一致
//             if(!empty($item['suffix']) && $item['suffix']!=$suffix){
//                 continue;
//             }
            
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
                $_REQUEST[$_k]=$_v;
            }
            return array("action"=>$actionName,"param"=>$param,"id"=>$item["id"]);
        }
        return false;
    }
    
    /**
     * 配置文件预解析
     * @param array $config
     * @return array
     */
    protected   function preProcessConfig($config){
        foreach ($config as $k=>$item){
            $path=$item['path'];
            preg_match_all("/\{\w*\}/", $path,$matches);
            
            $matches=$matches[0];
            $paramsMatch=array();
            $param=isset($item['param'])?$item['param']:array();
            foreach ($matches as $match){
                $p=strtr($match,array("{"=>'',"}"=>''));
                if(!isset($param[$p])){
                    $param[$p]="[^\/]+";
                }
                $paramsMatch['{'.$p."}"]="(".$param[$p].")";
            }
            $item['param']=$param;
            $item['_path_reg']=strtr($path,$paramsMatch);
            $item['_params']=$paramsMatch;
            $item['id']=isset($item["id"])?$item["id"]:"";
            $item["action"]=$this->reFormatAction($item["action"]);
            $config[$k]=$item;
        }
        return $config;
    }
    /**
     * @return array
     */
    public function getConfig(){
        return $this->config;
    }
    
    /**
     * 
     * @param string $actionName
     * @return string
     */
    private function reFormatAction($actionName){
        return implode("/", array_map("ucfirst", explode("/", $actionName)));
    }
    
    /**
     * @param string $actionName
     * @param array $params
     * @return string|boolean
     */
    public function generateUrl($actionName,$params=array()){
        $actionName=$this->reFormatAction($actionName);
        foreach ($this->config as $item){
            if($item["action"]!=$actionName){
                continue;
            }
            
            if(count($item["param"])>count($params)){
                continue;
            }
            
            $isMatch=true;
            $_params=array();
            foreach ($item['param'] as $k=>$reg){
                if(!isset($params[$k]) || !preg_match("#^".$reg."$#", $params[$k])){
                    $isMatch=false;
                    break;
                }
                $_params["{".$k."}"]=urlencode($params[$k]);
                unset($params[$k]);
            }
            if(!$isMatch){
                continue;
            }
            $url=strtr($item['path'], $_params);
            if(!empty($params)){
                $url.="?".http_build_query($params);
            }
            return $url;
        }
        return false;
    }
}