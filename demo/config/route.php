<?php

$route=array();
$route[]=array("path"=>"/a{name}","action"=>"Index");

$config=array();
$config['route']=$route;
return $config;