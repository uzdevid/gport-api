<?php

namespace socket\Service;

class Dns {
    /**
     * @param string $hostname
     * @param string $checkIp
     * @return bool
     */
    public static function checkIp(string $hostname, string $checkIp): bool {
        return gethostbyname($hostname) === $checkIp;
    }
}