<?php
require_once '../Spyc.php';
require_once '../tmsRouter.php';
$root_path = dirname(__FILE__).'/demo/';
$router = tmsRouter::getInstance($root_path.'routing.yml', $root_path.'routingCache.php');

echo '<a href="'.$router->getRoute('testrout').'">link</a><hr/>';

echo 'Current route params: <br/><pre>';
print_r($router->findRoute());
