<?php

$route=array();
$route[]=array("path"=>"/a{name}","action"=>"index");
$route[]=array("path"=>"/a{name}/{id}","action"=>"Index");

$config=array();
$config['route']=$route;
return $config;