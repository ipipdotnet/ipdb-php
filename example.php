<?php

require_once __DIR__ . '/src/ipip/Info.php';
require_once __DIR__ . '/src/ipip/Database.php';

$db = new ipip\Database('c:/work/tiantexin/framework/library/ip/mydata6.ipdb');
$loc = $db->find("2001:250:200::");
$map = $db->findMap("2001:250:200::");
$obj = $db->findInfo("2001:250:200::");
var_dump($loc, $map, $obj);