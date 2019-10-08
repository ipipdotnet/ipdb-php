<?php

/**
 * @site https://www.ipip.net
 * @desc Parse IP library in ipdb format
 * @copyright IPIP.net
 */

namespace ipip\db;

class District extends Proxy
{
    public function find($ip, $language)
    {
        return $this->getReader()->find($ip, $language);
    }

    public function findMap($ip, $language)
    {
        return $this->getReader()->findMap($ip, $language);
    }

    public function findInfo($ip, $language)
    {
        $map = $this->findMap($ip, $language);
        if (null === $map){
            return null;
        }

        return new DistrictInfo($map);
    }
}
