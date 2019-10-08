<?php

/**
 * @site https://www.ipip.net
 * @desc Parse IP library in ipdb format
 * @copyright IPIP.net
 */

namespace ipip\db;

abstract class Proxy
{
    /**
     * @var Reader|null
     */
    protected $reader = null;

    /**
     * Proxy constructor.
     * @param $reader
     * @throws \Exception
     */
    public function __construct($reader)
    {
        if (!$reader instanceof Reader){
            $reader = new IpDbReader($reader);
        }

        $this->reader = $reader;
    }

    /**
     * @return Reader|null
     */
    public function getReader()
    {
        return $this->reader;
    }
}
