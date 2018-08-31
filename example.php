<?php

require_once __DIR__ . '/src/ipip/db/Info.php';
require_once __DIR__ . '/src/ipip/db/Reader.php';

function randomIP() {
    return sprintf('%d.%d.%d.%d', mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
}

$db = new ipip\db\Reader(__DIR__ . '/mydata6vipday4.ipdb');

$db = new ipip\db\Reader('c:/work/tiantexin/bb/mydatavipday4.ipdb');
exit;

$s = microtime(1);

for ($i = 0; $i < 10000; $i++)
{

    $loc = $db->find(randomIP());
}

echo round(microtime(1) - $s, 6);
