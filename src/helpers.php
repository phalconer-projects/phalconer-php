<?php

use Phalcon\Config;

if (!function_exists('is_config')) {
    /**
     * Check value is Phalcon config.
     *
     * @param  mixed  $config
     * @return bool
     */
    function is_config($config)
    {
        return $config instanceof Config;
    }
}

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        static $config = null;
        
        if ($key instanceof Config) {
            $config = $key;
            return $config;
        }
        
        if (is_null($key) || is_null($config)) {
            return $config;
        }
        
        if (is_array($key)) {
            foreach ($key as $base => $value) {
                $subConfig = &$config;
                $keys = explode('.', $base);
                while (count($keys) > 1) {
                    $segment = array_shift($keys);
                    if (!isset($subConfig[$segment]) || !($subConfig[$segment] instanceof Config)) {
                        $subConfig[$segment] = [];
                    }
                    $subConfig = &$subConfig[$segment];
                }
                $segment = array_shift($keys);
                $subConfig[$segment] = $value;
            }
            return $config;
        }

        if (isset($config[$key])) {
            return $config[$key];
        }

        $subConfig = $config;
        foreach (explode('.', $key) as $segment) {
            if (($subConfig instanceof Config) && isset($subConfig[$segment])) {
                $subConfig = $subConfig[$segment];
            } else {
                return $default instanceof Closure ? $default() : $default;
            }
        }
        return $subConfig;
    }
}
