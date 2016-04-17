<?php
namespace Rare\Cache;
/**
 * 使用sqlite数据库进行缓存处理
 */
class SqliteCache extends Cache{
    protected  $db;
    /**
     * @param string $cacheMod 缓存级别 默认为当前全局 root:全局 app：单独app有效 其他则
     */
    public function __construct($dbPath){
      rare_checkDir(dirname($dbPath));
      $this->db=new PDo("sqlite:".$dbPath);
      $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      $this->db->exec("PRAGMA synchronous = OFF");
      $q=@$this->db->query("select life from cache limit 1");
      if($q==false){
           $this->db->exec("create table cache(id varchar(255),data text,expire int,mtime int);CREATE UNIQUE INDEX [cache_unique] ON cache ([id])"); 
      }
      if(mt_rand(0, 100)==50){
         $this->gc();
      }
    }
    
    protected  function gc(){
        $this->db->exec("delete from cache where expire>0 and expire<".time());
        $this->db->exec("VACUUM");
        return true;
    }
  
    public function has($key){
       $sth=$this->db->prepare("select id from cache where id=? and (expire>? or expire=0)");
       $sth->execute(array($this->keyPrex.$key,time()));
       $one=$sth->fetch();
       return (boolean)$one;
    }
    
    public function get($key,$default=null){
      $sth=$this->db->prepare("select data from cache where id=? and (expire>? or expire=0)");
      $sth->execute(array($this->keyPrex.$key,time()));
      $one=$sth->fetchColumn();
      if(false!==$one){
          return @unserialize($one);
      }
      return $default;
    }
    
    public function set($key, $data,$lifetime=0){
      $expire=0;
      if($lifetime>0){
          $expire=time()+$lifetime;
      }
      $this->db->beginTransaction();
      $sth=$this->db->prepare("insert or replace into cache(id,data,expire,mtime) values(?,?,?,?)");
      $rt=$sth->execute(array($this->keyPrex.$key,serialize($data),$expire,time()));
      $this->db->commit();
      return $rt;
    }
    
    public function remove($key){
      $sth=$this->db->prepare("delete from cache where id=?");
      return $sth->execute(array($this->keyPrex.$key));
    }
    
    public function removeAll(){
      $rt=$sth=$this->db->exec("delete from cache");
      $this->db->exec("VACUUM");
      return $rt;
    }
    
    public function getBackend(){
      return $this->db;
    }
    
    public function getByLike($keyLike){
       $sth=$this->db->prepare("select data from cache where id like '?'");
       $sth->execute(array($keyLike));
       return $sth->fetchAll();
    }
    
}