<?php
namespace Rare\Cache;
class NoCache extends Cache{
    
   public function has($key){
       return false;
   }
   
   public function get($key,$default=null){
      return null;
   }
   
   public function set($key,$data,$lifetime=null){
       return true;
   }
   
   public function remove($key){
       return true;
   }
   
   public function removeAll(){
       return true;
   }
}