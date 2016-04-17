<?php
namespace Rare\Cache;
abstract class Cache{
   protected $keyPrex="";
   
   abstract public function has($key);
   abstract public function get($key,$default=null);
   abstract public function set($key,$data,$lifetime=null);
   abstract public function remove($key);
   
   abstract public function removeAll();
   
   public function setKeyPrex($prex){
       $this->keyPrex=$prex;
   }
}
