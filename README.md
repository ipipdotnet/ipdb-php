# ipdb-php
IPIP.net officially supported IP database ipdb format parsing library

# Installing
<pre>
composer install ipip/db
</pre>

# Example Code
<pre>
 
 Language Support: CN(中文) / EN (English); 

// 全球 IPv6 地级市精度离线库（China：每周高级版，每日标准版，每日高级版，每日专业版，每日旗舰版）
$city = new ipip\db\City('c:\work\ipdb\city.ipv6.ipdb');
//var_dump($city->find('2001:250:200::', 'CN'));
//var_dump($city->findMap('2001:250:200::', 'CN'));
//var_dump($city->findInfo('2001:250:200::', 'CN'));

// 全球 IPv4 地级市精度离线库（China：免费版，每周高级版，每日标准版，每日高级版，每日专业版，每日旗舰版）
$city = new ipip\db\City('c:\work\ipdb\city.free.ipdb');
var_dump($city->find('118.28.1.1', 'CN'));
var_dump($city->findMap('118.28.1.1', 'CN'));
var_dump($city->findInfo('118.28.1.1', 'CN'));

// for China
// 中国地区区县级IPv4离线库
$district = new ipip\db\District('c:\work\ipdb\china_district.ipdb');
var_dump($district->find('1.12.7.255', 'CN'));
var_dump($district->findMap('1.12.7.255', 'CN'));
var_dump($district->findInfo('1.12.7.255', 'CN'));

// IDC IPv4 列表离线库
$idc = new ipip\db\IDC('c:\work\ipdb\idc_list.ipdb');
var_dump($idc->find('1.1.1.1', 'CN'));
var_dump($idc->findMap('1.1.1.1', 'CN'));
var_dump($idc->findInfo('1.1.1.1', 'CN'));

// 基站IPv4 离线库
$baseStation = new ipip\db\BaseStation('c:\work\ipdb\base_station.ipdb');
var_dump($baseStation->find('223.220.221.255', 'CN'));
var_dump($baseStation->findMap('223.220.221.255', 'CN'));
var_dump($baseStation->findInfo('223.220.221.255', 'CN'));

</pre>