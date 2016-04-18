<?php
namespace Rare\Cache;
class FileCache extends Cache{
    protected  $cacheDir;
    
    public function __construct($cacheDir){
       $this->cacheDir=$cacheDir;
    }
    
    public function has($key){
         $cache=$this->getCache($key);
         if(empty($cache)){
             return false;
         }
         if(!empty($cache["expire"])){
             return $cache["expire"]>time();
         }
         return true;
    }
    
    public function get($key,$default=null){
        $cache=$this->getCache($key);
        if(empty($cache)){
            return $default;
        }
        if(!empty($cache["expire"])){
            return time()>$cache["expire"]?$default:$cache["value"];
        }
        return $cache["value"];
    }
    
    protected function getCache($key){
        $cacheFilePath=$this->getCacheFilePath($key);
        if(!file_exists($cacheFilePath)){
            return false;
        }
        $data=@unserialize(file_get_contents($cacheFilePath));
        return is_array($data)?$data:false;
    }
    
    public function set($key,$value,$lifetime){
       $cacheFilePath=$this->getCacheFilePath($key);
       $expire=0;
       if($lifetime){
           $expire=time()+$lifetime;
       }
       $cacheData=array(
           "key"=>$key,
           "life"=>$lifetime,
           "expire"=>$expire,
           "value"=>$value,
       );
       $dir=dirname($cacheFilePath);
       rare_checkDir($dir);
       $tmpFilePath=tempnam($dir,"tmp");
       if(false===file_put_contents($tmpFilePath, serialize($cacheData),LOCK_EX)){
           return false;
       }
       if(file_exists($cacheFilePath)){
           unlink($cacheFilePath);
       }
       return rename($tmpFilePath, $cacheFilePath);
    }
    
    public function remove($key){
       @unlink($this->getCacheFilePath($key));
       return true;
    }
    public function removeAll(){
        return rare_rmDir($this->cacheDir);
    }
    protected  function getCacheFilePath($key){
        return rare_pathJoin($this->cacheDir,$this->keyPrex,$key.".cache");
    }

}