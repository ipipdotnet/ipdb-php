<?php

/**
 * @site https://www.ipip.net
 * @desc Parse IP library in ipdb format
 * @copyright IPIP.net
 */

namespace ipip\db;

abstract class Reader
{
    const IPV4 = 1;
    const IPV6 = 2;

    /**
     * @var int ipDB文件大小
     */
    private $fileSize;

    private $nodeCount  = 0;
    private $nodeOffset = 0;

    /**
     * @var array
     */
    private $meta;

    /**
     * 计算文件大小
     *
     * @return integer
     */
    abstract protected function computeFileSize();

    /**
     * 读取文件内容
     *
     * @param integer $offset 指针偏移
     * @param integer $length 读取长度
     * @return string|false
     */
    abstract protected function read($offset, $length);

    /**
     * 是否支持IP V6
     *
     * @return bool
     */
    public function supportV6()
    {
        return ($this->meta['ip_version'] & static::IPV6) === static::IPV6;
    }

    /**
     * 是否支持IP V4
     *
     * @return bool
     */
    public function supportV4()
    {
        return ($this->meta['ip_version'] & static::IPV4) === static::IPV4;
    }

    /**
     * 是否支持指定语言
     *
     * @param string $language
     * @return bool
     */
    public function supportLanguage($language)
    {
        return in_array($language, $this->getSupportLanguages(), true);
    }

    /**
     * 支持的语言
     * @return array
     */
    public function getSupportLanguages()
    {
        return (isset($this->meta['languages']) && is_array($this->meta['languages'])) ? array_keys($this->meta['languages']) : [];
    }

    /**
     * @return int  UTC Timestamp
     */
    public function getBuildTime()
    {
        return $this->meta['build'];
    }

    /**
     * 获取mete数据
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @throws \Exception
     */
    protected function init()
    {
        $this->fileSize = $this->computeFileSize();
        if ($this->fileSize === false){
            throw new \UnexpectedValueException("Error determining the size of data.");
        }

        $metaLength = unpack('N', $this->read(0, 4))[1];
        $text = $this->read(4, $metaLength);

        $this->meta = json_decode($text, true);
        if (isset($this->meta['fields']) === false || isset($this->meta['languages']) === false){
            throw new \Exception('IP Database metadata error.');
        }

        $fileSize = 4 + $metaLength + $this->meta['total_size'];
        if ($fileSize != $this->fileSize){
            throw  new \Exception('IP Database size error.');
        }

        $this->nodeCount = $this->meta['node_count'];
        $this->nodeOffset = 4 + $metaLength;
    }

    /**
     * @param string $ip
     * @param string $language
     * @return array|NULL
     */
    public function find($ip, $language)
    {
        if (!$this->supportLanguage($language)){
            throw new \InvalidArgumentException("language : {$language} not support.");
        }

        if (!IpUtils::isIp($ip)){
            throw new \InvalidArgumentException("The value \"$ip\" is not a valid IP address.");
        }

        if (IpUtils::isIp4($ip) && !$this->supportV4()){
            throw new \InvalidArgumentException("The Database not support IPv4 address.");
        }//
        elseif (IpUtils::isIp6($ip) && !$this->supportV6()){
            throw new \InvalidArgumentException("The Database not support IPv6 address.");
        }

        try{
            $node = $this->findNode($ip);

            if ($node > 0){
                $data = $this->resolve($node);
                $values = explode("\t", $data);

                return array_slice($values, $this->meta['languages'][$language], count($this->meta['fields']));
            }
        }catch(\Exception $e){
        }

        return null;
    }

    /**
     * @param string $ip
     * @param string $language
     * @return array|false|null
     */
    public function findMap($ip, $language)
    {
        $array = $this->find($ip, $language);
        if (null === $array){
            return null;
        }

        return array_combine($this->meta['fields'], $array);
    }

    private $v4offset      = 0;
    private $v6offsetCache = [];

    /**
     * @param $ip
     * @return int
     * @throws \Exception
     */
    private function findNode($ip)
    {
        $binary = inet_pton($ip);
        $bitCount = strlen($binary) * 8; // 32 | 128
        $key = substr($binary, 0, 2);
        $node = 0;
        $index = 0;
        if ($bitCount === 32){
            if ($this->v4offset === 0){
                for($i = 0; $i < 96 && $node < $this->nodeCount; $i++){
                    if ($i >= 80){
                        $idx = 1;
                    }else{
                        $idx = 0;
                    }
                    $node = $this->readNode($node, $idx);
                    if ($node > $this->nodeCount){
                        return 0;
                    }
                }
                $this->v4offset = $node;
            }else{
                $node = $this->v4offset;
            }
        }else{
            if (isset($this->v6offsetCache[$key])){
                $index = 16;
                $node = $this->v6offsetCache[$key];
            }
        }

        for($i = $index; $i < $bitCount; $i++){
            if ($node >= $this->nodeCount){
                break;
            }

            $node = $this->readNode($node, 1 & ((0xFF & ord($binary[$i >> 3])) >> 7 - ($i % 8)));

            if ($i == 15){
                $this->v6offsetCache[$key] = $node;
            }
        }

        if ($node === $this->nodeCount){
            return 0;
        }elseif ($node > $this->nodeCount){
            return $node;
        }

        throw new \Exception("find node failed.");
    }

    /**
     * @param $node
     * @param $index
     * @return mixed
     * @throws \Exception
     */
    private function readNode($node, $index)
    {
        return unpack('N', $this->readNodeData(($node * 8 + $index * 4), 4))[1];
    }

    /**
     * @param $node
     * @return mixed
     * @throws \Exception
     */
    private function resolve($node)
    {
        $resolved = $node - $this->nodeCount + $this->nodeCount * 8;
        if ($resolved >= $this->fileSize){
            return null;
        }

        $bytes = $this->readNodeData($resolved, 2);
        $size = unpack('N', str_pad($bytes, 4, "\x00", STR_PAD_LEFT))[1];

        $resolved += 2;

        return $this->readNodeData($resolved, $size);
    }

    /**
     * 读取节点数据
     *
     * @param integer $offset
     * @param integer $length
     * @return string
     * @throws \Exception
     */
    private function readNodeData($offset, $length)
    {
        if (0 >= $length){
            return '';
        }

        $value = $this->read(($offset + $this->nodeOffset), $length);
        if (strlen($value) === $length){
            return $value;
        }

        throw new \Exception("The Database file read bad data.");
    }

    /**
     * 回收资源
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }
}
