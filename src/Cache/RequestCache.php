<?php

namespace Rare\Cache;

/**
 * 在一次请求过程中的上下文缓存
 */
class RequestCache extends Cache {
    protected  $cacheData = array ();
    protected $minExpireTime=0;
    /**
     * 添加、设置cache
     * 
     * @param mixed $key
     * @param mixed $value 
     * @param int $life
     * @return boolean
     */
    public function set($key, $value, $life = 300) {
        $expire=$life?time()+$life:0;
        $this->$cacheData [$key] = array("data"=>$value,"expire"=>$expire);
        if($expire>0){
            if(empty($this->minExpireTime)){
                $this->minExpireTime=time()+3;
            }
            $this->minExpireTime=min($this->minExpireTime,$expire);
        }
        $this->gc();
        return true;
    }
    
    
    protected function gc(){
        if(time()>$this->minExpireTime){
            foreach ($this->$cacheData as $k=>$v){
                if(!empty($v["expire"]) && $v["expire"]<time()){
                    unset(self::$cacheData[$k]);
                    continue;
                }
                if(!empty($v["expire"])){
                    $this->minExpireTime=min($this->minExpireTime,$v["expire"]);
                }
            }
        }
        
        if (count ( $this->$cacheData ) > 10000) {
            array_shift ( $this->cacheData );
        }
    }
    
    /**
     * 判断是否有
     * 
     * @param mixed $key            
     * @return boolean
     */
    public function has($key) {
        $this->gc();
        return array_key_exists ( $key, $this->$cacheData );
    }
    
    /**
     * 取数据
     * @param mixed $key            
     * @return mixed
     */
    public function get($key, $default = null) {
        if($this->has($key)){
            $data=$this->$cacheData[$key];
            if($data["expire"]==0||$data["expire"]>time()){
                return $data["data"];
            }
        }
        return $default;
    }
    
    /**
     * 清除所有的数据
     * 
     * @return boolean
     */
    public function removeAll() {
        $this->$cacheData = array ();
        return true;
    }
    
    /**
     * 清除一个cache
     * 
     * @param unknown_type $key            
     * @return unknown_type
     */
    public function remove($key) {
        if(!empty(self::$cacheData [$key])){
             unset( self::$cacheData [$key] );
        }
        return true;
    }
}