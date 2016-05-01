<?php
class TestRouter extends PHPUnit_Framework_TestCase{
    public function testRoute(){
        $config=array();
        $config[]=array("path"=>"/","action"=>"index","id"=>"1");
        $config[]=array("path"=>"/user/{name}.json","action"=>"index@user","id"=>2);
        $config[]=array("path"=>"/goods/{id}","action"=>"index@user","id"=>4,"param"=>array("id"=>"\d+"));
        $config[]=array("path"=>"/goods/{name}","action"=>"index@user","id"=>3);
        $route=new \Rare\Router($config,new \Rare\Cache\NoCache());
        
        $info=$route->parseUriPath("/");
        $this->assertEquals(1, $info["id"]);

        $info=$route->parseUriPath("/user/hello.json");
        $this->assertEquals(2, $info["id"]);
        $this->assertEquals(array("name"=>"hello"), $info["param"]);
        
        $info=$route->parseUriPath("/user/hello");
        $this->assertFalse($info);

        $info=$route->parseUriPath("/user/hello.html");
        $this->assertFalse($info);
        
        $info=$route->parseUriPath("/goods/hello");
        $this->assertEquals(3, $info["id"]);
        
        $info=$route->parseUriPath("/goods/hello.html");
        $this->assertEquals(3, $info["id"]);
        
        
        $info=$route->parseUriPath("/goods/123");
        $this->assertEquals(4, $info["id"]);
    }
}