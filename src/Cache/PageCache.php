<?php
namespace Rare\Cache;
/**
 *@example
 * 页面缓存
 * 默认采用文件缓存,也可以修改为其他缓存方式  如 memcache
 * <?php
 *  if(!rarePageCahce::has("key")){
 *     echo "这里是要缓存的内容";
 *     rarePageCahce::save(1000);//保存1000秒
 *  } 
 *
 */
class PageCahce{
   protected  $keys=array();
   protected  $handle;
   
   public function __construct(Cache $handle){
       $this->handle=$handle;
   }
   
   public function has($key){
        $key='pageCache/'.$key;
        $data=$this->handle->get($key);
        if($cache->has($key)){
          echo $cache->get($key);
          return true;
        }else{
          array_push($this->keys,$key);
          ob_start();
          ob_implicit_flush(0);
          return false;
        }
   }
   
   public  function save($lifeTime = 300){
        $data = ob_get_clean();
        $key=array_pop($this->keys);
        $this->handle->set($key,$data,$lifeTime);
        echo $data;
   }
}