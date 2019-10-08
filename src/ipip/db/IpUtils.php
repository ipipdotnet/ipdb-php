<?php

namespace ipip\db;

/**
 * Class IpUtils
 * @package ipip\db
 */
class IpUtils
{
    /**
     * 判断是否为一个合法的IP地址
     *
     * @param string string [必须] 需要判断的字符;
     * @return bool;
     */
    public static function isIp($ip)
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * 判断是否是ipv4
     *
     * @param string $ip
     * @return bool
     */
    public static function isIp4($ip)
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * 判断是否是ipv6
     *
     * @param string $ip
     * @return bool
     */
    public static function isIp6($ip)
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * 判断是否是内网ip
     *
     * @param string $ip
     * @return bool
     */
    public static function isPrivateIp($ip)
    {
        return false === filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)
               && false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * 判断是否是公网ip
     *
     * @param string $ip
     * @return bool
     */
    public static function isPublicIp($ip)
    {
        return false !== static::isPrivateIp($ip);
    }
}
