<?php

namespace ipip\db;

class District
{
    public $reader = NULL;

    public function __construct($db)
    {
        $this->reader = new Reader($db);
    }

    public function find($ip, $language)
    {
        return $this->reader->find($ip, $language);
    }

    public function findMap($ip, $language)
    {
        return $this->reader->findMap($ip, $language);
    }

    public function findInfo($ip, $language)
    {
        $map = $this->findMap($ip, $language);
        if (NULL == $map)
        {
            return NULL;
        }

        return new DistrictInfo($map);
    }
}