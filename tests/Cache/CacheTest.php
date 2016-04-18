<?php
class TestCache extends PHPUnit_Framework_TestCase{
    
    public function testCacheAll(){
        $caches=array();
        $caches[]=new \Rare\Cache\FileCache(TEST_DATA_DIR);
        $caches[]=new \Rare\Cache\SqliteCache(TEST_DATA_DIR."/cache.db");
        $caches[]=new \Rare\Cache\RequestCache();
        
        foreach ($caches as $cache){
            $this->runCache($cache);
        }
    }
    
    private function runCache(\Rare\Cache\Cache $cache){
        echo "cache class:".get_class($cache)."\n";
        $key="abc";
        $value=mt_rand(1,time());
        $this->assertTrue($cache->set($key, $value,1));
        $this->assertTrue($cache->has($key));
        $this->assertEquals($value, $cache->get($key));
        
        $this->assertFalse($cache->has($key."123"));
        
        $key2="abcdef";
        $value2=time().mt_rand(1,99999);
        $this->assertTrue($cache->set($key2, $value2,2));
        $this->assertEquals($value2, $cache->get($key2));
        sleep(1);
        $this->assertFalse($cache->has($key));
        $this->assertEquals($value2, $cache->get($key2));
        
        $this->assertTrue($cache->remove($key2));
        $this->assertFalse($cache->has($key2));
        
        echo get_class($cache)." done\n";
    }
}