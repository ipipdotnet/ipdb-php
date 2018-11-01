<?php

namespace ipip\db;

class CityInfo
{
    public $country_name = '';
    public $region_name = '';
    public $city_name = '';
    public $owner_domain = '';
    public $isp_domain = '';
    public $latitude = '';
    public $longitude = '';
    public $timezone = '';
    public $utc_offset = '';
    public $china_admin_code = '';
    public $idd_code = '';
    public $country_code = '';
    public $continent_code = '';
    public $idc = '';
    public $base_station = '';
    public $country_code3 = '';
    public $european_union = '';
    public $currency_code = '';
    public $currency_name = '';
    public $anycast = '';

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