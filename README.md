# ipdb-php
IPIP.net officially supported IP database ipdb format parsing library

# Installing
composer install ipip/db

# Example Code
<code>
<pre>
$db = new ipip\db\Reader('c:\tmp\ipdb\mydata6vipday4.ipdb');
$loc = $db->find("2001:250:200::");
$map = $db->findMap("2001:250:200::");
$obj = $db->findInfo("2001:250:200::");
var_dump($loc, $map, $obj);

try
{
    var_dump($db->find("255.255.255.1"));
}
catch (Exception $e)
{
    exit($e->getMessage());
}
</pre>
</code>