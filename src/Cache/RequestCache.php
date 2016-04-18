<?php

namespace Rare\Cache;

/**
 * 在一次请求过程中的上下文缓存
 */
class RequestCache extends Cache {
    protected  $data = array ();
    protected $minExpireTime=0;
    /**
     * 添加、设置cache
     * 
     * @param mixed $key
     * @param mixed $value 
     * @param int $life
     * @return boolean
     */
    public function set($key, $value, $life) {
        $now=time();
        $expire=$life?$now+$life:0;
        $this->data [$key] = array("value"=>$value,"expire"=>$expire);
        if($expire>0){
            if(empty($this->minExpireTime)){
                $this->minExpireTime=$now+1;
            }
            $this->minExpireTime=min(max($this->minExpireTime,$now),$expire);
        }
        $this->gc();
        return true;
    }
    
    
    protected function gc(){
        $now=time();
        if($now>$this->minExpireTime){
            foreach ($this->data as $k=>$v){
                if(!empty($v["expire"]) && $v["expire"]<$now){
                    unset($this->data[$k]);
                    continue;
                }
                if(!empty($v["expire"])){
                    $this->minExpireTime=min($this->minExpireTime,$v["expire"]);
                }
            }
        }
        
        if (count ( $this->data ) > 10000) {
            array_shift ( $this->data );
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
        if (!array_key_exists ( $key, $this->data )){
            return false;
        }
        $item=$this->data[$key];
        return $item["expire"]==0 ||$item["expire"]>time();
    }
    
    /**
     * 取数据
     * @param mixed $key            
     * @return mixed
     */
    public function get($key, $default = null) {
        if($this->has($key)){
            $data=$this->data[$key];
            if($data["expire"]==0||$data["expire"]>time()){
                return $data["value"];
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
        $this->data = array ();
        return true;
    }
    
    /**
     * 清除一个cache
     * 
     * @param string $key            
     * @return boolean
     */
    public function remove($key) {
        if(array_key_exists($key, $this->data)){
             unset($this->data [$key] );
        }
        return true;
    }
}