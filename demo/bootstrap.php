<?php
include dirname(__FILE__).'/../src/Application.php';
$app=new \Rare\Application(dirname(__FILE__));
$app->setFilter(new \App\Filter($app));
return $app;