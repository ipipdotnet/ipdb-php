<?php

namespace ipip\db;

class Reader
{
    const IPV4 = 1;
    const IPV6 = 2;

    private $file       = NULL;
    private $fileSize   = 0;
    private $nodeCount  = 0;
    private $nodeOffset = 0;

    private $meta = [];

    private $database = '';

    /**
     * Reader constructor.
     * @param $database
     * @throws \Exception
     */
    public function __construct($database)
    {
        $this->database = $database;

        $this->init();
    }

    private function init()
    {
        if (is_readable($this->database) === FALSE)
        {
            throw new \InvalidArgumentException("The IP Database file \"{$this->database}\" does not exist or is not readable.");
        }
        $this->file = @fopen($this->database, 'rb');
        if ($this->file === FALSE)
        {
            throw new \InvalidArgumentException("IP Database File opening \"{$this->database}\".");
        }
        $this->fileSize = @filesize($this->database);
        if ($this->fileSize === FALSE)
        {
            throw new \UnexpectedValueException("Error determining the size of \"{$this->database}\".");
        }

        $metaLength = unpack('N', fread($this->file, 4))[1];
        $text = fread($this->file, $metaLength);

        $this->meta = json_decode($text, 1);

        if (!isset($this->meta['fields']) || !isset($this->meta['languages']))
        {
            throw new \Exception('IP Database metadata error.');
        }

        $fileSize = 4 + $metaLength + $this->meta['total_size'];
        if ($fileSize != $this->fileSize)
        {
            throw  new \Exception('IP Database size error.');
        }

        $this->nodeCount = $this->meta['node_count'];
        $this->nodeOffset = 4 + $metaLength;
    }

    /**
     * @param $ip
     * @param string $language
     * @return array|NULL
     */
    public function find($ip, $language = 'CN')
    {
        if (is_resource($this->file) === FALSE)
        {
            throw new \BadMethodCallException('closed IPIP DB.');
        }

        if (!isset($this->meta['languages'][$language]))
        {
            throw new \InvalidArgumentException("language : {$language} not support");
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === FALSE)
        {
            throw new \InvalidArgumentException("The value \"$ip\" is not a valid IP address.");
        }

        if (strpos($ip, '.') !== FALSE && !$this->supportV4())
        {
            throw new \InvalidArgumentException("The Database not support IPv4 address.");
        }
        elseif (strpos($ip, ':') !== FALSE && !$this->supportV6())
        {
            throw new \InvalidArgumentException("The Database not support IPv6 address.");
        }

        try
        {
            $node = $this->findNode($ip);
            if ($node > 0)
            {
                $data = $this->resolve($node);

                $values = explode("\t", $data);

                return array_slice($values, $this->meta['languages'][$language], count($this->meta['fields']));
            }
        }
        catch (\Exception $e)
        {
            return NULL;
        }

        return NULL;
    }

    public function findMap($ip, $language = 'CN')
    {
        $array = $this->find($ip, $language);
        if (NULL == $array)
        {
            return NULL;
        }

        return array_combine($this->meta['fields'], $array);
    }

    /**
     * @param $ip
     * @return int
     * @throws \Exception
     */
    private function findNode($ip)
    {
        static $v4offset = 0;
        static $v6offsetCache = [];

        $binary = inet_pton($ip);
        $bitCount = strlen($binary) * 8; // 32 | 128
        $key = substr($binary, 0, 2);
        $node = 0;
        $index = 0;
        if ($bitCount === 32)
        {
            if ($v4offset === 0)
            {
                for ($i = 0; $i < 96 && $node < $this->nodeCount; $i++)
                {
                    if ($i >= 80)
                    {
                        $idx = 1;
                    }
                    else
                    {
                        $idx = 0;
                    }
                    $node = $this->readNode($node, $idx);
                    if ($node > $this->nodeCount)
                    {
                        return 0;
                    }
                }
                $v4offset = $node;
            }
            else
            {
                $node = $v4offset;
            }
        }
        else
        {
            if (isset($v6offsetCache[$key]))
            {
                $index = 16;
                $node = $v6offsetCache[$key];
            }
        }

        for ($i = $index; $i < $bitCount; $i++)
        {
            if ($node >= $this->nodeCount)
            {
                break;
            }

            $node = $this->readNode($node, 1 & ((0xFF & ord($binary[$i >> 3])) >> 7 - ($i % 8)));

            if ($i == 15)
            {
                $v6offsetCache[$key] = $node;
            }
        }

        if ($node === $this->nodeCount)
        {
            return 0;
        }
        elseif ($node > $this->nodeCount)
        {
            return $node;
        }

        throw new \Exception("find node failed");
    }

    /**
     * @param $node
     * @param $index
     * @return mixed
     * @throws \Exception
     */
    private function readNode($node, $index)
    {
        return unpack('N', $this->read($this->file, $node * 8 + $index * 4, 4))[1];
    }

    /**
     * @param $node
     * @return mixed
     * @throws \Exception
     */
    private function resolve($node)
    {
        $resolved = $node - $this->nodeCount + $this->nodeCount * 8;
        if ($resolved >= $this->fileSize)
        {
            return NULL;
        }

        $bytes = $this->read($this->file, $resolved, 2);
        $size = unpack('N', str_pad($bytes, 4, "\x00", STR_PAD_LEFT))[1];

        $resolved += 2;

        return $this->read($this->file, $resolved, $size);
    }

    public function close()
    {
        if (is_resource($this->file) === TRUE)
        {
            fclose($this->file);
        }
    }

    /**
     * @param $stream
     * @param $offset
     * @param $length
     * @return bool|string
     * @throws \Exception
     */
    private function read($stream, $offset, $length)
    {
        if ($length > 0)
        {
            if (fseek($stream, $offset + $this->nodeOffset) === 0)
            {
                $value = fread($stream, $length);
                if (strlen($value) === $length)
                {
                    return $value;
                }
            }

            throw new \Exception("The Database file read bad data");
        }

        return '';
    }

    public function supportV6()
    {
        return ($this->meta['ip_version'] & self::IPV6) === self::IPV6;
    }

    public function supportV4()
    {
        return ($this->meta['ip_version'] & self::IPV4) === self::IPV4;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return int  UTC Timestamp
     */
    public function getBuildTime()
    {
        return $this->meta['build'];
    }
}