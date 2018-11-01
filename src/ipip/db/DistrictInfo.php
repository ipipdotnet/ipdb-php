<?php

namespace ipip\db;

class DistrictInfo
{
    public $country_name = '';
    public $region_name = '';
    public $city_name = '';
    public $district_name = '';
    public $china_admin_code = '';
    public $covering_radius = '';
    public $longitude = '';
    public $latitude = '';

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