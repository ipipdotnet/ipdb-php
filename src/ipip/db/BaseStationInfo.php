<?php

namespace ipip\db;

class BaseStationInfo
{
    public $country_name = '';
    public $region_name = '';
    public $city_name = '';
    public $owner_domain = '';
    public $isp_domain = '';
    public $base_station = '';

    public function __construct(array $data)
    {
        foreach ($data AS $field => $value)
        {
            $this->{$field} = $value;
        }
    }

    public function __get($name)
    {
        return $this->{$name};
    }
}